<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class NetellerGateway extends PaymentGateway
{
    // Neteller is part of Paysafe, their newer APIs are RESTful JSON based.
    private const API_BASE_URL_SANDBOX = 'https://api.test.neteller.com/v1'; // Example
    private const API_BASE_URL_PRODUCTION = 'https://api.neteller.com/v1'; // Example

    protected function getDefaultConfig(): array
    {
        return [
            'accountId' => '',       // Your Neteller Merchant Account ID (or Client ID)
            'apiKey' => '',          // Your Neteller API Key (or Client Secret)
            'isSandbox' => true,
            'timeout' => 60,
            'webhookSecret' => '',   // For verifying webhook authenticity
            'paymentDescription' => 'Purchase from YourStore',
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['accountId'])) {
            throw new InvalidConfigurationException('NetellerGateway: accountId (or Client ID) is required.');
        }
        if (empty($config['apiKey'])) {
            throw new InvalidConfigurationException('NetellerGateway: apiKey (or Client Secret) is required.');
        }
    }

    private function getApiBaseUrl(): string
    {
        return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX : self::API_BASE_URL_PRODUCTION;
    }

    private function getRequestHeaders(bool $includeContentType = true): array
    {
        $headers = [
            // Neteller uses Basic Auth with accountId:apiKey, or a Bearer token obtained via OAuth2.
            // This mock assumes Basic Auth for simplicity with accountId as username, apiKey as password.
            'Authorization' => 'Basic ' . base64_encode($this->config['accountId'] . ':' . $this->config['apiKey']),
        ];
        if ($includeContentType) {
             $headers['Content-Type'] = 'application/json';
        }
        $headers['Accept'] = 'application/json';
        return $headers;
    }

    public function initialize(array $data): array
    {
        // Neteller payments can be direct (customer provides Neteller credentials on merchant site via iframe/API)
        // or redirect (customer is sent to Neteller to log in and approve).
        // This mock simulates initiating a payment that might lead to a redirect or direct processing.
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('NetellerGateway: Invalid or missing amount.');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('NetellerGateway: Missing currency.');
        }
        if (empty($sanitizedData['orderId'])) {
            throw new InitializationException('NetellerGateway: Missing orderId (merchantRefNum).');
        }

        $payload = [
            'merchantRefNum' => $sanitizedData['orderId'],
            'amount' => (int)round($sanitizedData['amount'] * 100), // Amount in minor units (cents)
            'currency' => strtoupper($sanitizedData['currency']),
            'paymentHandleToken' => $sanitizedData['paymentHandleToken'] ?? null, // Required for Wallet payments after customer auth on Neteller
            'transactionType' => 'PAYMENT', // Or AUTHORIZATION
            'description' => $sanitizedData['description'] ?? $this->config['paymentDescription'],
            // 'customerIp' => $sanitizedData['customerIp'] ?? null,
            // 'returnLinks' => [ // For redirect flows
            //     ['rel' => 'on_completed', 'href' => $sanitizedData['returnUrl'] ?? $this->config['returnUrl'], 'method' => 'GET'],
            //     ['rel' => 'on_failed', 'href' => $sanitizedData['cancelUrl'] ?? $this->config['cancelUrl'], 'method' => 'GET'],
            // ],
            // 'webhook' => ['rel' => 'on_event', 'href' => $this->config['notifyUrl']]
        ];

        // If paymentHandleToken is not present, it might be a request to get a redirect URL to Neteller to authorize.
        // For this mock, we'll assume if no token, it's an error or needs a different initial call not covered here.
        if (empty($payload['paymentHandleToken'])) {
            // This would be a different API call to initiate and get a redirect for customer to authenticate on Neteller.
            // E.g. POST /paymenthub/v1/payments with different parameters to get back a redirect link.
            // For simplicity, we'll mock this step and assume we need a token.
            // Or, if this `initialize` is *after* a redirect from Neteller with some auth code, that code needs processing.

            // Mock a redirect scenario if no token (conceptual)
            if (!($sanitizedData['simulate_redirect_scenario'] ?? false)) {
                 throw new InitializationException('NetellerGateway: paymentHandleToken is required for this mock. For redirect, a prior step is needed.');
            }
             return [
                'status' => 'pending_redirect',
                'message' => 'Redirect to Neteller for authentication (simulated).',
                'redirectUrl' => $this->getApiBaseUrl() . '/oauth/authenticate?client_id=' . $this->config['accountId'] . '&response_type=token&scope=payment&redirect_uri=' . urlencode($this->config['returnUrl'] . '?orderId=' . $sanitizedData['orderId']),
                'orderId' => $sanitizedData['orderId'],
             ];
        }

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/payments', $payload, $this->getRequestHeaders());
            // Mocked response for processing a payment with a paymentHandleToken
            $mockTransactionId = 'nt_txn_' . uniqid();
            $mockStatus = 'COMPLETED'; // COMPLETED, PENDING, FAILED, CANCELLED

            if ($payload['paymentHandleToken'] === 'token_declined') {
                $mockStatus = 'FAILED';
            }
            if ($sanitizedData['amount'] == 99900) { // 999.00 in minor units
                $mockStatus = 'FAILED';
                // throw new InitializationException('NetellerGateway: Simulated API error due to amount.');
            }

            $apiResponse = [
                'id' => $mockTransactionId,
                'merchantRefNum' => $payload['merchantRefNum'],
                'amount' => $payload['amount'],
                'currency' => $payload['currency'],
                'status' => $mockStatus,
                // 'gatewayReconciliationId' => 'gr_' . uniqid(),
                // 'authCode' => 'AC12345'
            ];
            $statusCode = ($mockStatus === 'COMPLETED') ? 201 : 200;

            // if ($response['status_code'] >= 300 || empty($response['body']['id'])) {
            //     $errorMsg = $response['body']['error']['message'] ?? 'NetellerGateway: Failed to create payment.';
            //     throw new InitializationException($errorMsg);
            // }

            $isSuccess = $apiResponse['status'] === 'COMPLETED';
            $isPending = $apiResponse['status'] === 'PENDING';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'Neteller payment status: ' . $apiResponse['status'],
                'gatewayReferenceId' => $apiResponse['id'],
                'orderId' => $apiResponse['merchantRefNum'] ?? $sanitizedData['orderId'],
                'paymentStatus' => $apiResponse['status'],
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new InitializationException('NetellerGateway: Payment creation failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function process(array $data): array
    {
        // Process webhook notifications. Signature verification using webhookSecret is crucial.
        // Paysafe webhooks typically include a signature header (e.g., X-Paysafe-Signature) to verify.
        $sanitizedData = $this->sanitize($data); // Data from webhook

        // IMPORTANT: Verify webhook signature using $this->config['webhookSecret']

        $eventType = $sanitizedData['event'] ?? null; // e.g., PAYMENT_COMPLETED, PAYMENT_FAILED
        $payload = $sanitizedData['payload'] ?? [];
        $transactionId = $payload['id'] ?? null;
        $status = $payload['status'] ?? 'UNKNOWN';

        if (!$eventType || !$transactionId) {
            throw new ProcessingException('NetellerGateway: Invalid webhook data. Missing event type or payload ID.');
        }

        $isSuccess = ($eventType === 'PAYMENT_COMPLETED' || $status === 'COMPLETED');
        $isFailed = ($eventType === 'PAYMENT_FAILED' || $status === 'FAILED' || $status === 'CANCELLED');
        $isRefund = str_contains(strtolower($eventType), 'refund');

        return [
            'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : ($isRefund ? 'refund_processed' : 'pending')),
            'message' => 'Neteller webhook processed. Event: ' . $eventType . ', Status: ' . $status,
            'transactionId' => $transactionId,
            'orderId' => $payload['merchantRefNum'] ?? null,
            'paymentStatus' => $status,
            'eventType' => $eventType,
            'rawData' => $sanitizedData
        ];
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['gatewayReferenceId'])) { // Neteller Payment ID (id from payment response)
            throw new VerificationException('NetellerGateway: Missing gatewayReferenceId for verification.');
        }
        $paymentId = $sanitizedData['gatewayReferenceId'];

        try {
            // $response = $this->httpClient('GET', $this->getApiBaseUrl() . '/payments/' . $paymentId, [], $this->getRequestHeaders(false));
            // Mocked response
            $mockStatus = 'COMPLETED';
            if ($paymentId === 'nt_txn_fail_verify') {
                $mockStatus = 'FAILED';
            } elseif ($paymentId === 'nt_txn_pending_verify') {
                $mockStatus = 'PENDING';
            }
            $apiResponse = [
                'id' => $paymentId,
                'status' => $mockStatus,
                'amount' => ($sanitizedData['original_amount_for_test'] ?? 100) * 100, // minor units
                'currency' => 'USD',
                'merchantRefNum' => 'order_' . uniqid(),
            ];
            // if ($response['status_code'] !== 200 || empty($response['body']['id'])) {
            //     throw new VerificationException('NetellerGateway: Failed to verify payment. ' . ($response['body']['error']['message'] ?? 'API error'));
            // }

            $paymentStatus = $apiResponse['status'] ?? 'UNKNOWN';
            $isSuccess = $paymentStatus === 'COMPLETED';
            $isPending = $paymentStatus === 'PENDING';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'Neteller verification result: ' . $paymentStatus,
                'transactionId' => $apiResponse['id'],
                'orderId' => $apiResponse['merchantRefNum'] ?? null,
                'paymentStatus' => $paymentStatus,
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new VerificationException('NetellerGateway: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Original Neteller Payment ID to refund
            throw new RefundException('NetellerGateway: Missing transactionId for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('NetellerGateway: Invalid or missing amount for refund.');
        }

        $paymentId = $sanitizedData['transactionId'];
        $payload = [
            'merchantRefNum' => ($sanitizedData['refundReference'] ?? 'refund_' . $paymentId . '_' . uniqid()),
            'amount' => (int)round($sanitizedData['amount'] * 100), // Amount in minor units
            'currency' => strtoupper($sanitizedData['currency'] ?? 'USD'),
            'description' => $sanitizedData['reason'] ?? 'Merchant requested refund',
            // 'duplicateMerchantRefNumAllowed' => true, // If your merchantRefNum for refund might not be unique
        ];

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/payments/' . $paymentId . '/refunds', $payload, $this->getRequestHeaders());
            // Mocked response
            $mockRefundId = 'nt_ref_' . uniqid();
            $mockStatus = 'COMPLETED'; // Or PENDING, FAILED
            if (($sanitizedData['amount'] ?? 0) == 999.00) { // 999.00, which is 99900 in minor units
                 $mockStatus = 'FAILED';
                 //throw new RefundException('NetellerGateway: API rejected refund (simulated amount 999.00).');
            }
            $apiResponse = [
                'id' => $mockRefundId,
                'status' => $mockStatus,
                'merchantRefNum' => $payload['merchantRefNum'],
                'amount' => $payload['amount'],
            ];
            $statusCode = ($mockStatus === 'COMPLETED') ? 201 : 200;

            // if ($response['status_code'] >= 300 || empty($response['body']['id'])) {
            //     throw new RefundException('NetellerGateway: Failed to process refund. ' . ($response['body']['error']['message'] ?? 'Unknown error'));
            // }

            $isSuccess = $apiResponse['status'] === 'COMPLETED';
            $isPending = $apiResponse['status'] === 'PENDING';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'Neteller refund status: ' . $apiResponse['status'],
                'refundId' => $apiResponse['id'],
                'transactionId' => $paymentId,
                'paymentStatus' => 'refunded (' . $apiResponse['status'] . ')',
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new RefundException('NetellerGateway: Refund request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
