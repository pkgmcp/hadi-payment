<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class PayoneerGateway extends PaymentGateway
{
    private const API_BASE_URL_SANDBOX = 'https://api.sandbox.payoneer.com'; // Example
    private const API_BASE_URL_PRODUCTION = 'https://api.payoneer.com'; // Example

    protected function getDefaultConfig(): array
    {
        return [
            'programId' => '', // Your Payoneer Program ID
            'apiUsername' => '', // Your Payoneer API Username
            'apiPassword' => '', // Your Payoneer API Password
            'isSandbox' => true,
            'payoutCurrency' => 'USD', // Default currency for payouts
            'notificationUrl' => 'https://example.com/payoneer/webhook',
            'timeout' => 60,
        ];
    }

    protected function validateConfig(array $config): void
    {
        foreach (['programId', 'apiUsername', 'apiPassword'] as $key) {
            if (empty($config[$key])) {
                throw new InvalidConfigurationException("Payoneer: {$key} is required.");
            }
        }
    }

    private function getApiBaseUrl(): string
    {
        return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX : self::API_BASE_URL_PRODUCTION;
    }

    // Payoneer uses OAuth2 for authentication, this is a simplified mock.
    private function getAccessToken(): string
    {
        // In a real scenario, you'd make a POST request to Payoneer's token endpoint
        // with client_credentials grant type and your API username/password (or client ID/secret).
        // $auth = base64_encode($this->config['apiUsername'] . ':' . $this->config['apiPassword']);
        // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/v2/oauth/token',
        // ['grant_type' => 'client_credentials'],
        // ['Authorization' => 'Basic ' . $auth, 'Content-Type' => 'application/x-www-form-urlencoded']);
        // if ($response['status_code'] !== 200 || empty($response['body']['access_token'])) {
        //     throw new InitializationException('Payoneer: Failed to obtain access token.');
        // }
        // return $response['body']['access_token'];
        if ($this->config['apiUsername'] === 'force_token_error') {
            throw new InitializationException('Payoneer: Failed to obtain access token (simulated).');
        }
        return 'mock_payoneer_access_token_' . uniqid();
    }

    private function getRequestHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ];
    }

    public function initialize(array $data): array
    {
        // Payoneer's primary use case is often payouts (sending money), not collecting payments directly like Stripe/PayPal checkout.
        // However, they do have APIs for requesting payments from other Payoneer users or via a payment link.
        // This mock will simulate requesting a payment.
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('Payoneer: Invalid or missing amount.');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('Payoneer: Missing currency.');
        }
        if (empty($sanitizedData['payeeEmail'])) { // Email of the Payoneer user to request payment from
            throw new InitializationException('Payoneer: Missing payeeEmail for payment request.');
        }
        if (empty($sanitizedData['description'])) {
            throw new InitializationException('Payoneer: Missing description for payment request.');
        }
        if (empty($sanitizedData['requestId'])) { // Your unique ID for this request
            throw new InitializationException('Payoneer: Missing requestId.');
        }

        $payload = [
            'client_reference_id' => $sanitizedData['requestId'],
            'payee' => [
                'type' => 'EMAIL',
                'id' => $sanitizedData['payeeEmail'],
            ],
            'amount' => sprintf('%.2f', $sanitizedData['amount']),
            'currency' => strtoupper($sanitizedData['currency']),
            'description' => $sanitizedData['description'],
            'payment_method_types' => ['PAYONEER_BALANCE'], // Example, or allow more
            // 'notification_url' => $this->config['notificationUrl'], // If applicable
        ];

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/v4/programs/' . $this->config['programId'] . '/payment-requests', $payload, $this->getRequestHeaders());
            // Mocked response
            if ($sanitizedData['amount'] == 999) {
                 throw new InitializationException('Payoneer: API rejected payment request (simulated).');
            }
            $mockPaymentRequestId = 'pr_' . strtoupper(uniqid());
            $mockPaymentLink = $this->getApiBaseUrl() . '/payments/pay?id=' . $mockPaymentRequestId; // Conceptual link

            $response = ['body' => [
                    'id' => $mockPaymentRequestId,
                    'status' => 'PENDING_APPROVAL', // Or PENDING_PAYMENT
                    'client_reference_id' => $sanitizedData['requestId'],
                    'payment_url' => $mockPaymentLink, // If Payoneer provides a direct link
                ],
                'status_code' => 201 // Created
            ];

            if ($response['status_code'] !== 201 || empty($response['body']['id'])) {
                throw new InitializationException('Payoneer: Failed to create payment request. ' . ($response['body']['error']['description'] ?? 'Unknown API error'));
            }

            return [
                'status' => 'pending', // Payment request created, waiting for payee action or approval
                'message' => 'Payoneer payment request created. Status: ' . $response['body']['status'],
                'gatewayReferenceId' => $response['body']['id'], // Payoneer Payment Request ID
                'paymentUrl' => $response['body']['payment_url'] ?? null,
                'orderId' => $sanitizedData['requestId'],
                'rawData' => $response['body']
            ];
        } catch (\Exception $e) {
            throw new InitializationException('Payoneer: Payment request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function process(array $data): array
    {
        // Process for Payoneer often means handling a webhook notification about the payment status.
        $sanitizedData = $this->sanitize($data); // Data from webhook

        if (empty($sanitizedData['event_type']) || empty($sanitizedData['payload']['id'])) {
            throw new ProcessingException('Payoneer: Invalid webhook data.');
        }

        // Example webhook event types: payment_request.paid, payment_request.failed
        $eventType = $sanitizedData['event_type'];
        $payload = $sanitizedData['payload'];
        $paymentRequestId = $payload['id'];
        $status = $payload['status'] ?? 'UNKNOWN';

        $isSuccess = ($eventType === 'payment_request.paid' || $status === 'PAID');
        $isFailed = ($eventType === 'payment_request.failed' || $status === 'DECLINED' || $status === 'CANCELLED');

        return [
            'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
            'message' => 'Payoneer payment processed via webhook. Event: ' . $eventType . ', Status: ' . $status,
            'transactionId' => $payload['payment_id'] ?? $paymentRequestId, // Actual payment ID if available
            'orderId' => $payload['client_reference_id'] ?? null,
            'paymentStatus' => $status,
            'rawData' => $sanitizedData
        ];
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['gatewayReferenceId'])) { // Payoneer Payment Request ID
            throw new VerificationException('Payoneer: Missing gatewayReferenceId (Payment Request ID) for verification.');
        }
        $paymentRequestId = $sanitizedData['gatewayReferenceId'];

        try {
            // $response = $this->httpClient('GET', $this->getApiBaseUrl() . '/v4/programs/' . $this->config['programId'] . '/payment-requests/' . $paymentRequestId, [], $this->getRequestHeaders());
            // Mocked response
            $mockStatus = 'PAID';
            if ($paymentRequestId === 'fail_verify_ref') {
                $mockStatus = 'DECLINED';
            } elseif ($paymentRequestId === 'pending_verify_ref') {
                $mockStatus = 'PENDING_PAYMENT';
            }
            $response = ['body' => [
                    'id' => $paymentRequestId,
                    'status' => $mockStatus, // e.g., PENDING_APPROVAL, PENDING_PAYMENT, PAID, DECLINED, CANCELLED
                    'client_reference_id' => 'mock_client_ref_' . uniqid(),
                    'amount' => $sanitizedData['original_amount_for_test'] ?? '100.00',
                    'currency' => $this->config['payoutCurrency'],
                ],
                'status_code' => 200
            ];

            if ($response['status_code'] !== 200 || empty($response['body']['status'])) {
                throw new VerificationException('Payoneer: Failed to verify payment request. ' . ($response['body']['error']['description'] ?? 'API error'));
            }

            $paymentStatus = $response['body']['status'] ?? 'UNKNOWN';
            $isSuccess = $paymentStatus === 'PAID';
            $isPending = in_array($paymentStatus, ['PENDING_APPROVAL', 'PENDING_PAYMENT']);

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'Payoneer verification result: ' . $paymentStatus,
                'transactionId' => $paymentRequestId, // Payment Request ID, actual payment ID might differ
                'orderId' => $response['body']['client_reference_id'] ?? null,
                'paymentStatus' => $paymentStatus,
                'rawData' => $response['body']
            ];
        } catch (\Exception $e) {
            throw new VerificationException('Payoneer: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        // Refunding a payment requested via Payoneer might involve cancelling the request if not paid,
        // or a more complex process if it's already paid out.
        // Payoneer's APIs are more geared towards payouts (sending funds).
        // This is a conceptual mock for cancelling a payment request or refunding a received payment if API supports.
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Payoneer Payment Request ID or actual Payment ID
            throw new RefundException('Payoneer: Missing transactionId for refund/cancellation.');
        }

        $paymentId = $sanitizedData['transactionId'];
        // Example for cancelling a payment request:
        // $endpoint = $this->getApiBaseUrl() . '/v4/programs/' . $this->config['programId'] . '/payment-requests/' . $paymentId . '/cancel';
        // Or for refunding a completed payment if such API exists.
        // $payload = ['reason' => $sanitizedData['reason'] ?? 'Merchant requested refund'];

        try {
            // $response = $this->httpClient('POST', $endpoint, $payload, $this->getRequestHeaders());
            // Mocked response (simulating a cancellation or a refund status)
            if (($sanitizedData['amount'] ?? 0) == 999) { // Simulate error condition
                 throw new RefundException('Payoneer: API rejected refund/cancellation (simulated).');
            }
            $response = ['body' => [
                    'id' => $paymentId,
                    'status' => 'CANCELLED', // Or REFUNDED, REFUND_PENDING
                    'message' => 'Payment request cancelled successfully.'
                ],
                'status_code' => 200
            ];

            if ($response['status_code'] !== 200 || empty($response['body']['status'])) {
                throw new RefundException('Payoneer: Failed to process refund/cancellation. ' . ($response['body']['error']['description'] ?? 'Unknown error'));
            }

            $refundStatus = $response['body']['status'];
            $isSuccess = in_array($refundStatus, ['CANCELLED', 'REFUNDED']);
            $isPending = $refundStatus === 'REFUND_PENDING';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'Payoneer refund/cancellation status: ' . $refundStatus,
                'refundId' => $paymentId, // Using the original ID as reference for cancellation/refund
                'paymentStatus' => $refundStatus,
                'rawData' => $response['body']
            ];
        } catch (\Exception $e) {
            throw new RefundException('Payoneer: Refund/cancellation request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
