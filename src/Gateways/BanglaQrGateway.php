<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

use Hadi\Payment\Exceptions\InitializationException;

/**
 * BanglaQR payment gateway for Bangladesh.
 *
 * BanglaQR is the interoperable QR-based payment scheme operated by the
 * Bangladesh Bank. Payments are initiated by requesting a merchant QR code;
 * the customer completes the payment by scanning the code with any
 * participating wallet or banking app, after which the merchant verifies the
 * transaction.
 */
class BanglaQrGateway extends BangladeshGateway
{
    protected function apiBaseUrls(): array
    {
        return [
            'sandbox' => 'https://sandbox.banglaqr.gov.bd/api/v1',
            'production' => 'https://api.banglaqr.gov.bd/api/v1',
        ];
    }

    protected function requiredConfigKeys(): array
    {
        return ['merchant_id', 'api_key'];
    }

    protected function gatewaySlug(): string
    {
        return 'banglaqr';
    }

    /**
     * Initialize a QR payment and return the merchant QR payload.
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
            'description' => $sanitized['description'] ?? 'Payment',
            'callback_url' => $sanitized['callbackUrl'] ?? $sanitized['success_url'] ?? ($this->config['defaultCallbackUrl'] ?? ''),
        ];

        $response = $this->apiRequest('POST', '/qr/generate', $payload);

        if ($response['status_code'] !== 200) {
            throw new InitializationException($this->gatewayName() . ': Failed to generate QR payment (HTTP ' . $response['status_code'] . ').');
        }

        $responseData = $response['body'] ?? [];

        if (($responseData['status'] ?? '') !== 'success') {
            throw new InitializationException($this->gatewayName() . ': ' . ($responseData['message'] ?? 'Failed to generate QR payment.'));
        }

        return $this->buildQrInitializeResponse($responseData);
    }

    /**
     * Build the normalized QR initialize response, exposing the QR payload so
     * host apps can render it (raw, image, or data) and display it at checkout.
     */
    protected function buildQrInitializeResponse(array $responseData): array
    {
        $data = $responseData['data'] ?? $responseData;

        return [
            'status' => 'pending_user_action',
            'message' => $this->gatewayName() . ' QR payment initialized. Ask the customer to scan the code.',
            'gatewayReferenceId' => $data['qr_reference'] ?? $data['transaction_id'] ?? $data['payment_id'] ?? null,
            'paymentUrl' => $data['payment_url'] ?? $data['redirect_url'] ?? null,
            'qrContent' => $data['qr_content'] ?? $data['qr_data'] ?? null,
            'qrImage' => $data['qr_image'] ?? $data['qr_base64'] ?? null,
            'expiresAt' => $data['expires_at'] ?? $data['expiry'] ?? null,
            'rawData' => $responseData,
        ];
    }
}
