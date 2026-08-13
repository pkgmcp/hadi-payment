<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways\Generic;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\RefundException;
use Hadi\Payment\Exceptions\VerificationException;

/**
 * Config-driven gateway base used by the long-tail of country gateways that
 * follow a generic hosted/API payment flow.
 *
 * Concrete subclasses differ only in the default endpoint paths and in the
 * payload helpers, while this class provides the shared HTTP + validation +
 * status-mapping machinery.
 */
abstract class GenericGateway extends PaymentGateway
{
    /**
     * Gateway type slug used in the country catalog (redirect, api, mobile,
     * bank, card, crypto, wallet).
     */
    abstract protected function gatewayType(): string;

    /**
     * Default endpoint paths relative to the configured base endpoint.
     *
     * @return array{initialize: string, verify: string, refund: string}
     */
    protected function endpointPaths(): array
    {
        return [
            'initialize' => '/initialize',
            'verify' => '/verify',
            'refund' => '/refund',
        ];
    }

    protected function getDefaultConfig(): array
    {
        return [
            'merchant_id' => '',
            'secret_key' => '',
            'api_key' => '',
            'endpoint' => '',
            'sandbox' => true,
            'timeout' => 30,
            'defaultCallbackUrl' => 'https://example.com/hadi-payment/callback',
            'currency' => 'USD',
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['endpoint'])) {
            throw new InvalidConfigurationException($this->getGatewayName() . ': endpoint is required.');
        }

        if (empty($config['merchant_id']) && empty($config['api_key']) && empty($config['secret_key'])) {
            throw new InvalidConfigurationException($this->getGatewayName() . ': at least one credential (merchant_id, api_key, secret_key) is required.');
        }
    }

    /**
     * Build the full URL for a named operation.
     */
    protected function urlFor(string $operation): string
    {
        $endpoint = rtrim((string) ($this->config['endpoint'] ?? ''), '/');
        $paths = $this->endpointPaths();

        return $endpoint . ($paths[$operation] ?? '/' . $operation);
    }

    /**
     * Build the base initialize payload shared by all generic drivers.
     */
    protected function buildInitializePayload(array $data): array
    {
        return [
            'merchant_id' => $this->config['merchant_id'] ?? '',
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? ($this->config['currency'] ?? 'USD'),
            'order_id' => $data['order_id'] ?? $data['orderId'] ?? null,
            'reference_id' => $data['reference_id'] ?? $data['referenceId'] ?? null,
            'description' => $data['description'] ?? 'Payment',
            'callback_url' => $data['callback_url'] ?? $data['callbackUrl'] ?? ($this->config['defaultCallbackUrl'] ?? ''),
            'customer' => $data['customer'] ?? $data['customer_data'] ?? [],
        ];
    }

    /**
     * Normalize an initialize response.
     */
    protected function buildInitializeResponse(array $responseData): array
    {
        $data = $responseData['data'] ?? $responseData;

        return [
            'status' => 'pending_user_action',
            'message' => $this->getGatewayName() . ' payment initialized.',
            'paymentId' => $data['payment_id'] ?? $data['paymentId'] ?? null,
            'gatewayReferenceId' => $data['gateway_reference_id'] ?? $data['gatewayReferenceId'] ?? $data['transaction_id'] ?? $data['call_id'] ?? null,
            'paymentUrl' => $data['payment_url'] ?? $data['paymentUrl'] ?? $data['redirect_url'] ?? $data['redirectUrl'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? ($this->config['currency'] ?? 'USD'),
            'rawData' => $responseData,
        ];
    }

    /**
     * Map a provider status string to the normalized vocabulary.
     */
    protected function mapStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'PENDING', 'PROCESSING', 'INITIATED', 'CREATED' => 'pending',
            'SUCCESS', 'COMPLETED', 'SUCCEEDED', 'AUTHORIZED', 'PAID', 'CAPTURED' => 'success',
            'FAILED', 'CANCELLED', 'CANCELED', 'DECLINED', 'REVERSED' => 'failed',
            'REFUNDED', 'REFUND' => 'refunded',
            default => 'unknown',
        };
    }

    /**
     * Perform an HTTP call against a named operation.
     */
    protected function apiCall(string $method, string $operation, array $payload = []): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        if (!empty($this->config['api_key'])) {
            $headers['Authorization'] = 'Bearer ' . $this->config['api_key'];
        } elseif (!empty($this->config['merchant_id'])) {
            $headers['X-Merchant-Id'] = (string) $this->config['merchant_id'];
        }

        if (!empty($this->config['secret_key'])) {
            $headers['X-Secret-Key'] = (string) $this->config['secret_key'];
        }

        if (!in_array(strtoupper($method), ['GET', 'HEAD'], true)) {
            $headers['Content-Type'] = 'application/json';
        }

        return $this->httpClient($method, $this->urlFor($operation), $payload, $headers);
    }

    /**
     * @inheritDoc
     */
    public function initialize(array $data): array
    {
        $sanitized = $this->sanitize($data);

        if (empty($sanitized['amount']) || !is_numeric($sanitized['amount']) || (float) $sanitized['amount'] <= 0) {
            throw new InitializationException($this->getGatewayName() . ': Invalid or missing amount.');
        }

        if (empty($sanitized['order_id']) && empty($sanitized['orderId']) && empty($sanitized['reference_id'])) {
            throw new InitializationException($this->getGatewayName() . ': Missing order id.');
        }

        $response = $this->apiCall('POST', 'initialize', $this->buildInitializePayload($sanitized));

        if ($response['status_code'] !== 200) {
            throw new InitializationException($this->getGatewayName() . ': Failed to initialize payment (HTTP ' . $response['status_code'] . ').');
        }

        return $this->buildInitializeResponse($response['body'] ?? []);
    }

    /**
     * @inheritDoc
     */
    public function process(array $data): array
    {
        $sanitized = $this->sanitize($data);

        if (empty($sanitized['transaction_id']) && empty($sanitized['transactionId']) && empty($sanitized['payment_id'])) {
            throw new ProcessingException($this->getGatewayName() . ': Missing transaction id for processing.');
        }

        return $this->verify($sanitized);
    }

    /**
     * @inheritDoc
     */
    public function verify(array $data): array
    {
        $sanitized = $this->sanitize($data);
        $transactionId = $sanitized['transaction_id'] ?? $sanitized['transactionId'] ?? $sanitized['payment_id'] ?? $sanitized['paymentID'] ?? null;

        if (empty($transactionId)) {
            throw new VerificationException($this->getGatewayName() . ': Missing transaction id for verification.');
        }

        $response = $this->apiCall('GET', 'verify', ['transaction_id' => (string) $transactionId]);

        if ($response['status_code'] !== 200) {
            throw new VerificationException($this->getGatewayName() . ': Failed to verify payment (HTTP ' . $response['status_code'] . ').');
        }

        $body = $response['body'] ?? [];
        $data = $body['data'] ?? $body;
        $status = $this->mapStatus((string) ($data['status'] ?? 'PENDING'));

        return [
            'status' => $status,
            'message' => $this->getGatewayName() . ' verification result: ' . ($data['status'] ?? 'unknown'),
            'transactionId' => $transactionId,
            'paymentStatus' => $data['status'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? ($this->config['currency'] ?? 'USD'),
            'rawData' => $body,
        ];
    }

    /**
     * @inheritDoc
     */
    public function refund(array $data): array
    {
        $sanitized = $this->sanitize($data);
        $transactionId = $sanitized['transaction_id'] ?? $sanitized['transactionId'] ?? $sanitized['payment_id'] ?? $sanitized['paymentID'] ?? null;

        if (empty($transactionId)) {
            throw new RefundException($this->getGatewayName() . ': Missing transaction id for refund.');
        }

        $payload = [
            'transaction_id' => $transactionId,
            'amount' => $sanitized['amount'] ?? null,
            'reason' => $sanitized['reason'] ?? 'Refund request',
        ];

        $response = $this->apiCall('POST', 'refund', $payload);

        if ($response['status_code'] !== 200) {
            throw new RefundException($this->getGatewayName() . ': Failed to process refund (HTTP ' . $response['status_code'] . ').');
        }

        $body = $response['body'] ?? [];
        $data = $body['data'] ?? $body;

        if (($data['status'] ?? '') !== 'success' && strtoupper((string) ($data['status'] ?? '')) !== 'REFUNDED') {
            throw new RefundException($this->getGatewayName() . ': ' . ($data['message'] ?? 'Failed to process refund.'));
        }

        return [
            'status' => 'success',
            'message' => $this->getGatewayName() . ' refund processed successfully.',
            'transactionId' => $transactionId,
            'refundId' => $data['refund_id'] ?? $data['refundId'] ?? null,
            'amount' => $data['refunded_amount'] ?? $data['amount'] ?? $payload['amount'],
            'rawData' => $body,
        ];
    }
}
