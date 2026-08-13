<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class CheckoutComGateway extends PaymentGateway
{
    private const API_BASE_URL_SANDBOX = 'https://api.sandbox.checkout.com';
    private const API_BASE_URL_PRODUCTION = 'https://api.checkout.com';

    protected function getDefaultConfig(): array
    {
        return [
            'secretKey' => '', // Your Checkout.com Secret Key (sk_xxxx)
            'publicKey' => '', // Your Checkout.com Public Key (pk_xxxx), used for tokenization client-side usually
            'isSandbox' => true,
            'timeout' => 60,
            'webhookSecret' => '', // For verifying webhook signatures
            'processingChannelId' => '', // Optional: If you have a specific processing channel ID
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['secretKey'])) {
            throw new InvalidConfigurationException('CheckoutComGateway: secretKey is required.');
        }
        // PublicKey is mainly for client-side operations (generating tokens) but might be stored for reference.
        // if (empty($config['publicKey'])) {
        //     throw new InvalidConfigurationException('CheckoutComGateway: publicKey is required.');
        // }
    }

    private function getApiBaseUrl(): string
    {
        return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX : self::API_BASE_URL_PRODUCTION;
    }

    private function getRequestHeaders(?string $idempotencyKey = null): array
    {
        $headers = [
            'Authorization' => $this->config['secretKey'],
            'Content-Type' => 'application/json',
            // 'Cko-Sdk-Version' => 'laravelgpt-multipay-1.0.0' // Example custom SDK version
        ];
        if ($idempotencyKey) {
            $headers['Cko-Idempotency-Key'] = $idempotencyKey;
        }
        return $headers;
    }

    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('CheckoutComGateway: Invalid or missing amount (in minor units, e.g., cents).');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('CheckoutComGateway: Missing currency.');
        }
        if (empty($sanitizedData['orderId'])) {
            throw new InitializationException('CheckoutComGateway: Missing orderId (reference).');
        }
        // Either a source (token, card details, etc.) or customer ID + source for saved cards is needed.
        // This mock assumes a 'token' source is provided (e.g., from Checkout.com Frames).
        if (empty($sanitizedData['source']['token']) && empty($sanitizedData['source']['id'])) {
            throw new InitializationException('CheckoutComGateway: Missing payment source token or ID.');
        }

        $idempotencyKey = 'cko_pay_' . ($sanitizedData['orderId'] ?? uniqid());

        $payload = [
            'source' => $sanitizedData['source'], // e.g., ['type' => 'token', 'token' => 'tok_xxx'] or ['id' => 'src_xxx']
            'amount' => (int) $sanitizedData['amount'],
            'currency' => strtoupper($sanitizedData['currency']),
            'reference' => $sanitizedData['orderId'],
            'description' => $sanitizedData['description'] ?? 'Payment for ' . $sanitizedData['orderId'],
            'capture' => $sanitizedData['capture'] ?? true, // true for auto-capture, false for auth-only
            // 'customer' => ['email' => $sanitizedData['customerEmail'] ?? null, 'name' => $sanitizedData['customerName'] ?? null],
            // 'billing_descriptor' => ['name' => 'YourSiteName', 'city' => 'YourCity'],
            // 'success_url' => $sanitizedData['successUrl'] ?? null, // For 3DS or other redirects
            // 'failure_url' => $sanitizedData['failureUrl'] ?? null,
        ];

        if (!empty($this->config['processingChannelId'])) {
            $payload['processing_channel_id'] = $this->config['processingChannelId'];
        }

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/payments', $payload, $this->getRequestHeaders($idempotencyKey));
            // Mocked response
            if ($sanitizedData['amount'] == 99999) {
                 throw new InitializationException('CheckoutComGateway: Simulated API error during payment creation (amount 99999).');
            }

            $mockPaymentId = 'pay_' . uniqid();
            $mockStatus = 'Authorized'; // Or Captured, Pending, Declined, etc.
            if ($sanitizedData['source']['token'] === 'tok_declined') {
                $mockStatus = 'Declined';
            }
            if ($sanitizedData['capture'] === false && $mockStatus === 'Authorized') {
                // No change, it's correctly Authorized for later capture.
            } elseif ($sanitizedData['capture'] === true && $mockStatus === 'Authorized') {
                $mockStatus = 'Captured'; // Simulate auto-capture
            }

            $response = ['body' => [
                    'id' => $mockPaymentId,
                    'action_id' => 'act_' . uniqid(), // Usually present if further action needed like 3DS
                    'status' => $mockStatus,
                    'amount' => $payload['amount'],
                    'currency' => $payload['currency'],
                    'reference' => $payload['reference'],
                    'approved' => !in_array($mockStatus, ['Declined', 'Voided']), // Simplified approval status
                    '_links' => [
                        'self' => ['href' => $this->getApiBaseUrl() . '/payments/' . $mockPaymentId],
                        // 'redirect' => ['href' => 'https://example.com/3ds_redirect_url'] // If 3DS required
                    ]
                ],
                'status_code' => $mockStatus === 'Declined' ? 202 : ($mockStatus === 'Pending' ? 202 : 201) // 201 Created for sync success, 202 Accepted for async/redirect
            ];

            // A 202 response often means further action (like 3DS redirect) or webhook confirmation.
            if ($response['status_code'] === 202 && !empty($response['body']['_links']['redirect'])) {
                return [
                    'status' => 'pending_redirect',
                    'message' => 'Checkout.com payment requires redirection. Status: ' . $response['body']['status'],
                    'gatewayReferenceId' => $response['body']['id'],
                    'orderId' => $response['body']['reference'] ?? $sanitizedData['orderId'],
                    'redirectUrl' => $response['body']['_links']['redirect']['href'],
                    'paymentStatus' => $response['body']['status'],
                    'rawData' => $response['body']
                ];
            }

            if ($response['status_code'] >= 300 || empty($response['body']['id'])) {
                $errorMsg = $response['body']['data'][0]['error_codes'][0] ?? ('CheckoutComGateway: Failed to create payment. Status: ' . ($response['body']['status'] ?? 'unknown'));
                throw new InitializationException($errorMsg);
            }

            $isSuccess = ($response['body']['approved'] ?? false) && in_array($response['body']['status'], ['Authorized','Captured','Card Verified']);
            $isPending = ($response['body']['status'] ?? '') === 'Pending';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'Checkout.com payment status: ' . $response['body']['status'],
                'gatewayReferenceId' => $response['body']['id'],
                'orderId' => $response['body']['reference'] ?? $sanitizedData['orderId'],
                'paymentStatus' => $response['body']['status'],
                'rawData' => $response['body']
            ];
        } catch (\Exception $e) {
            throw new InitializationException('CheckoutComGateway: Payment creation failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function process(array $data): array
    {
        // Process webhook notifications. Signature verification is crucial.
        $sanitizedData = $this->sanitize($data); // Data from webhook

        // IMPORTANT: Verify `Cko-Signature` header using $this->config['webhookSecret']

        if (empty($sanitizedData['type']) || empty($sanitizedData['data']['id'])) {
            throw new ProcessingException('CheckoutComGateway: Invalid webhook data.');
        }

        $eventType = $sanitizedData['type']; // e.g., payment_approved, payment_captured, payment_declined, payment_refunded
        $paymentData = $sanitizedData['data'];
        $paymentId = $paymentData['id'];
        $status = $paymentData['status'] ?? 'Unknown'; // Status from the webhook payload

        // Mapping webhook types to internal statuses
        $isSuccess = in_array($eventType, ['payment_captured', 'payment_approved']); // payment_approved for auths
        $isFailed = in_array($eventType, ['payment_declined', 'payment_capture_declined', 'payment_void_declined']);
        $isRefund = in_array($eventType, ['payment_refunded', 'payment_refund_declined']);

        return [
            'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : ($isRefund ? 'refund_processed' : 'pending')),
            'message' => 'CheckoutComGateway webhook processed. Type: ' . $eventType . ', Status: ' . $status,
            'transactionId' => $paymentId,
            'orderId' => $paymentData['reference'] ?? null,
            'paymentStatus' => $status, // Detailed status from gateway
            'eventType' => $eventType,
            'rawData' => $sanitizedData
        ];
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['gatewayReferenceId'])) { // Checkout.com Payment ID (pay_xxx)
            throw new VerificationException('CheckoutComGateway: Missing gatewayReferenceId (Payment ID) for verification.');
        }
        $paymentId = $sanitizedData['gatewayReferenceId'];

        try {
            // $response = $this->httpClient('GET', $this->getApiBaseUrl() . '/payments/' . $paymentId, [], $this->getRequestHeaders());
            // Mocked response
            $mockStatus = 'Captured';
            if ($paymentId === 'pay_fail_verify') {
                $mockStatus = 'Declined';
            } elseif ($paymentId === 'pay_pending_verify') {
                $mockStatus = 'Pending';
            }
            $response = ['body' => [
                    'id' => $paymentId,
                    'status' => $mockStatus,
                    'approved' => !in_array($mockStatus, ['Declined', 'Voided']),
                    'amount' => $sanitizedData['original_amount_for_test'] ?? 1000,
                    'currency' => 'USD',
                    'reference' => 'order_' . uniqid(),
                    'actions' => [] // Actions taken on the payment
                ],
                'status_code' => 200
            ];

            if ($response['status_code'] !== 200 || empty($response['body']['id'])) {
                throw new VerificationException('CheckoutComGateway: Failed to verify payment. ' . ($response['body']['error_type'] ?? 'API error'));
            }

            $paymentDetails = $response['body'];
            $paymentStatus = $paymentDetails['status'] ?? 'Unknown';
            $isSuccess = ($paymentDetails['approved'] ?? false) && in_array($paymentStatus, ['Authorized', 'Captured', 'Card Verified']);
            $isPending = $paymentStatus === 'Pending';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'CheckoutComGateway verification result: ' . $paymentStatus,
                'transactionId' => $paymentDetails['id'],
                'orderId' => $paymentDetails['reference'] ?? null,
                'paymentStatus' => $paymentStatus,
                'rawData' => $paymentDetails
            ];
        } catch (\Exception $e) {
            throw new VerificationException('CheckoutComGateway: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Checkout.com Payment ID (pay_xxx) to refund
            throw new RefundException('CheckoutComGateway: Missing transactionId for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('CheckoutComGateway: Invalid or missing amount for refund (minor units).');
        }

        $paymentId = $sanitizedData['transactionId'];
        $idempotencyKey = 'cko_refund_' . ($sanitizedData['refundReference'] ?? uniqid());

        $payload = [
            'amount' => (int) $sanitizedData['amount'],
            'reference' => $sanitizedData['refundReference'] ?? 'refund_for_' . $paymentId,
            'metadata' => $sanitizedData['metadata'] ?? [],
        ];

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/payments/' . $paymentId . '/refunds', $payload, $this->getRequestHeaders($idempotencyKey));
            // Mocked response
            if (($sanitizedData['amount'] ?? 0) == 99999) {
                 throw new RefundException('CheckoutComGateway: API rejected refund (simulated amount 99999).');
            }
            $mockActionId = 'act_ref_' . uniqid();
            $response = ['body' => [
                    'action_id' => $mockActionId,      // ID of the refund action
                    'reference' => $payload['reference'] // Your reference for the refund
                    // 'processing' => ['acquirer_transaction_id' => 'xxxx'] // May contain further processing info
                ],
                'status_code' => 202 // Refund request accepted, processed asynchronously
            ];

            if ($response['status_code'] !== 202 || empty($response['body']['action_id'])) {
                throw new RefundException('CheckoutComGateway: Failed to process refund. ' . ($response['body']['error_type'] ?? 'Unknown error'));
            }

            // Refund status will be confirmed via webhook (payment_refunded or payment_refund_declined).
            return [
                'status' => 'pending', // Refund is pending, final status via webhook
                'message' => 'CheckoutComGateway refund request accepted. Action ID: ' . $response['body']['action_id'],
                'refundId' => $response['body']['action_id'], // This is the action ID for the refund
                'transactionId' => $paymentId,
                'paymentStatus' => 'refund_pending',
                'rawData' => $response['body']
            ];
        } catch (\Exception $e) {
            throw new RefundException('CheckoutComGateway: Refund request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
