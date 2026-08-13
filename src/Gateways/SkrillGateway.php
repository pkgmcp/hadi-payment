<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class SkrillGateway extends PaymentGateway
{
    private const PREPARE_URL_SANDBOX = 'https://pay.sandbox.skrill.com'; // This is for redirect
    private const PREPARE_URL_PRODUCTION = 'https://pay.skrill.com';
    // MQI (Merchant Query Interface) for status checks - usually a different endpoint or method.
    private const MQI_STATUS_URL_SANDBOX = 'https://www.sandbox.skrill.com/app/query.pl'; // Example
    private const MQI_STATUS_URL_PRODUCTION = 'https://www.skrill.com/app/query.pl'; // Example

    protected function getDefaultConfig(): array
    {
        return [
            'merchantEmail' => '',    // Your Skrill merchant account email
            'secretWord' => '',       // Your Skrill merchant secret word (for MD5 signature on status_url)
            'mqiPassword' => '',      // Your Skrill MQI API/Password (for API calls like refund/status check)
            'merchantId' => '',       // Your Skrill Merchant ID (numerical)
            'isSandbox' => true,
            'language' => 'EN',        // Default language for payment page
            'returnUrl' => 'https://example.com/skrill/success',
            'cancelUrl' => 'https://example.com/skrill/cancel',
            'statusUrl' => 'https://example.com/skrill/status', // Webhook/IPN URL
            'timeout' => 60,
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['merchantEmail'])) {
            throw new InvalidConfigurationException('SkrillGateway: merchantEmail is required.');
        }
        // Secret word is crucial for verifying status_url callbacks.
        if (empty($config['secretWord'])) {
            throw new InvalidConfigurationException('SkrillGateway: secretWord is required for status URL verification.');
        }
        // MQI password is for server-to-server API calls.
        if (empty($config['mqiPassword'])) {
            throw new InvalidConfigurationException('SkrillGateway: mqiPassword is required for API operations like refunds or direct status checks.');
        }
        if (empty($config['merchantId'])) {
            throw new InvalidConfigurationException('SkrillGateway: merchantId is required.');
        }
    }

    private function getPrepareUrl(): string
    {
        return $this->config['isSandbox'] ? self::PREPARE_URL_SANDBOX : self::PREPARE_URL_PRODUCTION;
    }

    private function getMqiStatusUrl(): string
    {
        return $this->config['isSandbox'] ? self::MQI_STATUS_URL_SANDBOX : self::MQI_STATUS_URL_PRODUCTION;
    }

    // Skrill's redirect payment request doesn't use a server-side generated hash typically.
    // The security is on the status_url (webhook) via MD5 signature using the secretWord.

    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('SkrillGateway: Invalid or missing amount.');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('SkrillGateway: Missing currency.');
        }
        if (empty($sanitizedData['orderId'])) {
            throw new InitializationException('SkrillGateway: Missing orderId (transaction_id).');
        }

        $payload = [
            'pay_to_email' => $this->config['merchantEmail'],
            'recipient_description' => $this->config['merchantName'] ?? 'Your Store',
            'transaction_id' => $sanitizedData['orderId'],
            'return_url' => $this->config['returnUrl'],
            'cancel_url' => $this->config['cancelUrl'],
            'status_url' => $this->config['statusUrl'], // Crucial for getting payment status
            'language' => $this->config['language'],
            'amount' => sprintf('%.2f', $sanitizedData['amount']),
            'currency' => strtoupper($sanitizedData['currency']),
            'detail1_description' => 'Order ID:',
            'detail1_text' => $sanitizedData['orderId'],
            'detail2_description' => $sanitizedData['descriptionTitle'] ?? 'Product:',
            'detail2_text' => $sanitizedData['description'] ?? 'Payment for order ' . $sanitizedData['orderId'],
            // 'pay_from_email' => $sanitizedData['customerEmail'] ?? null, // Optional pre-fill
            // 'firstname' => $sanitizedData['firstName'] ?? null,
            // 'lastname' => $sanitizedData['lastName'] ?? null,
            // 'prepare_only' => 1, // If you want to get a session ID and then redirect via your own form
        ];

        // If using prepare_only=1, you would make a POST to this URL, get a session_id,
        // then redirect GET to https://pay.skrill.com/?sid={session_id}
        // For direct redirect, the user's browser POSTs these fields to pay.skrill.com

        return [
            'status' => 'pending_redirect',
            'message' => 'Redirect to Skrill payment page.',
            'redirectUrl' => $this->getPrepareUrl(), // This is the action URL for the form
            'formData' => $payload, // Data to be POSTed via a form
            'orderId' => $sanitizedData['orderId'],
        ];
    }

    private function verifyStatusNotification(array $data): bool
    {
        // Concatenate fields: merchant_id + transaction_id + strtoupper(md5(secret_word)) + mb_id + amount + currency + status
        if (empty($data['merchant_id']) || empty($data['transaction_id']) || empty($data['mb_amount']) || empty($data['mb_currency']) || empty($data['status'])) {
            return false; // Not enough data to verify
        }
        $sigString = $data['merchant_id'] .
                     $data['transaction_id'] .
                     strtoupper(md5($this->config['secretWord'])) .
                     ($data['mb_transaction_id'] ?? '') . // Skrill's transaction ID
                     $data['mb_amount'] .
                     $data['mb_currency'] .
                     $data['status']; // Status: 2 = processed, 0 = pending, -1 = pending, -2 = failed, -3 = chargeback

        $generatedSig = strtoupper(md5($sigString));
        return ($data['md5sig'] ?? '') === $generatedSig;
    }

    public function process(array $data): array
    {
        // Handles the status_url notification from Skrill (Webhook/IPN).
        $sanitizedData = $this->sanitize($data); // Data from POST by Skrill

        if (!$this->verifyStatusNotification($sanitizedData)) {
            throw new ProcessingException('SkrillGateway: MD5 signature verification failed for status notification.');
        }

        $status = (int)($sanitizedData['status'] ?? -2); // Default to failed if not present
        // Status codes: 2 (processed/success), 0 (pending), -1 (pending from manual), -2 (failed), -3 (chargeback)
        $isSuccess = ($status === 2);
        $isFailed = ($status === -2 || $status === -3);
        $isPending = ($status === 0 || $status === -1);

        return [
            'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
            'message' => 'Skrill payment processed. Gateway Status: ' . $status,
            'transactionId' => $sanitizedData['mb_transaction_id'] ?? null, // Skrill's transaction ID
            'orderId' => $sanitizedData['transaction_id'] ?? null, // Your order ID
            'paymentStatus' => (string)$status,
            'rawData' => $sanitizedData
        ];
    }

    public function verify(array $data): array
    {
        // Verification can be done using MQI (Merchant Query Interface) if needed, beyond status_url.
        // This requires MQI password and typically involves checking transaction_id or mb_transaction_id.
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['orderId'])) { // Your transaction_id
            throw new VerificationException('SkrillGateway: Missing orderId (transaction_id) for verification.');
        }

        // MQI request structure example (simplified):
        // POST to query.pl with action=status_trn, email, password (MQI), trn_id (your transaction_id)
        $mqiPayload = [
            'action' => 'status_trn',
            'email' => $this->config['merchantEmail'],
            'password' => md5($this->config['mqiPassword']), // MQI password usually MD5 hashed
            'trn_id' => $sanitizedData['orderId'],
        ];

        try {
            // $response = $this->httpClient('POST', $this->getMqiStatusUrl(), $mqiPayload, ['Content-Type' => 'application/x-www-form-urlencoded']);
            // Skrill MQI responses are often plain text or simple key-value pairs, not structured XML/JSON by default.
            // Parsing would be needed. Example successful response might contain: `200:OK:2:Processed:-:EUR:10.00:mb_txn_id_here`
            // Mocked MQI response (simulating parsed key-value or structured if API supports it)
            $mockStatus = 2; // 2 = Processed/Success
            $mockMbTransactionId = 'sk_mqi_txn_' . uniqid();

            if ($sanitizedData['orderId'] === 'fail_verify_order') {
                $mockStatus = -2; // Failed
            } elseif ($sanitizedData['orderId'] === 'pending_verify_order') {
                $mockStatus = 0; // Pending
            }

            // Simulate API error
            if ($sanitizedData['orderId'] === 'mqi_api_error') {
                 throw new VerificationException('SkrillGateway: MQI API error during verification (simulated).');
            }

            // Hypothetical structured response after parsing plain text
            $apiResponse = [
                'mb_transaction_id' => $mockMbTransactionId,
                'transaction_id' => $sanitizedData['orderId'],
                'status' => $mockStatus,
                'status_message' => $mockStatus == 2 ? 'Processed' : ($mockStatus == 0 ? 'Pending' : 'Failed'),
                'amount' => $sanitizedData['original_amount_for_test'] ?? '10.00',
                'currency' => 'EUR'
            ];

            if (empty($apiResponse['mb_transaction_id']) || !isset($apiResponse['status'])) {
                throw new VerificationException('SkrillGateway: Failed to verify payment via MQI. Invalid response format.');
            }

            $paymentStatus = (int)$apiResponse['status'];
            $isSuccess = ($paymentStatus === 2);
            $isFailed = ($paymentStatus === -2 || $paymentStatus === -3);
            $isPending = ($paymentStatus === 0 || $paymentStatus === -1);

            return [
                'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
                'message' => 'Skrill MQI verification result: ' . $apiResponse['status_message'],
                'transactionId' => $apiResponse['mb_transaction_id'],
                'orderId' => $apiResponse['transaction_id'],
                'paymentStatus' => (string)$paymentStatus,
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new VerificationException('SkrillGateway: MQI Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        // Refunds via Skrill are done using the Merchant Query Interface (MQI) or specific Refund API.
        // Requires action=prepare_refund, then action=do_refund with a session ID.
        // This is a simplified mock assuming a direct refund possibility or a single-step refund API if available.
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Skrill's mb_transaction_id to refund
            throw new RefundException('SkrillGateway: Missing Skrill transactionId (mb_transaction_id) for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('SkrillGateway: Invalid or missing amount for refund.');
        }

        // Simplified refund payload for a hypothetical direct refund MQI call.
        // Real Skrill refund is more complex (prepare_refund, then do_refund with session_id).
        $refundPayload = [
            'action' => 'refund', // Hypothetical direct refund action
            'email' => $this->config['merchantEmail'],
            'password' => md5($this->config['mqiPassword']),
            'mb_trn_id' => $sanitizedData['transactionId'],
            'amount' => sprintf('%.2f', $sanitizedData['amount']),
            'subject' => 'Refund for order',
            'note' => $sanitizedData['reason'] ?? 'Merchant requested refund',
        ];

        try {
            // $response = $this->httpClient('POST', $this->getMqiStatusUrl(), $refundPayload, ['Content-Type' => 'application/x-www-form-urlencoded']);
            // Parse plain text response from Skrill MQI
            // Mocked MQI refund response
            $mockStatus = 'success'; // 'success' or 'failed' or 'pending'
            $mockRefundId = 'sk_refund_' . uniqid();

            if ($sanitizedData['amount'] == 999.99) { // Simulate refund failure
                $mockStatus = 'failed';
                // throw new RefundException('SkrillGateway: MQI API rejected refund (simulated amount 999.99).');
            }

            // Hypothetical parsed response
            $apiResponse = [
                'status' => $mockStatus,
                'refund_id' => $mockStatus === 'success' ? $mockRefundId : null,
                'message' => $mockStatus === 'success' ? 'Refund successful' : 'Refund failed or pending',
            ];

            if ($apiResponse['status'] !== 'success') {
                throw new RefundException('SkrillGateway: Failed to process refund via MQI. ' . ($apiResponse['message'] ?? 'Unknown error'));
            }

            // Refund status can be asynchronous, confirmed via status_url if it triggers for refunds.
            return [
                'status' => 'success', // Assuming synchronous success for mock, or 'pending' if async
                'message' => 'Skrill refund status: ' . $apiResponse['message'],
                'refundId' => $apiResponse['refund_id'],
                'transactionId' => $sanitizedData['transactionId'],
                'paymentStatus' => 'refunded',
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new RefundException('SkrillGateway: Refund request failed via MQI. ' . $e->getMessage(), 0, $e);
        }
    }
}
