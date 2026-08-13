<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\RefundException;
use Hadi\Payment\Exceptions\VerificationException;

/**
 * Shared base class for virtual-card (card issuing) gateways.
 *
 * These gateways issue a virtual card on initialize, resolve the card state on
 * verify, and credit/refund funds on refund. Providers share the same
 * HTTP + validation + status mapping flow.
 */
abstract class VirtualCardGateway extends PaymentGateway
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
     * Lowercase gateway slug used in messages and defaults (e.g. "privacy").
     */
    abstract protected function gatewaySlug(): string;

    /**
     * Human friendly display name (defaults to ucfirst(slug)).
     */
    protected function gatewayDisplayName(): string
    {
        return ucfirst($this->gatewaySlug());
    }

    protected function getDefaultConfig(): array
    {
        return [
            'api_key' => '',
            'sandbox' => true,
            'timeout' => 30,
            'defaultCallbackUrl' => 'https://example.com/hadi-payment/callback',
        ];
    }

    protected function validateConfig(array $config): void
    {
        foreach ($this->requiredConfigKeys() as $key) {
            if (empty($config[$key])) {
                throw new \Hadi\Payment\Exceptions\InvalidConfigurationException(
                    $this->gatewayDisplayName() . ": {$key} is required."
                );
            }
        }
    }

    protected function gatewayName(): string
    {
        return $this->gatewayDisplayName();
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
     * Validate the common card-issuing payload shape.
     */
    protected function validatePaymentData(array $data): void
    {
        if (empty($data['amount']) || !is_numeric($data['amount']) || (float) $data['amount'] <= 0) {
            throw new InitializationException($this->gatewayName() . ': Invalid or missing amount.');
        }

        if (empty($data['currency'])) {
            throw new InitializationException($this->gatewayName() . ': Missing currency code.');
        }

        if (empty($data['orderId'])) {
            throw new InitializationException($this->gatewayName() . ': Missing orderId.');
        }
    }

    /**
     * Map a provider status string to the normalized Hadi status vocabulary.
     */
    protected function mapStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'PENDING', 'PROCESSING', 'INITIATED', 'ACTIVE', 'FUNDED' => 'pending',
            'SUCCESS', 'COMPLETED', 'SUCCEEDED', 'AUTHORIZED', 'SETTLED' => 'success',
            'FAILED', 'CANCELLED', 'CANCELED', 'REVERSED', 'BLOCKED', 'CLOSED' => 'failed',
            'REFUNDED', 'CREDITED' => 'refunded',
            default => 'unknown',
        };
    }

    /**
     * Build the normalized initialize response (virtual card issued).
     */
    protected function buildInitializeResponse(array $responseData): array
    {
        $data = $responseData['data'] ?? $responseData;

        return [
            'status' => 'pending_user_action',
            'message' => $this->gatewayName() . ' virtual card issued.',
            'gatewayReferenceId' => $data['card_id'] ?? $data['cardId'] ?? $data['id'] ?? null,
            'paymentUrl' => $data['card_url'] ?? $data['url'] ?? null,
            'rawData' => $responseData,
        ];
    }

    /**
     * Build the normalized verify response (card state).
     */
    protected function buildVerifyResponse(string $cardId, array $responseData): array
    {
        $data = $responseData['data'] ?? $responseData;
        $status = $this->mapStatus((string) ($data['status'] ?? 'PENDING'));

        return [
            'status' => $status,
            'message' => $this->gatewayName() . ' card status: ' . ($data['status'] ?? 'unknown'),
            'transactionId' => $cardId,
            'cardId' => $data['card_id'] ?? $data['cardId'] ?? $cardId,
            'last4' => $data['last4'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            'rawData' => $responseData,
        ];
    }

    /**
     * Build the normalized refund response (funds credited to the card).
     */
    protected function buildRefundResponse(string $cardId, array $responseData): array
    {
        $data = $responseData['data'] ?? $responseData;

        return [
            'status' => 'success',
            'message' => $this->gatewayName() . ' funds credited to virtual card.',
            'transactionId' => $cardId,
            'refundId' => $data['refund_id'] ?? $data['transfer_id'] ?? $data['id'] ?? null,
            'amount' => $data['credited_amount'] ?? $data['amount'] ?? null,
            'rawData' => $responseData,
        ];
    }

    /**
     * Shared initialize implementation: issue a virtual card.
     */
    public function initialize(array $data): array
    {
        $sanitized = $this->sanitize($data);
        $this->validatePaymentData($sanitized);

        $payload = [
            'amount' => $sanitized['amount'],
            'currency' => $sanitized['currency'],
            'order_id' => $sanitized['orderId'],
            'customer_name' => $sanitized['customer']['name'] ?? 'Customer',
            'customer_email' => $sanitized['customer']['email'] ?? '',
            'callback_url' => $sanitized['callbackUrl'] ?? ($this->config['defaultCallbackUrl'] ?? ''),
        ];

        $response = $this->apiRequest('POST', '/cards/issue', $payload);

        if ($response['status_code'] !== 200) {
            throw new InitializationException($this->gatewayName() . ': Failed to issue virtual card (HTTP ' . $response['status_code'] . ').');
        }

        $responseData = $response['body'] ?? [];

        if (($responseData['status'] ?? '') !== 'success') {
            throw new InitializationException($this->gatewayName() . ': ' . ($responseData['message'] ?? 'Failed to issue virtual card.'));
        }

        return $this->buildInitializeResponse($responseData);
    }

    /**
     * Shared process implementation (alias of verify for card gateways).
     */
    public function process(array $data): array
    {
        $sanitized = $this->sanitize($data);

        if (empty($sanitized['cardId']) && empty($sanitized['transactionId']) && empty($sanitized['reference_id'])) {
            throw new ProcessingException($this->gatewayName() . ': Missing cardId for processing.');
        }

        return $this->verify($sanitized);
    }

    /**
     * Shared verify implementation: resolve the virtual card state.
     */
    public function verify(array $data): array
    {
        $sanitized = $this->sanitize($data);
        $cardId = $sanitized['cardId'] ?? $sanitized['transactionId'] ?? $sanitized['reference_id'] ?? null;

        if (empty($cardId)) {
            throw new VerificationException($this->gatewayName() . ': Missing cardId for verification.');
        }

        $response = $this->apiRequest('GET', '/cards/' . $cardId);

        if ($response['status_code'] !== 200) {
            throw new VerificationException($this->gatewayName() . ': Failed to verify card (HTTP ' . $response['status_code'] . ').');
        }

        return $this->buildVerifyResponse((string) $cardId, $response['body'] ?? []);
    }

    /**
     * Shared refund implementation: credit funds to the virtual card.
     */
    public function refund(array $data): array
    {
        $sanitized = $this->sanitize($data);
        $cardId = $sanitized['cardId'] ?? $sanitized['transactionId'] ?? $sanitized['reference_id'] ?? null;

        if (empty($cardId)) {
            throw new RefundException($this->gatewayName() . ': Missing cardId for refund.');
        }

        $payload = [
            'card_id' => $cardId,
            'amount' => $sanitized['amount'] ?? null,
            'reason' => $sanitized['reason'] ?? 'Funds credit',
        ];

        $response = $this->apiRequest('POST', '/cards/credit', $payload);

        if ($response['status_code'] !== 200) {
            throw new RefundException($this->gatewayName() . ': Failed to credit card (HTTP ' . $response['status_code'] . ').');
        }

        $responseData = $response['body'] ?? [];

        if (($responseData['status'] ?? '') !== 'success') {
            throw new RefundException($this->gatewayName() . ': ' . ($responseData['message'] ?? 'Failed to credit card.'));
        }

        return $this->buildRefundResponse((string) $cardId, $responseData);
    }
}
