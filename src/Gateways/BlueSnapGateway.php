<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class BlueSnapGateway extends PaymentGateway
{
    private const API_BASE_URL_SANDBOX = 'https://sandbox.bluesnap.com/services/2';
    private const API_BASE_URL_PRODUCTION = 'https://ws.bluesnap.com/services/2';

    protected function getDefaultConfig(): array
    {
        return [
            'apiUsername' => '', // Your BlueSnap API Username
            'apiPassword' => '', // Your BlueSnap API Password
            'isSandbox' => true,
            'timeout' => 60,
            'storeId' => null,   // Optional: if you have a specific store ID for transactions
            'softDescriptor' => null, // Optional: soft descriptor for statements
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['apiUsername'])) {
            throw new InvalidConfigurationException('BlueSnapGateway: apiUsername is required.');
        }
        if (empty($config['apiPassword'])) {
            throw new InvalidConfigurationException('BlueSnapGateway: apiPassword is required.');
        }
    }

    private function getApiBaseUrl(): string
    {
        return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX : self::API_BASE_URL_PRODUCTION;
    }

    private function getRequestHeaders(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->config['apiUsername'] . ':' . $this->config['apiPassword']),
            'Content-Type' => 'application/xml', // BlueSnap's Payment API primarily uses XML
            'Accept' => 'application/xml',
            // 'bluesnap-version' => '3.0' // Specify API version if needed
        ];
    }

    // Helper to convert array to simple XML. In reality, a more robust XML builder is needed.
    private function arrayToXml(array $data, \SimpleXMLElement $xml_data)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) { // Handle numeric keys for repeating elements
                $key = "item{$key}";
            }
            if (is_array($value)) {
                $subnode = $xml_data->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars((string)"$value"));
            }
        }
    }

    // Helper to convert SimpleXML to array. In reality, a more robust XML parser is needed.
    private function xmlToArray(\SimpleXMLElement $xmlObject, array &$out = []): array
    {
        foreach ((array) $xmlObject as $index => $node) {
            $out[$index] = (is_object($node) || is_array($node)) ? $this->xmlToArray($node) : $node;
        }
        return $out;
    }


    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('BlueSnapGateway: Invalid or missing amount.');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('BlueSnapGateway: Missing currency.');
        }
        if (empty($sanitizedData['orderId'])) {
            throw new InitializationException('BlueSnapGateway: Missing orderId (merchantTransactionId).');
        }
        // BlueSnap requires card details or a payment token (e.g., from their hosted payment fields or a stored payment method).
        // This mock assumes card details are passed, which is NOT PCI compliant for direct handling.
        // In production, use BlueSnap.js for tokenization.
        if (empty($sanitizedData['card']['number']) || empty($sanitizedData['card']['expiryMonth']) || empty($sanitizedData['card']['expiryYear']) || empty($sanitizedData['card']['cvv'])) {
            throw new InitializationException('BlueSnapGateway: Missing card details (use tokenization in production).');
        }

        $xmlPayload = new \SimpleXMLElement('<card-transaction xmlns="http://ws.bluesnap.com/beans/recurring"></card-transaction>');
        $this->arrayToXml([
            'card-holder-info' => [
                'first-name' => $sanitizedData['firstName'] ?? 'Test',
                'last-name' => $sanitizedData['lastName'] ?? 'User',
                'email' => $sanitizedData['email'] ?? 'test@example.com',
            ],
            'credit-card' => [
                'card-number' => $sanitizedData['card']['number'],
                'expiration-month' => str_pad($sanitizedData['card']['expiryMonth'], 2, '0', STR_PAD_LEFT),
                'expiration-year' => $sanitizedData['card']['expiryYear'],
                'security-code' => $sanitizedData['card']['cvv'],
            ],
            'amount' => sprintf('%.2f', $sanitizedData['amount']),
            'currency' => strtoupper($sanitizedData['currency']),
            'merchant-transaction-id' => $sanitizedData['orderId'],
            'soft-descriptor' => $this->config['softDescriptor'] ?? 'MyStore',
            'card-transaction-type' => 'AUTH_CAPTURE', // Or 'AUTH_ONLY'
        ], $xmlPayload);

        if ($this->config['storeId']) {
            $xmlPayload->addChild('store-id', (string)$this->config['storeId']);
        }

        try {
            // $responseXmlString = $this->httpClient('POST', $this->getApiBaseUrl() . '/transactions', $xmlPayload->asXML(), $this->getRequestHeaders(), false);
            // $response = simplexml_load_string($responseXmlString['body']);
            // $responseArray = $this->xmlToArray($response);

            // Mocked response
            $mockTransactionId = 'bs_txn_' . uniqid();
            $mockProcessingStatus = 'SUCCESS';
            if ($sanitizedData['card']['number'] === '4242424242424241') { // Simulate decline
                $mockProcessingStatus = 'FAILURE';
            }

            $responseArray = [
                'transaction-id' => $mockTransactionId,
                'processing-info' => [
                    'processing-status' => $mockProcessingStatus,
                    'cvv-response-code' => 'M', // Match
                    'avs-response-code-zip' => 'M', // Match
                    'avs-response-code-street' => 'M', // Match
                ],
                'card-transaction-type' => 'AUTH_CAPTURE',
                'amount' => $xmlPayload->amount,
                'currency' => $xmlPayload->currency,
            ];
            $statusCode = 200; // BlueSnap often returns 200, status is in payload.

            // If response status code indicates HTTP error or missing transaction-id
            // if ($responseXmlString['status_code'] >= 400 || empty($responseArray['transaction-id'])) {
            //     $errorMsg = $responseArray['message'][0]['description'] ?? 'BlueSnapGateway: Failed to create transaction.';
            //     throw new InitializationException($errorMsg);
            // }

            if ($sanitizedData['amount'] == 999.99) {
                 $responseArray['processing-info']['processing-status'] = 'FAILURE';
                 $responseArray['processing-info']['proc-return-code'] = '005';
                 $responseArray['processing-info']['proc-return-message'] = 'Transaction declined by bank (simulated)';
                 $statusCode = 200; // Still 200, but failure in body
            }

            $isSuccess = ($responseArray['processing-info']['processing-status'] ?? '') === 'SUCCESS';

            return [
                'status' => $isSuccess ? 'success' : 'failed',
                'message' => 'BlueSnap payment status: ' . ($responseArray['processing-info']['processing-status'] ?? 'Unknown') . ' - ' . ($responseArray['processing-info']['proc-return-message'] ?? ''),
                'gatewayReferenceId' => (string)($responseArray['transaction-id'] ?? ''),
                'orderId' => (string)($xmlPayload->{'merchant-transaction-id'} ?? $sanitizedData['orderId']),
                'paymentStatus' => (string)($responseArray['processing-info']['processing-status'] ?? 'Unknown'),
                'rawData' => $responseArray
            ];
        } catch (\Exception $e) {
            throw new InitializationException('BlueSnapGateway: Payment creation failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function process(array $data): array
    {
        // BlueSnap uses IPN (Instant Payment Notifications / Webhooks). XML format.
        // IMPORTANT: Verify authenticity of IPN, e.g. by checking source IP or other mechanisms.
        $sanitizedData = $this->sanitize($data); // Expecting parsed XML array from webhook body

        $eventType = $sanitizedData['event'] ?? null; // e.g., CHARGE, REFUND, CANCELLATION
        $transactionId = $sanitizedData['referenceNumber'] ?? null; // BlueSnap transaction ID
        $merchantTransactionId = $sanitizedData['merchantTransactionId'] ?? null;
        $status = $sanitizedData['transactionStatus'] ?? ($sanitizedData['subscriptionStatus'] ?? 'UNKNOWN');

        if (!$eventType || !$transactionId) {
            throw new ProcessingException('BlueSnapGateway: Invalid IPN data. Missing event type or referenceNumber.');
        }

        $isSuccess = ($eventType === 'CHARGE' && $status === 'Approved') || ($eventType === 'REFUND' && $status === 'Approved');
        $isFailed = ($eventType === 'CHARGE' && $status === 'Declined');

        return [
            'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
            'message' => 'BlueSnap IPN processed. Event: ' . $eventType . ', Status: ' . $status,
            'transactionId' => $transactionId,
            'orderId' => $merchantTransactionId,
            'paymentStatus' => $status,
            'eventType' => $eventType,
            'rawData' => $sanitizedData
        ];
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['gatewayReferenceId'])) { // BlueSnap Transaction ID
            throw new VerificationException('BlueSnapGateway: Missing gatewayReferenceId for verification.');
        }
        $transactionId = $sanitizedData['gatewayReferenceId'];

        try {
            // $responseXmlString = $this->httpClient('GET', $this->getApiBaseUrl() . '/transactions/' . $transactionId, [], $this->getRequestHeaders(), false);
            // $response = simplexml_load_string($responseXmlString['body']);
            // $responseArray = $this->xmlToArray($response);

            // Mocked response
            $mockStatus = 'Approved';
            if ($transactionId === 'bs_txn_fail_verify') {
                $mockStatus = 'Failed';
            } elseif ($transactionId === 'bs_txn_pending_verify') {
                $mockStatus = 'Pending Merchant Review';
            }
            $responseArray = [
                'transaction-id' => $transactionId,
                'processing-info' => [
                    'processing-status' => $mockStatus === 'Approved' ? 'SUCCESS' : 'FAILURE',
                ],
                'transaction-status' => $mockStatus, // More descriptive status
                'amount' => $sanitizedData['original_amount_for_test'] ?? '100.00',
                'currency' => 'USD',
                'merchant-transaction-id' => 'order_' . uniqid(),
            ];
            // if ($responseXmlString['status_code'] >= 400 || empty($responseArray['transaction-id'])) {
            //     throw new VerificationException('BlueSnapGateway: Failed to retrieve transaction. ' . ($responseArray['message'][0]['description'] ?? 'API error'));
            // }

            $paymentStatus = $responseArray['transaction-status'] ?? ($responseArray['processing-info']['processing-status'] ?? 'Unknown');
            $isSuccess = in_array($paymentStatus, ['Approved', 'SUCCESS']);
            $isPending = in_array($paymentStatus, ['Pending Merchant Review']);

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'BlueSnap verification result: ' . $paymentStatus,
                'transactionId' => (string)($responseArray['transaction-id'] ?? ''),
                'orderId' => (string)($responseArray['merchant-transaction-id'] ?? null),
                'paymentStatus' => $paymentStatus,
                'rawData' => $responseArray
            ];
        } catch (\Exception $e) {
            throw new VerificationException('BlueSnapGateway: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Original BlueSnap Transaction ID to refund
            throw new RefundException('BlueSnapGateway: Missing transactionId for refund.');
        }
        // Amount is optional for full refund, required for partial.
        $amount = $sanitizedData['amount'] ?? null;
        if ($amount !== null && (!is_numeric($amount) || $amount <= 0)) {
            throw new RefundException('BlueSnapGateway: Invalid amount for partial refund.');
        }

        $refundPayload = [];
        if ($amount) {
            $refundPayload['amount'] = sprintf('%.2f', $amount);
        }
        if (!empty($sanitizedData['reason'])) {
            $refundPayload['reason'] = $sanitizedData['reason'];
        }
        // For idempotency, BlueSnap might use merchant-transaction-id for the refund operation itself.
        // $refundPayload['merchant-transaction-id'] = $sanitizedData['refundReference'] ?? 'refund-'.uniqid();

        $queryString = http_build_query($refundPayload); // Refunds are often done via PUT with query params or XML body
        $endpoint = $this->getApiBaseUrl() . '/transactions/refund/' . $sanitizedData['transactionId'];
        if ($queryString) {
            $endpoint .= '/?' . $queryString;
        }

        try {
            // $responseXmlString = $this->httpClient('PUT', $endpoint, null, $this->getRequestHeaders(), false);
            // $statusCode = $responseXmlString['status_code'];

            // Mocked response for refund (BlueSnap PUT refund usually returns 204 No Content on success)
            $mockStatusCode = 204;
            if (($sanitizedData['amount'] ?? 0) == 999.99) { // Simulate refund failure
                // This would typically be a 4xx error with an XML body explaining why
                // For mock, we just change status code and assume the calling code would parse a (non-existent) body for error
                $mockStatusCode = 400;
                // throw new RefundException('BlueSnapGateway: API rejected refund (simulated amount 999.99).');
            }

            if ($mockStatusCode === 204) {
                return [
                    'status' => 'success', // Refund processed successfully (or accepted for processing)
                    'message' => 'BlueSnap refund processed successfully.',
                    'refundId' => $sanitizedData['transactionId'] . '_refund', // No specific refund ID, use original + suffix
                    'transactionId' => $sanitizedData['transactionId'],
                    'paymentStatus' => 'refunded',
                    'rawData' => ['status_code' => $mockStatusCode]
                ];
            } else {
                 // In a real case, parse the XML error response if $mockStatusCode is not 204.
                throw new RefundException('BlueSnapGateway: Failed to process refund. Status code: ' . $mockStatusCode);
            }
        } catch (\Exception $e) {
            throw new RefundException('BlueSnapGateway: Refund request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
