<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\RefundException;
use Hadi\Payment\Exceptions\VerificationException;

/**
 * Shared base class for Bangladesh-based payment gateways (Rocket, SureCash,
 * UCash, MCash, MyCash, ShurjoPay and friends).
 *
 * These gateways share the same HTTP + validation + status mapping flow.
 */
abstract class BangladeshGateway extends PaymentGateway
{
    /**
     * Sandbox / production API base URLs.
     *
     * @return array{sandbox: string, production: string}
     */
    abstract protected function apiBaseUrls(): array;

    /**
     * Configuration keys that must be present for this gateway.
     *
     * @return array<int, string>
     */
    abstract protected function requiredConfigKeys(): array;

    /**
     * Lowercase gateway slug used in messages and defaults (e.g. "rocket").
     */
    abstract protected function gatewaySlug(): string;

    protected function getDefaultConfig(): array
    {
        return [
            'api_key' => '',
            'secret_key' => '',
            'merchant_id' => '',
            'sandbox' => true,
            'timeout' => 30,
            'defaultCallbackUrl' => 'https://example.com/hadi-payment/callback',
        ];
    }

    protected function validateConfig(array $config): void
    {
        foreach ($this->requiredConfigKeys() as $key) {
            if (empty($config[$key])) {
                throw new InvalidConfigurationException($this->gatewayName() . ": {$key} is required.");
            }
        }
    }

    protected function gatewayName(): string
    {
        return ucfirst($this->gatewaySlug());
    }

    protected function getApiBaseUrl(): string
    {
        $urls = $this->apiBaseUrls();

        return $this->config['sandbox'] ? $urls['sandbox'] : $urls['production'];
    }

    /**
     * Perform an authenticated request against the gateway API.
     */
    protected function apiRequest(string $method, string $endpoint, array $payload = []): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . ($this->config['api_key'] ?? ''),
            'Accept' => 'application/json',
        ];

        if (!in_array(strtoupper($method), ['GET', 'HEAD'], true)) {
            $headers['Content-Type'] = 'application/json';
        }

        return $this->httpClient($method, $this->getApiBaseUrl() . $endpoint, $payload, $headers);
    }

    /**
     * Validate the common payment payload shape.
     */
    protected function validatePaymentData(array $data): void
    {
        if (empty($data['amount']) || !is_numeric($data['amount']) || (float) $data['amount'] <= 0) {
            throw new InitializationException($this->gatewayName() . ': Invalid or missing amount.');
        }

        if (empty($data['orderId']) && empty($data['reference_id'])) {
            throw new InitializationException($this->gatewayName() . ': Missing orderId.');
        }
    }

    /**
     * Map a provider status string to the normalized Hadi status vocabulary.
     */
    protected function mapStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'PENDING', 'PROCESSING', 'INITIATED' => 'pending',
            'SUCCESS', 'COMPLETED', 'SUCCEEDED', 'AUTHORIZED' => 'success',
            'FAILED', 'CANCELLED', 'CANCELED', 'REVERSED' => 'failed',
            'REFUNDED' => 'refunded',
            default => 'unknown',
        };
    }

    /**
     * Build the normalized initialize response.
     */
    protected function buildInitializeResponse(array $responseData): array
    {
        $data = $responseData['data'] ?? $responseData;

        return [
            'status' => 'pending_user_action',
            'message' => $this->gatewayName() . ' payment initialized.',
            'gatewayReferenceId' => $data['transaction_id'] ?? $data['payment_id'] ?? null,
            'paymentUrl' => $data['payment_url'] ?? $data['redirect_url'] ?? null,
            'rawData' => $responseData,
        ];
    }

    /**
     * Build the normalized verify response.
     */
    protected function buildVerifyResponse(string $transactionId, array $responseData): array
    {
        $data = $responseData['data'] ?? $responseData;
        $status = $this->mapStatus($data['status'] ?? 'PENDING');

        return [
            'status' => $status,
            'message' => $this->gatewayName() . ' verification result: ' . ($data['status'] ?? 'unknown'),
            'transactionId' => $transactionId,
            'paymentStatus' => $data['status'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'BDT',
            'rawData' => $responseData,
        ];
    }

    /**
     * Build the normalized refund response.
     */
    protected function buildRefundResponse(string $transactionId, array $responseData): array
    {
        $data = $responseData['data'] ?? $responseData;

        return [
            'status' => 'success',
            'message' => $this->gatewayName() . ' refund processed successfully.',
            'transactionId' => $transactionId,
            'refundId' => $data['refund_id'] ?? $data['refundTrxID'] ?? null,
            'amount' => $data['refunded_amount'] ?? $data['amount'] ?? null,
            'rawData' => $responseData,
        ];
    }

    /**
     * Shared initialize implementation.
     */
    public function initialize(array $data): array
    {
        $sanitized = $this->sanitize($data);
        $this->validatePaymentData($sanitized);

        $orderId = $sanitized['orderId'] ?? $sanitized['reference_id'];

        $payload = [
            'merchant_id' => $this->config['merchant_id'] ?? '',
            'amount' => $sanitized['amount'],
            'currency' => $sanitized['currency'] ?? 'BDT',
            'order_id' => $orderId,
            'customer_name' => $sanitized['customer']['name'] ?? 'Customer',
            'customer_mobile' => $sanitized['customer']['mobile'] ?? '',
            'customer_email' => $sanitized['customer']['email'] ?? '',
            'description' => $sanitized['description'] ?? 'Payment',
            'callback_url' => $sanitized['callbackUrl'] ?? $sanitized['success_url'] ?? ($this->config['defaultCallbackUrl'] ?? ''),
            'cancel_url' => $sanitized['cancel_url'] ?? ($this->config['defaultCallbackUrl'] ?? ''),
        ];

        $response = $this->apiRequest('POST', '/payment/initialize', $payload);

        if ($response['status_code'] !== 200) {
            throw new InitializationException($this->gatewayName() . ': Failed to initialize payment (HTTP ' . $response['status_code'] . ').');
        }

        $responseData = $response['body'] ?? [];

        if (($responseData['status'] ?? '') !== 'success') {
            throw new InitializationException($this->gatewayName() . ': ' . ($responseData['message'] ?? 'Failed to initialize payment.'));
        }

        return $this->buildInitializeResponse($responseData);
    }

    /**
     * Shared process implementation (alias of verify for BD gateways).
     */
    public function process(array $data): array
    {
        $sanitized = $this->sanitize($data);

        if (empty($sanitized['transactionId']) && empty($sanitized['paymentID']) && empty($sanitized['reference_id'])) {
            throw new ProcessingException($this->gatewayName() . ': Missing transactionId for processing.');
        }

        return $this->verify($sanitized);
    }

    /**
     * Shared verify implementation.
     */
    public function verify(array $data): array
    {
        $sanitized = $this->sanitize($data);
        $transactionId = $sanitized['transactionId'] ?? $sanitized['paymentID'] ?? $sanitized['reference_id'] ?? null;

        if (empty($transactionId)) {
            throw new VerificationException($this->gatewayName() . ': Missing transactionId for verification.');
        }

        $response = $this->apiRequest('GET', '/payment/verify/' . $transactionId);

        if ($response['status_code'] !== 200) {
            throw new VerificationException($this->gatewayName() . ': Failed to verify payment (HTTP ' . $response['status_code'] . ').');
        }

        return $this->buildVerifyResponse((string) $transactionId, $response['body'] ?? []);
    }

    /**
     * Shared refund implementation.
     */
    public function refund(array $data): array
    {
        $sanitized = $this->sanitize($data);
        $transactionId = $sanitized['transactionId'] ?? $sanitized['paymentID'] ?? $sanitized['reference_id'] ?? null;

        if (empty($transactionId)) {
            throw new RefundException($this->gatewayName() . ': Missing transactionId for refund.');
        }

        $payload = [
            'transaction_id' => $transactionId,
            'amount' => $sanitized['amount'] ?? null,
            'reason' => $sanitized['reason'] ?? 'Refund request',
        ];

        $response = $this->apiRequest('POST', '/payment/refund', $payload);

        if ($response['status_code'] !== 200) {
            throw new RefundException($this->gatewayName() . ': Failed to process refund (HTTP ' . $response['status_code'] . ').');
        }

        $responseData = $response['body'] ?? [];

        if (($responseData['status'] ?? '') !== 'success') {
            throw new RefundException($this->gatewayName() . ': ' . ($responseData['message'] ?? 'Failed to process refund.'));
        }

        return $this->buildRefundResponse((string) $transactionId, $responseData);
    }
}
