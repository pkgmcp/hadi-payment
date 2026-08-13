<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class InstamojoGateway extends PaymentGateway
{
    private const API_BASE_URL_SANDBOX = 'https://test.instamojo.com/api/1.1/';
    private const API_BASE_URL_PRODUCTION = 'https://www.instamojo.com/api/1.1/'; // Or v2

    protected function getDefaultConfig(): array
    {
        return [
            'apiKey' => '',         // Your Instamojo API Key
            'authToken' => '',      // Your Instamojo Auth Token
            'isSandbox' => true,
            'timeout' => 60,
            'webhookSecret' => '',  // Optional: Salt/Secret for webhook verification if provided
            'redirectUrl' => 'https://example.com/instamojo/return', // Your redirect URL after payment
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['apiKey'])) {
            throw new InvalidConfigurationException('InstamojoGateway: apiKey is required.');
        }
        if (empty($config['authToken'])) {
            throw new InvalidConfigurationException('InstamojoGateway: authToken is required.');
        }
        if (empty($config['redirectUrl'])) {
            throw new InvalidConfigurationException('InstamojoGateway: redirectUrl is required.');
        }
    }

    private function getApiBaseUrl(): string
    {
        return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX : self::API_BASE_URL_PRODUCTION;
    }

    private function getRequestHeaders(): array
    {
        return [
            'X-Api-Key' => $this->config['apiKey'],
            'X-Auth-Token' => $this->config['authToken'],
            'Content-Type' => 'application/x-www-form-urlencoded', // API v1.1 often uses form-urlencoded
            'Accept' => 'application/json',
        ];
    }

    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('InstamojoGateway: Invalid or missing amount.');
        }
        if (empty($sanitizedData['purpose'])) {
            throw new InitializationException('InstamojoGateway: Missing purpose for the payment.');
        }
        if (empty($sanitizedData['buyerName'])) {
            throw new InitializationException('InstamojoGateway: Missing buyerName.');
        }
        if (empty($sanitizedData['email'])) {
            throw new InitializationException('InstamojoGateway: Missing email.');
        }
        // orderId is optional for Instamojo but good practice for your system.
        // Instamojo uses `transaction_id` internally for their payment requests.

        $payload = [
            'amount' => sprintf('%.2f', $sanitizedData['amount']),
            'purpose' => $sanitizedData['purpose'],
            'buyer_name' => $sanitizedData['buyerName'],
            'email' => $sanitizedData['email'],
            'phone' => $sanitizedData['phone'] ?? null,
            'redirect_url' => $this->config['redirectUrl'],
            'webhook' => $sanitizedData['webhookUrl'] ?? ($this->config['statusUrl'] ?? null), // Instamojo's term for webhook
            'allow_repeated_payments' => $sanitizedData['allowRepeatedPayments'] ?? false,
            'send_email' => $sanitizedData['sendEmail'] ?? false, // Send email receipt from Instamojo
            'send_sms' => $sanitizedData['sendSms'] ?? false,
            'transaction_id' => $sanitizedData['orderId'] ?? null, // Your internal transaction ID (optional for Instamojo)
        ];

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . 'payment-requests/', http_build_query($payload), $this->getRequestHeaders(), true);
            // Mocked response
            if ($sanitizedData['amount'] == 999.99) {
                throw new InitializationException('InstamojoGateway: Simulated API error creating payment request (amount 999.99).');
            }
            $mockPaymentRequestId = 'im_pr_' . uniqid();
            $mockLongUrl = ($this->config['isSandbox'] ? 'https://test.instamojo.com/' : 'https://www.instamojo.com/') . '@user/' . $mockPaymentRequestId;

            $apiResponse = [
                'success' => true,
                'payment_request' => [
                    'id' => $mockPaymentRequestId,
                    'status' => 'Pending',
                    'longurl' => $mockLongUrl,
                    'amount' => $payload['amount'],
                    'purpose' => $payload['purpose'],
                ]
            ];
            $statusCode = 201;

            // if ($response['status_code'] >= 300 || !($response['body']['success'] ?? false) || empty($response['body']['payment_request']['longurl'])) {
            //     $errorMsg = $response['body']['message'] ?? 'InstamojoGateway: Failed to create payment request.';
            //     if (is_array($errorMsg)) $errorMsg = implode(', ', array_map(function($k, $v) { return "$k: " . implode(",",$v); }, array_keys($errorMsg), array_values($errorMsg)));
            //     throw new InitializationException($errorMsg);
            // }

            return [
                'status' => 'pending_redirect',
                'message' => 'Redirect to Instamojo payment page.',
                'redirectUrl' => $apiResponse['payment_request']['longurl'],
                'gatewayReferenceId' => $apiResponse['payment_request']['id'], // This is Instamojo's payment_request_id
                'orderId' => $sanitizedData['orderId'] ?? null,
                'rawData' => $apiResponse['payment_request']
            ];
        } catch (\Exception $e) {
            throw new InitializationException('InstamojoGateway: Payment request creation failed. ' . $e->getMessage(), 0, $e);
        }
    }

    // Instamojo webhook verification involves checking `X-Instamojo-Signature` if salt is configured, or comparing received data.
    // This is a simplified version of webhook processing.
    private function verifyWebhook(array $data): bool
    {
        if (empty($this->config['webhookSecret'])) {
            // If no secret, assume valid if critical data is present. Not recommended for production.
            return !empty($data['payment_id']) && !empty($data['payment_request_id']) && !empty($data['status']);
        }
        // Actual signature verification logic:
        // $mac = hash_hmac("sha1", implode("|", $data_to_sign_ordered_by_key), $this->config['webhookSecret']);
        // if ($mac === $_SERVER['HTTP_X_INSTAMOJO_SIGNATURE']) { return true; } else { return false; }
        // For mock, we'll just check if a known test signature is passed if secret is set.
        return ($data['mac'] ?? '') === 'mock_instamojo_signature'; // Replace with actual server-side signature generation/check
    }

    public function process(array $data): array
    {
        // Handles redirect from Instamojo (to redirect_url) and webhooks.
        // Redirect usually contains: payment_id, payment_status, payment_request_id
        // Webhook POSTs more details and should be verified if salt is used.
        $sanitizedData = $this->sanitize($data); // Data from redirect query params or webhook POST body

        // Check if it's a webhook with MAC for verification
        $isWebhook = isset($sanitizedData['mac']);
        if ($isWebhook && !empty($this->config['webhookSecret'])) {
            if (!$this->verifyWebhook($sanitizedData)) {
                 throw new ProcessingException('InstamojoGateway: Webhook MAC signature verification failed.');
            }
        }

        $paymentId = $sanitizedData['payment_id'] ?? null;
        $paymentRequestId = $sanitizedData['payment_request_id'] ?? null;
        $status = $sanitizedData['status'] ?? 'Failed'; // Instamojo status: Credit, Failed, Pending

        if (!$paymentId || !$paymentRequestId) {
            throw new ProcessingException('InstamojoGateway: Invalid data. Missing payment_id or payment_request_id.');
        }

        $isSuccess = strtolower($status) === 'credit' || strtolower($status) === 'completed'; // 'Completed' can also be used
        $isFailed = strtolower($status) === 'failed';
        $isPending = strtolower($status) === 'pending';

        return [
            'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
            'message' => 'Instamojo payment processed. Status: ' . $status,
            'transactionId' => $paymentId, // Instamojo's payment_id
            'orderId' => $sanitizedData['transaction_id'] ?? ($sanitizedData['custom_fields']['order_id']['value'] ?? null), // Your orderId if passed via transaction_id or custom field
            'gatewayReferenceId' => $paymentRequestId, // Instamojo's payment_request_id
            'paymentStatus' => $status,
            'rawData' => $sanitizedData
        ];
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        // Can verify using payment_request_id or payment_id.
        $paymentRequestId = $sanitizedData['gatewayReferenceId'] ?? null; // This is payment_request_id
        $paymentId = $sanitizedData['transactionId'] ?? null; // This is payment_id

        if (!$paymentRequestId && !$paymentId) {
            throw new VerificationException('InstamojoGateway: Missing gatewayReferenceId (payment_request_id) or transactionId (payment_id) for verification.');
        }

        $endpoint = $paymentId ? ('payments/' . $paymentId . '/') : ('payment-requests/' . $paymentRequestId . '/');

        try {
            // $response = $this->httpClient('GET', $this->getApiBaseUrl() . $endpoint, [], $this->getRequestHeaders());
            // Mocked response
            $mockStatus = 'Credit';
            $mockPaymentId = $paymentId ?? 'im_pay_' . uniqid();
            $mockPaymentRequestId = $paymentRequestId ?? 'im_pr_' . uniqid();

            if (($paymentRequestId ?: $paymentId) === 'fail_verify_ref') {
                $mockStatus = 'Failed';
            } elseif (($paymentRequestId ?: $paymentId) === 'pending_verify_ref') {
                $mockStatus = 'Pending';
            }

            $apiResponsePayload = $paymentId ?
            [
                'success' => true,
                'payment' => [
                    'payment_id' => $mockPaymentId,
                    'payment_request' => $this->getApiBaseUrl() . 'payment-requests/' . $mockPaymentRequestId . '/',
                    'status' => $mockStatus,
                    'amount' => sprintf("%.2f", $sanitizedData['original_amount_for_test'] ?? 100.00),
                    'buyer_name' => 'Test Buyer',
                    'buyer_email' => 'test@example.com',
                ]
            ] :
            [
                'success' => true,
                'payment_request' => [
                    'id' => $mockPaymentRequestId,
                    'status' => $mockStatus, // Payment request status, might be different from final payment status
                    'payments' => $mockStatus === 'Credit' ? [[ 'payment_id' => $mockPaymentId, 'status' => 'Credit' ]] : [],
                    'longurl' => ($this->config['isSandbox'] ? 'https://test.instamojo.com/' : 'https://www.instamojo.com/') . '@user/' . $mockPaymentRequestId,
                ]
            ];

            // if (!($response['body']['success'] ?? false)) {
            //      $errorMsg = $response['body']['message'] ?? 'InstamojoGateway: Failed to verify payment.';
            //      if (is_array($errorMsg)) $errorMsg = implode(', ', $errorMsg);
            //     throw new VerificationException($errorMsg);
            // }

            $details = $paymentId ? ($apiResponsePayload['payment'] ?? []) : ($apiResponsePayload['payment_request'] ?? []);
            $paymentStatus = $details['status'] ?? 'Failed';
            if (!$paymentId && !empty($details['payments'][0]['status'])) {
                $paymentStatus = $details['payments'][0]['status']; // Get status from actual payment if querying request
            }

            $isSuccess = strtolower($paymentStatus) === 'credit' || strtolower($paymentStatus) === 'completed';
            $isPending = strtolower($paymentStatus) === 'pending';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'Instamojo verification result: ' . $paymentStatus,
                'transactionId' => $paymentId ?? ($details['payments'][0]['payment_id'] ?? null),
                'gatewayReferenceId' => $paymentRequestId ?? ($details['id'] ?? null),
                'orderId' => $details['transaction_id'] ?? null, // If set by you during creation
                'paymentStatus' => $paymentStatus,
                'rawData' => $details
            ];
        } catch (\Exception $e) {
            throw new VerificationException('InstamojoGateway: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Instamojo Payment ID (not payment_request_id)
            throw new RefundException('InstamojoGateway: Missing transactionId (Payment ID) for refund.');
        }
        if (empty($sanitizedData['type'])) { // Refund type, e.g., 'RFD' (Full Refund), 'QFL', 'QPR' etc.
            throw new RefundException('InstamojoGateway: Missing type for refund (e.g., RFD).');
        }
        if (empty($sanitizedData['body'])) { // Reason for refund
            throw new RefundException('InstamojoGateway: Missing body (reason) for refund.');
        }
        // Amount is needed for partial refunds (types like PRF), not for full (RFD).
        if (strtoupper($sanitizedData['type']) === 'PRF' && (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0)) {
            throw new RefundException('InstamojoGateway: Missing or invalid amount for partial refund (type PRF).');
        }

        $paymentId = $sanitizedData['transactionId'];
        $payload = [
            'transaction_id' => $paymentId, // This is your internal ID if you passed it, Instamojo refers to Payment ID for refunds.
            'type' => strtoupper($sanitizedData['type']),
            'body' => $sanitizedData['body'], // Reason
        ];
        if (strtoupper($payload['type']) === 'PRF') {
            $payload['refund_amount'] = sprintf('%.2f', $sanitizedData['amount']);
        }

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . 'refunds/', http_build_query($payload), $this->getRequestHeaders(), true);
            // Mocked response
            if (($sanitizedData['amount'] ?? 0) == 999.99) {
                 throw new RefundException('InstamojoGateway: API rejected refund (simulated amount 999.99).');
            }
            $mockRefundId = 'im_rfnd_' . uniqid();
            $apiResponse = [
                'success' => true,
                'refund' => [
                    'id' => $mockRefundId,
                    'payment_id' => $paymentId,
                    'status' => 'Refunded', // Or Pending
                    'type' => $payload['type'],
                    'body' => $payload['body'],
                    'refund_amount' => $payload['refund_amount'] ?? $sanitizedData['original_total_amount_for_test'] ?? '0.00',
                    'total_amount' => $sanitizedData['original_total_amount_for_test'] ?? '0.00',
                ]
            ];
            // if (!($response['body']['success'] ?? false) || empty($response['body']['refund']['id'])) {
            //     $errorMsg = $response['body']['message'] ?? 'InstamojoGateway: Failed to process refund.';
            //     if (is_array($errorMsg)) $errorMsg = implode(', ', $errorMsg);
            //     throw new RefundException($errorMsg);
            // }

            $refundDetails = $apiResponse['refund'];
            $isSuccess = strtolower($refundDetails['status']) === 'refunded';
            $isPending = strtolower($refundDetails['status']) === 'pending';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'Instamojo refund status: ' . $refundDetails['status'],
                'refundId' => $refundDetails['id'],
                'transactionId' => $refundDetails['payment_id'],
                'paymentStatus' => $refundDetails['status'],
                'rawData' => $refundDetails
            ];
        } catch (\Exception $e) {
            throw new RefundException('InstamojoGateway: Refund request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
