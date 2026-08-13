<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class CCAvenueGateway extends PaymentGateway
{
    // CCAvenue uses different URLs for new vs. old integrations and for seamless vs. non-seamless.
    // These are conceptual URLs for a redirect-based (non-seamless) integration.
    private const REDIRECT_URL_SANDBOX = 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction';
    private const REDIRECT_URL_PRODUCTION = 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction';
    // API for status check & refunds (older API used SOAP, newer uses form post to a different URL)
    private const API_URL_SANDBOX = 'https://apitest.ccavenue.com/apis/servlet/DoWebTrans';
    private const API_URL_PRODUCTION = 'https://api.ccavenue.com/apis/servlet/DoWebTrans';


    protected function getDefaultConfig(): array
    {
        return [
            'merchantId' => '',     // Your CCAvenue Merchant ID
            'accessCode' => '',     // Your CCAvenue Access Code (for request encryption)
            'workingKey' => '',     // Your CCAvenue Working Key (for request encryption & response decryption)
            'isSandbox' => true,
            'timeout' => 60,
            'redirectUrl' => 'https://example.com/ccavenue/return', // Mandatory return URL for CCAvenue
            'cancelUrl' => 'https://example.com/ccavenue/cancel',   // Mandatory cancel URL
            'language' => 'EN',
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['merchantId'])) {
            throw new InvalidConfigurationException('CCAvenueGateway: merchantId is required.');
        }
        if (empty($config['accessCode'])) {
            throw new InvalidConfigurationException('CCAvenueGateway: accessCode is required.');
        }
        if (empty($config['workingKey'])) {
            throw new InvalidConfigurationException('CCAvenueGateway: workingKey is required.');
        }
        if (empty($config['redirectUrl'])) {
            throw new InvalidConfigurationException('CCAvenueGateway: redirectUrl is required.');
        }
        if (empty($config['cancelUrl'])) {
            throw new InvalidConfigurationException('CCAvenueGateway: cancelUrl is required.');
        }
    }

    private function getRedirectUrl(): string
    {
        return $this->config['isSandbox'] ? self::REDIRECT_URL_SANDBOX : self::REDIRECT_URL_PRODUCTION;
    }

    private function getApiUrl(): string
    {
        return $this->config['isSandbox'] ? self::API_URL_SANDBOX : self::API_URL_PRODUCTION;
    }

    // CCAvenue uses a specific AES encryption method. This is a MOCK encryption.
    // In a real scenario, you MUST use CCAvenue's provided libraries or implement their exact AES variant.
    private function encryptRequest(string $data, string $key): string
    {
        if (function_exists('openssl_encrypt')) {
            $iv = str_repeat("\0", openssl_cipher_iv_length('aes-256-cbc')); // Placeholder IV
            $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
            return bin2hex($encrypted);
        }
        // Fallback or error if openssl is not available
        // This is NOT CCAvenue's actual encryption, just a placeholder concept.
        return 'mock_encrypted_' . hash('sha256', $data . $key);
    }

    // MOCK decryption. Real implementation needed for CCAvenue.
    private function decryptResponse(string $data, string $key): string
    {
        $data = hex2bin($data);
        if (function_exists('openssl_decrypt')) {
            $iv = str_repeat("\0", openssl_cipher_iv_length('aes-256-cbc')); // Placeholder IV
            return openssl_decrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        }
        return str_replace('mock_encrypted_', '', $data); // Simplistic mock reversal
    }


    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('CCAvenueGateway: Invalid or missing amount.');
        }
        if (empty($sanitizedData['orderId'])) {
            throw new InitializationException('CCAvenueGateway: Missing orderId.');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('CCAvenueGateway: Missing currency (e.g., INR).');
        }

        $merchantData = [
            'merchant_id' => $this->config['merchantId'],
            'order_id' => $sanitizedData['orderId'],
            'currency' => strtoupper($sanitizedData['currency']),
            'amount' => sprintf('%.2f', $sanitizedData['amount']),
            'redirect_url' => $this->config['redirectUrl'],
            'cancel_url' => $this->config['cancelUrl'],
            'language' => $this->config['language'],
            // Billing info (optional but recommended)
            'billing_name' => $sanitizedData['billingName'] ?? 'Test User',
            'billing_address' => $sanitizedData['billingAddress'] ?? 'Test Address',
            'billing_city' => $sanitizedData['billingCity'] ?? 'Test City',
            'billing_state' => $sanitizedData['billingState'] ?? 'MH',
            'billing_zip' => $sanitizedData['billingZip'] ?? '400001',
            'billing_country' => $sanitizedData['billingCountry'] ?? 'India',
            'billing_tel' => $sanitizedData['billingPhone'] ?? '9876543210',
            'billing_email' => $sanitizedData['billingEmail'] ?? 'test@example.com',
            // 'integration_type' => 'iframe_normal', // Or other integration types
        ];

        $merchantDataQueryString = http_build_query($merchantData);
        $encryptedRequest = $this->encryptRequest($merchantDataQueryString, $this->config['workingKey']);

        $payload = [
            'encRequest' => $encryptedRequest,
            'access_code' => $this->config['accessCode'],
        ];

        return [
            'status' => 'pending_redirect',
            'message' => 'Redirect to CCAvenue payment page.',
            'redirectUrl' => $this->getRedirectUrl(),
            'formData' => $payload, // Data to be POSTed to the redirectUrl
            'orderId' => $sanitizedData['orderId'],
        ];
    }

    public function process(array $data): array
    {
        // Handles the POST back from CCAvenue to redirect_url/cancel_url.
        // The response is an encrypted string in 'encResp'.
        if (empty($data['encResp'])) {
            throw new ProcessingException('CCAvenueGateway: Encrypted response (encResp) not found.');
        }

        $decryptedResponseString = $this->decryptResponse($data['encResp'], $this->config['workingKey']);
        parse_str($decryptedResponseString, $decryptedData);
        $sanitizedData = $this->sanitize($decryptedData);

        $orderStatus = $sanitizedData['order_status'] ?? 'Failure'; // Success, Failure, Aborted, Invalid
        $trackingId = $sanitizedData['tracking_id'] ?? null;
        $orderId = $sanitizedData['order_id'] ?? null;

        $isSuccess = strtolower($orderStatus) === 'success';
        $isFailed = in_array(strtolower($orderStatus), ['failure', 'aborted', 'invalid']);
        // CCAvenue does not really have a 'pending' from redirect that requires further action by merchant normally.
        // 'Aborted' is effectively a failure/cancellation.

        return [
            'status' => $isSuccess ? 'success' : 'failed',
            'message' => 'CCAvenue payment processed. Status: ' . $orderStatus . (isset($sanitizedData['failure_message']) ? ' - ' . $sanitizedData['failure_message'] : ''),
            'transactionId' => $trackingId, // CCAvenue's tracking ID
            'orderId' => $orderId,
            'paymentStatus' => $orderStatus,
            'rawData' => $sanitizedData
        ];
    }

    private function buildApiRequestXml(string $command, array $params)
    {
        // CCAvenue Order Status Tracker and Refund APIs often use XML over HTTP POST.
        // This is a simplified builder for the status check.
        $xml = new \SimpleXMLElement('<Order_Status_Query></Order_Status_Query>');
        $xml->addChild('order_no', $params['order_id'] ?? '');
        if (isset($params['reference_no'])) {
             $xml->addChild('reference_no', $params['reference_no']); // CCAvenue Tracking ID
        }
        // For refunds, it would be different XML structure.
        return $xml->asXML();
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        $orderId = $sanitizedData['orderId'] ?? null;
        $trackingId = $sanitizedData['transactionId'] ?? null; // CCAvenue tracking_id

        if (!$orderId && !$trackingId) {
            throw new VerificationException('CCAvenueGateway: Missing orderId or transactionId for verification.');
        }

        // Construct the request for CCAvenue's Order Status API.
        // This involves sending an XML or specific query string to their API endpoint.
        // The request usually needs to be encrypted or signed.
        // For this mock, we'll assume a simplified query and response.

        $requestParams = [];
        if ($orderId) {
            $requestParams['order_id'] = $orderId;
        }
        if ($trackingId) {
            $requestParams['reference_no'] = $trackingId;
        }

        // In reality, the request needs to be XML, then encrypted using accessCode, then placed in enc_request field for the API POST.
        // $xmlRequest = $this->buildApiRequestXml('orderStatusTracker', $requestParams);
        // $encryptedApiRequest = $this->encryptRequest($xmlRequest, $this->config['accessCode']); // Note: AccessCode for API req usually
        // $apiPostData = 'enc_request=' . $encryptedApiRequest . '&access_code=' . $this->config['accessCode'] . '&command=orderStatusTracker&request_type=XML&response_type=XML&version=1.1';

        try {
            // $response = $this->httpClient('POST', $this->getApiUrl(), $apiPostData, ['Content-Type' => 'application/x-www-form-urlencoded']);
            // $decryptedApiResponseString = $this->decryptResponse($response['body']['enc_response'], $this->config['workingKey']);
            // $apiResult = $this->xmlToArray(simplexml_load_string($decryptedApiResponseString));

            // Mocked API response (assuming it's been decrypted and parsed)
            $mockStatus = 'Shipped'; // CCAvenue uses statuses like Shipped (for success), Aborted, etc.
            if ($orderId === 'fail_verify_order' || $trackingId === 'fail_verify_track') {
                $mockStatus = 'Aborted';
            } elseif ($orderId === 'pending_verify_order') {
                $mockStatus = 'Awaited'; // Some pending state if applicable
            }

            $apiResult = [
                'Order_Status_Result' => [
                    'order_no' => $orderId ?? 'mock_oid_' . uniqid(),
                    'reference_no' => $trackingId ?? 'mock_tid_' . uniqid(),
                    'order_status' => $mockStatus,
                    'order_amt' => sprintf("%.2f", $sanitizedData['original_amount_for_test'] ?? 100.00),
                    'order_currncy' => 'INR',
                    'order_card_name' => 'Visa',
                ]
            ];

            if (empty($apiResult['Order_Status_Result']['order_status'])) {
                throw new VerificationException('CCAvenueGateway: Failed to parse verification response or status missing.');
            }

            $paymentStatus = $apiResult['Order_Status_Result']['order_status'];
            // CCAvenue statuses for successful transactions: Shipped, Successful, Completed. Some might be pending settlement.
            $isSuccess = in_array(strtolower($paymentStatus), ['shipped', 'successful', 'completed', 'success']);
            $isPending = in_array(strtolower($paymentStatus), ['awaited', 'pending']); // Example pending statuses

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'CCAvenue verification result: ' . $paymentStatus,
                'transactionId' => $apiResult['Order_Status_Result']['reference_no'],
                'orderId' => $apiResult['Order_Status_Result']['order_no'],
                'paymentStatus' => $paymentStatus,
                'rawData' => $apiResult['Order_Status_Result']
            ];
        } catch (\Exception $e) {
            throw new VerificationException('CCAvenueGateway: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // CCAvenue Tracking ID (reference_no)
            throw new RefundException('CCAvenueGateway: Missing transactionId (CCAvenue reference_no) for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('CCAvenueGateway: Invalid or missing amount for refund.');
        }
        if (empty($sanitizedData['orderId'])) { // Your original order ID
            throw new RefundException('CCAvenueGateway: Missing orderId for refund reference.');
        }

        // Refund API request needs to be XML, encrypted, and POSTed.
        // XML content for refund: reference_no, refund_amount, order_id, currency, reason
        // $refundXmlData = sprintf('<RefundRequest><reference_no>%s</reference_no><refund_amount>%.2f</refund_amount><order_id>%s</order_id><currency>%s</currency><reason>%s</reason></RefundRequest>',
        //     $sanitizedData['transactionId'],
        //     $sanitizedData['amount'],
        //     $sanitizedData['orderId'],
        //     strtoupper($sanitizedData['currency'] ?? 'INR'),
        //     $sanitizedData['reason'] ?? 'Merchant refund'
        // );
        // $encryptedRefundRequest = $this->encryptRequest($refundXmlData, $this->config['accessCode']); // AccessCode for API req
        // $apiPostData = 'enc_request=' . $encryptedRefundRequest . '&access_code=' . $this->config['accessCode'] . '&command=refundOrder&request_type=XML&response_type=XML&version=1.1';

        try {
            // $response = $this->httpClient('POST', $this->getApiUrl(), $apiPostData, ['Content-Type' => 'application/x-www-form-urlencoded']);
            // $decryptedRefundResponse = $this->decryptResponse($response['body']['enc_response'], $this->config['workingKey']);
            // $refundResult = $this->xmlToArray(simplexml_load_string($decryptedRefundResponse));

            // Mocked Refund API response
            $mockRefundStatus = 'Y'; // 'Y' for success, 'N' for failure
            $mockRefundMessage = 'Refund Successful';
            if (($sanitizedData['amount'] ?? 0) == 999.99) {
                $mockRefundStatus = 'N';
                $mockRefundMessage = 'Refund failed due to invalid parameters (simulated).';
            }

            $refundResult = [
                'Refund_Order_Result' => [
                    'reference_no' => $sanitizedData['transactionId'],
                    'order_id' => $sanitizedData['orderId'],
                    'refund_status' => $mockRefundStatus,
                    'refund_message' => $mockRefundMessage,
                    'refund_amount' => sprintf("%.2f", $sanitizedData['amount']),
                    'refund_id' => 'ccav_ref_' . uniqid() // CCAvenue might provide its own refund ID
                ]
            ];

            if (empty($refundResult['Refund_Order_Result']['refund_status']) || $refundResult['Refund_Order_Result']['refund_status'] !== 'Y') {
                throw new RefundException('CCAvenueGateway: Failed to process refund. ' . ($refundResult['Refund_Order_Result']['refund_message'] ?? 'API error or refund rejected.'));
            }

            // Refund status is usually confirmed here, but reconciliation might be needed.
            return [
                'status' => 'success',
                'message' => 'CCAvenue refund status: ' . $refundResult['Refund_Order_Result']['refund_message'],
                'refundId' => $refundResult['Refund_Order_Result']['refund_id'] ?? ($sanitizedData['transactionId'] . '_refund'),
                'transactionId' => $sanitizedData['transactionId'],
                'paymentStatus' => 'refunded',
                'rawData' => $refundResult['Refund_Order_Result']
            ];
        } catch (\Exception $e) {
            throw new RefundException('CCAvenueGateway: Refund request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
