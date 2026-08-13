<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class SquareGateway extends PaymentGateway
{
    private const API_BASE_URL_SANDBOX = 'https://connect.squareupsandbox.com';
    private const API_BASE_URL_PRODUCTION = 'https://connect.squareup.com';

    protected function getDefaultConfig(): array
    {
        return [
            'accessToken' => '', // Square Access Token
            'locationId' => '',  // Square Location ID
            'isSandbox' => true,
            'timeout' => 60,
            'idempotencyKey' => null, // Recommended for POST requests
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['accessToken'])) {
            throw new InvalidConfigurationException('SquareGateway: accessToken is required.');
        }
        // Location ID might not be needed for all API calls, but often is for payments.
        // if (empty($config['locationId'])) {
        //     throw new InvalidConfigurationException('SquareGateway: locationId is required.');
        // }
    }

    private function getApiBaseUrl(): string
    {
        return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX : self::API_BASE_URL_PRODUCTION;
    }

    private function getRequestHeaders(): array
    {
        $headers = [
            'Square-Version' => '2023-10-18', // Example API version
            'Authorization' => 'Bearer ' . $this->config['accessToken'],
            'Content-Type' => 'application/json',
        ];
        return $headers;
    }

    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('SquareGateway: Invalid or missing amount (must be in cents).');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('SquareGateway: Missing currency.');
        }
        if (empty($sanitizedData['orderId'])) { // Your internal order ID
            throw new InitializationException('SquareGateway: Missing orderId.');
        }
        // Square typically requires a source_id (e.g., card nonce) obtained from a client-side SDK,
        // or uses a checkout API for redirection. This mock simulates creating a payment.

        $idempotencyKey = $this->config['idempotencyKey'] ?? uniqid('sq_');

        $payload = [
            'idempotency_key' => $idempotencyKey,
            'source_id' => $sanitizedData['source_id'] ?? 'cnon:card-nonce-ok', // Placeholder for card nonce
            'amount_money' => [
                'amount' => (int) $sanitizedData['amount'], // Amount in smallest currency unit (e.g., cents)
                'currency' => strtoupper($sanitizedData['currency']),
            ],
            'order_id' => $sanitizedData['orderId'], // Link to your order
            // 'autocomplete' => true, // Or false to authorize and capture later
            'note' => $sanitizedData['description'] ?? 'Payment for order ' . $sanitizedData['orderId'],
        ];

        // If locationId is configured and required for this endpoint
        if (!empty($this->config['locationId'])) {
            // $payload['location_id'] = $this->config['locationId']; // Some endpoints like Payments API need it in the body
        }

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/v2/payments', $payload, $this->getRequestHeaders());
            // Mocked response
            if ($sanitizedData['amount'] == 99999) { // Simulate a specific error
                throw new InitializationException('SquareGateway: Simulated API error creating payment.');
            }

            $mockGatewayReferenceId = 'sq_pay_' . uniqid();
            $response = ['body' => [
                    'payment' => [
                        'id' => $mockGatewayReferenceId,
                        'status' => 'COMPLETED', // Or PENDING, APPROVED
                        'amount_money' => $payload['amount_money'],
                        'order_id' => $payload['order_id'],
                        'receipt_url' => $this->getApiBaseUrl() . '/receipts/mock/' . $mockGatewayReferenceId,
                    ]
                ],
                'status_code' => 200
            ];

            if ($response['status_code'] !== 200 || empty($response['body']['payment']['id'])) {
                // $errorMessage = $response['body']['errors'][0]['detail'] ?? 'Unknown API error';
                throw new InitializationException('SquareGateway: Failed to create payment. (mocked error)');
            }

            $payment = $response['body']['payment'];
            return [
                'status' => strtolower($payment['status']) === 'completed' ? 'success' : 'pending',
                'message' => 'Square payment processed. Status: ' . $payment['status'],
                'gatewayReferenceId' => $payment['id'],
                'orderId' => $payment['order_id'] ?? $sanitizedData['orderId'],
                'receiptUrl' => $payment['receipt_url'] ?? null,
                'rawData' => $payment
            ];
        } catch (\Exception $e) {
            throw new InitializationException('SquareGateway: Payment creation failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function process(array $data): array
    {
        // Square primarily uses webhooks for asynchronous updates.
        $sanitizedData = $this->sanitize($data); // Data from webhook (ensure it's verified)

        if (empty($sanitizedData['type']) || empty($sanitizedData['data']['object']['payment'])) {
            throw new ProcessingException('SquareGateway: Invalid webhook data.');
        }

        $eventType = $sanitizedData['type']; // e.g., "payment.updated"
        $payment = $sanitizedData['data']['object']['payment'];
        $paymentId = $payment['id'];
        $status = $payment['status'] ?? 'UNKNOWN'; // COMPLETED, CANCELED, FAILED

        $isSuccess = ($eventType === 'payment.updated' && $status === 'COMPLETED');
        $isFailed = in_array($status, ['CANCELED', 'FAILED']);

        return [
            'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
            'message' => 'SquareGateway payment processed via webhook. Event: ' . $eventType . ', Status: ' . $status,
            'transactionId' => $paymentId,
            'orderId' => $payment['order_id'] ?? null,
            'paymentStatus' => $status,
            'rawData' => $sanitizedData
        ];
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['gatewayReferenceId'])) { // Square Payment ID
            throw new VerificationException('SquareGateway: Missing gatewayReferenceId (Payment ID) for verification.');
        }
        $paymentId = $sanitizedData['gatewayReferenceId'];

        try {
            // $response = $this->httpClient('GET', $this->getApiBaseUrl() . '/v2/payments/' . $paymentId, [], $this->getRequestHeaders());
            // Mocked response
            $mockStatus = 'COMPLETED';
            if ($paymentId === 'fail_verify_ref') {
                $mockStatus = 'FAILED';
            } elseif ($paymentId === 'pending_verify_ref') {
                $mockStatus = 'APPROVED'; // Or some other pending state
            }
            $response = ['body' => [
                    'payment' => [
                        'id' => $paymentId,
                        'status' => $mockStatus,
                        'order_id' => 'mock_order_' . uniqid(),
                        'amount_money' => ['amount' => $sanitizedData['original_amount_for_test'] ?? 1000, 'currency' => 'USD'],
                    ]
                ],
                'status_code' => 200
            ];

            if ($response['status_code'] !== 200 || empty($response['body']['payment']['status'])) {
                // $errorMessage = $response['body']['errors'][0]['detail'] ?? 'API error';
                throw new VerificationException('SquareGateway: Failed to verify payment. (mocked error)');
            }

            $payment = $response['body']['payment'];
            $paymentStatus = $payment['status'] ?? 'UNKNOWN';
            $isSuccess = $paymentStatus === 'COMPLETED';
            $isPending = in_array($paymentStatus, ['APPROVED', 'PENDING']); // APPROVED means authorized, needs capture

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'SquareGateway verification result: ' . $paymentStatus,
                'transactionId' => $payment['id'],
                'orderId' => $payment['order_id'] ?? null,
                'paymentStatus' => $paymentStatus,
                'rawData' => $payment
            ];
        } catch (\Exception $e) {
            throw new VerificationException('SquareGateway: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Square Payment ID to be refunded
            throw new RefundException('SquareGateway: Missing transactionId for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('SquareGateway: Invalid or missing amount for refund (must be in cents).');
        }
        if (empty($sanitizedData['orderId'])) { // Your internal order ID, useful for idempotency
            throw new RefundException('SquareGateway: Missing orderId for refund idempotency.');
        }

        $paymentId = $sanitizedData['transactionId'];
        $idempotencyKey = $this->config['idempotencyKey'] ?? uniqid('sq_refund_');

        $payload = [
            'idempotency_key' => $idempotencyKey,
            'payment_id' => $paymentId,
            'amount_money' => [
                'amount' => (int) $sanitizedData['amount'],
                'currency' => strtoupper($sanitizedData['currency'] ?? 'USD'), // Get currency from original payment or config
            ],
            'reason' => $sanitizedData['reason'] ?? 'Merchant requested refund',
        ];

        // If locationId is configured and required for this endpoint
        // if (!empty($this->config['locationId'])) {
        //     $payload['location_id'] = $this->config['locationId'];
        // }

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/v2/refunds', $payload, $this->getRequestHeaders());
            // Mocked response
            if (($sanitizedData['amount'] ?? 0) == 99999) {
                 throw new RefundException('SquareGateway: API rejected refund (simulated).');
            }
            $mockRefundId = 'sq_ref_' . uniqid();
            $response = ['body' => [
                    'refund' => [
                        'id' => $mockRefundId,
                        'status' => 'PENDING', // Refunds can be PENDING, COMPLETED, REJECTED, FAILED
                        'payment_id' => $paymentId,
                        'order_id' => $sanitizedData['orderId'],
                        'amount_money' => $payload['amount_money'],
                        'reason' => $payload['reason']
                    ]
                ],
                'status_code' => 200
            ];

            if ($response['status_code'] !== 200 || empty($response['body']['refund']['id'])) {
                // $errorMessage = $response['body']['errors'][0]['detail'] ?? 'Unknown error';
                throw new RefundException('SquareGateway: Failed to process refund. (mocked error)');
            }

            $refund = $response['body']['refund'];
            $refundStatus = $refund['status'];
            $isSuccess = $refundStatus === 'COMPLETED';
            $isPending = $refundStatus === 'PENDING';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'SquareGateway refund status: ' . $refundStatus,
                'refundId' => $refund['id'],
                'transactionId' => $refund['payment_id'],
                'paymentStatus' => $refundStatus, // Reflects the refund status
                'rawData' => $refund
            ];
        } catch (\Exception $e) {
            throw new RefundException('SquareGateway: Refund request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
