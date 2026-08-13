<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class SagePayGateway extends PaymentGateway
{
    private const SERVER_REDIRECT_URL_SANDBOX = 'https://test.sagepay.com/gateway/service/vspserver-register.vsp';
    private const SERVER_REDIRECT_URL_PRODUCTION = 'https://live.sagepay.com/gateway/service/vspserver-register.vsp';
    private const DIRECT_API_URL_SANDBOX = 'https://test.sagepay.com/gateway/service'; // Base for direct API calls
    private const DIRECT_API_URL_PRODUCTION = 'https://live.sagepay.com/gateway/service';

    protected function getDefaultConfig(): array
    {
        return [
            'vendorName' => '',         // Your SagePay Vendor Name
            'encryptionPassword' => '', // Used for Server & Form integrations (Crypt field)
            'integrationType' => 'server', // 'server', 'form', 'direct'
            'isSandbox' => true,
            'timeout' => 60,
            'referrerId' => '',       // Optional: Partner Referrer ID
            'applyAVSCV2' => '0',      // Security checks, 0=Default, 1=Force, 2=Disable, 3=Force CV2, 4=Force Address & Postcode only
            'apply3DSecure' => '0',    // 3D Secure checks, 0=Default, 1=Force, 2=Disable, 3=Force if card scheme is enabled
            'currency' => 'GBP',
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['vendorName'])) {
            throw new InvalidConfigurationException('SagePayGateway: vendorName is required.');
        }
        if (in_array(strtolower($config['integrationType']), ['server', 'form']) && empty($config['encryptionPassword'])) {
            throw new InvalidConfigurationException('SagePayGateway: encryptionPassword is required for Server or Form integration.');
        }
        // For Direct API, username/password might be needed instead or in addition, not covered in this basic mock.
    }

    private function getRedirectUrl(): string
    {
        if (strtolower($this->config['integrationType']) === 'form') {
            return $this->config['isSandbox'] ? 'https://test.sagepay.com/gateway/service/vspform-register.vsp' : 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
        }
        return $this->config['isSandbox'] ? self::SERVER_REDIRECT_URL_SANDBOX : self::SERVER_REDIRECT_URL_PRODUCTION;
    }

    private function getApiBaseUrl(): string
    {
         return $this->config['isSandbox'] ? self::DIRECT_API_URL_SANDBOX : self::DIRECT_API_URL_PRODUCTION;
    }

    // SagePay AES encryption for the Crypt field. THIS IS A MOCK.
    // Real SagePay encryption uses AES/CBC/PKCS5Padding with the provided Encryption Password.
    private function encryptAes(string $string, string $key): string
    {
        if (empty($key)) {
            return $string; // No encryption if key is empty (Direct may not use it)
        }
        // The IV is often the same as the key for older SagePay integrations, or derived. This is a simplified mock.
        $iv = substr(md5($key), 0, 16); // Example IV, NOT secure or correct for SagePay
        $encrypted = openssl_encrypt($string, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return '@' . strtoupper(bin2hex($encrypted)); // Prepend @ and hex encode
    }

    // SagePay AES decryption. THIS IS A MOCK.
    private function decryptAes(string $string, string $key): string
    {
        if (empty($key) || strpos($string, '@') !== 0) {
            return $string;
        }
        $string = substr($string, 1); // Remove @
        $iv = substr(md5($key), 0, 16); // Must match encryption IV
        $decrypted = openssl_decrypt(hex2bin($string), 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return rtrim($decrypted, "\0.."); // Remove padding
    }

    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('SagePayGateway: Invalid or missing amount.');
        }
        if (empty($sanitizedData['orderId'])) {
            throw new InitializationException('SagePayGateway: Missing orderId (VendorTxCode).');
        }
        if (empty($sanitizedData['description'])) {
            throw new InitializationException('SagePayGateway: Missing description.');
        }
        if (empty($sanitizedData['returnUrl'])) {
            throw new InitializationException('SagePayGateway: Missing returnUrl (NotificationURL for server, RedirectURL for form).');
        }

        $payload = [
            'VPSProtocol' => '3.00', // Or 4.00 for newer features
            'TxType' => 'PAYMENT', // Or DEFERRED, AUTHENTICATE
            'Vendor' => $this->config['vendorName'],
            'VendorTxCode' => $sanitizedData['orderId'],
            'Amount' => sprintf('%.2f', $sanitizedData['amount']),
            'Currency' => $sanitizedData['currency'] ?? $this->config['currency'],
            'Description' => $sanitizedData['description'],
            'NotificationURL' => $sanitizedData['returnUrl'], // For Server integration, SagePay POSTs here
            // Billing details (highly recommended)
            'BillingSurname' => $sanitizedData['lastName'] ?? 'User',
            'BillingFirstnames' => $sanitizedData['firstName'] ?? 'Test',
            'BillingAddress1' => $sanitizedData['address1'] ?? 'Test Address 1',
            'BillingCity' => $sanitizedData['city'] ?? 'Test City',
            'BillingPostCode' => $sanitizedData['postcode'] ?? 'AB12CD',
            'BillingCountry' => $sanitizedData['country'] ?? 'GB', // GB for UK
            'DeliverySurname' => $sanitizedData['deliveryLastName'] ?? $sanitizedData['lastName'] ?? 'User',
            'DeliveryFirstnames' => $sanitizedData['deliveryFirstName'] ?? $sanitizedData['firstName'] ?? 'Test',
            'DeliveryAddress1' => $sanitizedData['deliveryAddress1'] ?? $sanitizedData['address1'] ?? 'Test Address 1',
            'DeliveryCity' => $sanitizedData['deliveryCity'] ?? $sanitizedData['city'] ?? 'Test City',
            'DeliveryPostCode' => $sanitizedData['deliveryPostcode'] ?? $sanitizedData['postcode'] ?? 'AB12CD',
            'DeliveryCountry' => $sanitizedData['deliveryCountry'] ?? $sanitizedData['country'] ?? 'GB',
            'CustomerEmail' => $sanitizedData['email'] ?? 'test@example.com',
            'ApplyAVSCV2' => $this->config['applyAVSCV2'],
            'Apply3DSecure' => $this->config['apply3DSecure'],
        ];

        if (strtolower($this->config['integrationType']) === 'server') {
            // For Server, you send these details to SagePay, they give you a NextURL to redirect the customer.
            // The response from this initial POST is not encrypted usually.
            try {
                // $response = $this->httpClient('POST', $this->getRedirectUrl(), http_build_query($payload), ['Content-Type' => 'application/x-www-form-urlencoded']);
                // Parse $response['body'] which is key=value pairs
                // Mocked response for vspserver-register.vsp
                if ($sanitizedData['amount'] == 999.99) { // Simulate registration failure
                    // throw new InitializationException('SagePayGateway: Failed to register transaction with SagePay (simulated). Status: INVALID');
                    $apiResponse = ['Status' => 'INVALID', 'StatusDetail' => 'Simulated error: Invalid amount.', 'VPSTxId' => '','SecurityKey' => '', 'NextURL' => ''];
                } else {
                    $apiResponse = [
                        'Status' => 'OK',
                        'StatusDetail' => 'Successfully registered transaction with Sage Pay.',
                        'VPSTxId' => '{' . strtoupper(uniqid('SAGEPAYTXID-')) . '}', // Example: {ABC123XYZ-1234-ABCD-1234-ABC123XYZ4567}
                        'SecurityKey' => strtoupper(uniqid('SAGEPAYSECKEY')), // 10 char alphanumeric
                        'NextURL' => ($this->config['isSandbox'] ? 'https://test.sagepay.com/gateway/paymentpage?' : 'https://live.sagepay.com/gateway/paymentpage?') . 'txid=' . urlencode('{' . strtoupper(uniqid('SAGEPAYTXID-')) . '}'),
                    ];
                }

                if ($apiResponse['Status'] !== 'OK') {
                    throw new InitializationException('SagePayGateway: Failed to register transaction. Status: ' . $apiResponse['Status'] . ' - ' . ($apiResponse['StatusDetail'] ?? 'Unknown error.'));
                }

                return [
                    'status' => 'pending_redirect',
                    'message' => 'SagePay Server transaction registered. Redirect customer to NextURL.',
                    'redirectUrl' => $apiResponse['NextURL'],
                    'gatewayReferenceId' => $apiResponse['VPSTxId'], // SagePay's transaction ID
                    'securityKey' => $apiResponse['SecurityKey'], // Needed for validating callback
                    'orderId' => $sanitizedData['orderId'],
                    'rawData' => $apiResponse
                ];
            } catch (\Exception $e) {
                throw new InitializationException('SagePayGateway: Server registration failed. ' . $e->getMessage(), 0, $e);
            }
        } elseif (strtolower($this->config['integrationType']) === 'form') {
            // For Form, you encrypt the payload and POST it via user's browser.
            $cryptPayload = [];
            foreach ($payload as $key => $value) {
                $cryptPayload[] = $key . '=' . $value;
            }
            $encryptedCrypt = $this->encryptAes(implode('&', $cryptPayload), $this->config['encryptionPassword']);

            return [
                'status' => 'pending_redirect',
                'message' => 'Prepare form post to SagePay with encrypted Crypt field.',
                'redirectUrl' => $this->getRedirectUrl(),
                'formData' => [
                    'VPSProtocol' => $payload['VPSProtocol'],
                    'TxType' => $payload['TxType'],
                    'Vendor' => $this->config['vendorName'],
                    'Crypt' => $encryptedCrypt,
                ],
                'orderId' => $sanitizedData['orderId'],
            ];
        }
        // Direct integration would be an API call here, not a redirect setup.
        throw new InitializationException('SagePayGateway: Direct integration not fully mocked in initialize.');
    }

    public function process(array $data): array
    {
        // Handles the NotificationURL POST from SagePay (for Server integration)
        // Or the redirect back with Crypt field (for Form integration)
        $sanitizedData = $this->sanitize($data);
        $integrationType = strtolower($this->config['integrationType']);

        if ($integrationType === 'server') {
            // For server, data is POSTed directly as key-value pairs. Validate using SecurityKey if possible.
            // The SecurityKey check involves comparing a generated signature.
            // Signature: VPSTxId + VendorTxCode + Status + TxAuthNo + VendorName (lowercase) + AVSCV2 + SecurityKey + AddressResult + PostCodeResult + CV2Result + GiftAid + 3DSecureStatus + CAVV + AddressStatus + PayerStatus + CardType + Last4Digits + DeclineCode + ExpiryDate + FraudResponse + BankAuthCode
            // This is complex and not fully mocked here.
            $status = $sanitizedData['Status'] ?? 'UNKNOWN'; // OK, NOTAUTHED, ABORT, REJECTED, AUTHENTICATED, REGISTERED, ERROR
            $vpsTxId = $sanitizedData['VPSTxId'] ?? null;
            $vendorTxCode = $sanitizedData['VendorTxCode'] ?? null;

            $isSuccess = $status === 'OK'; // 'OK' means authorised successfully.
            $isFailed = in_array($status, ['NOTAUTHED', 'ABORT', 'REJECTED', 'ERROR']);
            $isPending = in_array($status, ['AUTHENTICATED', 'REGISTERED']); // E.g. 3DS authenticated, awaiting completion

            return [
                'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
                'message' => 'SagePay Server notification processed. Status: ' . $status . ' - ' . ($sanitizedData['StatusDetail'] ?? ''),
                'transactionId' => $vpsTxId,
                'orderId' => $vendorTxCode,
                'paymentStatus' => $status,
                'authCode' => $sanitizedData['TxAuthNo'] ?? null,
                'rawData' => $sanitizedData
            ];
        } elseif ($integrationType === 'form') {
            if (empty($sanitizedData['Crypt'])) {
                throw new ProcessingException('SagePayGateway (Form): Encrypted response (Crypt) not found.');
            }
            $decryptedString = $this->decryptAes($sanitizedData['Crypt'], $this->config['encryptionPassword']);
            parse_str($decryptedString, $decryptedData);
            $parsedData = $this->sanitize($decryptedData);

            $status = $parsedData['Status'] ?? 'UNKNOWN';
            $isSuccess = $status === 'OK';
            $isFailed = in_array($status, ['NOTAUTHED', 'ABORT', 'REJECTED', 'ERROR']);

            return [
                'status' => $isSuccess ? 'success' : 'failed',
                'message' => 'SagePay Form response processed. Status: ' . $status . ' - ' . ($parsedData['StatusDetail'] ?? ''),
                'transactionId' => $parsedData['VPSTxId'] ?? null,
                'orderId' => $parsedData['VendorTxCode'] ?? null,
                'paymentStatus' => $status,
                'authCode' => $parsedData['TxAuthNo'] ?? null,
                'rawData' => $parsedData
            ];
        }
        throw new ProcessingException('SagePayGateway: Integration type not supported for process.');
    }

    public function verify(array $data): array
    {
        // Verification for SagePay often involves querying their reporting API or using VPSTxId.
        // This is a highly simplified mock and doesn't represent a real API call to SagePay.
        $sanitizedData = $this->sanitize($data);
        $transactionId = $sanitizedData['transactionId'] ?? null; // VPSTxId
        $orderId = $sanitizedData['orderId'] ?? null; // VendorTxCode

        if (!$transactionId && !$orderId) {
            throw new VerificationException('SagePayGateway: Missing transactionId (VPSTxId) or orderId (VendorTxCode) for verification.');
        }

        // Simulate a lookup (no actual API call in this mock)
        $mockStatus = 'OK';
        if (($transactionId ?: $orderId) === 'fail_verify_ref') {
            $mockStatus = 'NOTAUTHED';
        } elseif (($transactionId ?: $orderId) === 'pending_verify_ref') {
            $mockStatus = 'AUTHENTICATED'; // Or some other pending state
        }

        $apiResponse = [
            'VPSTxId' => $transactionId ?? '{FAKE-VERIFY-ID-' . uniqid() . '}',
            'VendorTxCode' => $orderId ?? 'fake_order_verify_' . uniqid(),
            'Status' => $mockStatus,
            'StatusDetail' => 'Mock verification status: ' . $mockStatus,
            'Amount' => sprintf('%.2f', $sanitizedData['original_amount_for_test'] ?? 100.00),
            'Currency' => 'GBP',
            'TxAuthNo' => 'mock_auth_' . rand(10000, 99999),
        ];

        $isSuccess = $apiResponse['Status'] === 'OK';
        $isPending = in_array($apiResponse['Status'], ['AUTHENTICATED', 'REGISTERED']);

        return [
            'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
            'message' => 'SagePay mock verification result: ' . $apiResponse['StatusDetail'],
            'transactionId' => $apiResponse['VPSTxId'],
            'orderId' => $apiResponse['VendorTxCode'],
            'paymentStatus' => $apiResponse['Status'],
            'rawData' => $apiResponse
        ];
    }

    public function refund(array $data): array
    {
        // SagePay refunds require specific transaction details from the original payment.
        // POST to /gateway/service/directrefund.vsp
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // VPSTxId of original transaction
            throw new RefundException('SagePayGateway: Missing VPSTxId (transactionId) for refund.');
        }
        if (empty($sanitizedData['vendorTxCode'])) { // Your original VendorTxCode
            throw new RefundException('SagePayGateway: Missing VendorTxCode for refund.');
        }
        if (empty($sanitizedData['securityKey'])) { // SecurityKey from original transaction response
            throw new RefundException('SagePayGateway: Missing SecurityKey for refund.');
        }
        if (empty($sanitizedData['txAuthNo'])) { // TxAuthNo from original transaction
            throw new RefundException('SagePayGateway: Missing TxAuthNo for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('SagePayGateway: Invalid or missing amount for refund.');
        }

        $payload = [
            'VPSProtocol' => '3.00',
            'TxType' => 'REFUND',
            'Vendor' => $this->config['vendorName'],
            'VendorTxCode' => $sanitizedData['refundVendorTxCode'] ?? ('REF-' . $sanitizedData['vendorTxCode'] . '-' . uniqid()), // Must be unique
            'Amount' => sprintf('%.2f', $sanitizedData['amount']),
            'Currency' => $sanitizedData['currency'] ?? $this->config['currency'],
            'Description' => $sanitizedData['reason'] ?? 'Merchant requested refund',
            'RelatedVPSTxId' => $sanitizedData['transactionId'],
            'RelatedVendorTxCode' => $sanitizedData['vendorTxCode'],
            'RelatedSecurityKey' => $sanitizedData['securityKey'],
            'RelatedTxAuthNo' => $sanitizedData['txAuthNo'],
        ];

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/directrefund.vsp', http_build_query($payload), ['Content-Type' => 'application/x-www-form-urlencoded']);
            // Parse $response['body'] for Status, StatusDetail, VPSTxId (for the refund), TxAuthNo (for the refund)
            // Mocked refund response
            $mockStatus = 'OK';
            if ($sanitizedData['amount'] == 999.99) {
                $mockStatus = 'MALFORMED';
                // throw new RefundException('SagePayGateway: Refund API rejected (simulated malformed amount 999.99).');
            }
            $apiResponse = [
                'Status' => $mockStatus,
                'StatusDetail' => $mockStatus === 'OK' ? 'Refund successful.' : 'Refund failed (simulated).',
                'VPSTxId' => '{REFUND-ID-' . uniqid() . '}', // VPSTxId for this refund transaction
                'TxAuthNo' => 'REFUNDAUTH' . rand(10000, 99999)
            ];

            if ($apiResponse['Status'] !== 'OK') {
                throw new RefundException('SagePayGateway: Failed to process refund. Status: ' . $apiResponse['Status'] . ' - ' . ($apiResponse['StatusDetail'] ?? 'Unknown error'));
            }

            return [
                'status' => 'success',
                'message' => 'SagePay refund status: ' . $apiResponse['StatusDetail'],
                'refundId' => $apiResponse['VPSTxId'], // This is the VPSTxId of the refund transaction itself
                'transactionId' => $sanitizedData['transactionId'], // Original transactionId
                'paymentStatus' => 'refunded',
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new RefundException('SagePayGateway: Refund request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
