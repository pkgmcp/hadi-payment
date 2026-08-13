<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class PayUGateway extends PaymentGateway
{
    // Note: PayU has different APIs for different regions (India, Latam, etc.)
    // This is a generic mock, conceptually similar to PayU India (PayUBiz/PayUMoney)
    private const API_BASE_URL_SANDBOX_INDIA = 'https://test.payu.in'; // Example for PayU India
    private const API_BASE_URL_PRODUCTION_INDIA = 'https://secure.payu.in'; // Example for PayU India

    private const API_BASE_URL_SANDBOX_LATAM = 'https://sandbox.api.payulatam.com';
    private const API_BASE_URL_PRODUCTION_LATAM = 'https://api.payulatam.com';

    protected function getDefaultConfig(): array
    {
        return [
            'merchantKey' => '',    // PayU Merchant Key (e.g., from PayUBiz dashboard)
            'salt' => '',           // PayU Salt (e.g., from PayUBiz dashboard)
            'authHeader' => '',     // For some PayU APIs (like Latam) an Authorization header is used instead of hash.
            'accountId' => '',      // For PayU Latam
            'merchantId' => '',     // For PayU Latam
            'apiLogin' => '',       // For PayU Latam API calls
            'apiKey' => '',         // For PayU Latam API calls
            'isSandbox' => true,
            'region' => 'IN', // 'IN' for India, 'LATAM' for Latin America. Determines API endpoints and hashing.
            'timeout' => 60,
            'returnUrl' => 'https://example.com/payu/success', // Your success URL
            'cancelUrl' => 'https://example.com/payu/cancel',   // Your cancel/failure URL
            'notifyUrl' => 'https://example.com/payu/notify',   // Your notification/webhook URL
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (strtoupper($config['region']) === 'IN') {
            if (empty($config['merchantKey'])) {
                throw new InvalidConfigurationException('PayUGateway (India): merchantKey is required.');
            }
            if (empty($config['salt'])) {
                throw new InvalidConfigurationException('PayUGateway (India): salt is required.');
            }
        } elseif (strtoupper($config['region']) === 'LATAM') {
            if (empty($config['accountId'])) {
                throw new InvalidConfigurationException('PayUGateway (LATAM): accountId is required.');
            }
            if (empty($config['merchantId'])) {
                throw new InvalidConfigurationException('PayUGateway (LATAM): merchantId is required.');
            }
            if (empty($config['apiLogin'])) {
                throw new InvalidConfigurationException('PayUGateway (LATAM): apiLogin is required for API operations.');
            }
            if (empty($config['apiKey'])) {
                throw new InvalidConfigurationException('PayUGateway (LATAM): apiKey is required for API operations.');
            }
        } else {
            throw new InvalidConfigurationException('PayUGateway: Invalid region specified.');
        }
    }

    private function getApiBaseUrl(): string
    {
        if (strtoupper($this->config['region']) === 'IN') {
            return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX_INDIA : self::API_BASE_URL_PRODUCTION_INDIA;
        }
        return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX_LATAM : self::API_BASE_URL_PRODUCTION_LATAM;
    }

    private function generateIndiaHash(array $data, string $salt): string
    {
        // Hash sequence for PayU India (varies for request and response)
        // Example for request: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT
        $hashString = $data['key'] . '|' . $data['txnid'] . '|' . $data['amount'] . '|' . $data['productinfo'] . '|' . $data['firstname'] . '|' . $data['email'] .
                      '|' . ($data['udf1'] ?? '') . '|' . ($data['udf2'] ?? '') . '|' . ($data['udf3'] ?? '') . '|' . ($data['udf4'] ?? '') . '|' . ($data['udf5'] ?? '') .
                      '||||||' . $salt; // Ensure correct number of pipes if UDFs are empty
        return strtolower(hash('sha512', $hashString));
    }

    private function verifyIndiaResponseHash(array $data, string $salt): bool
    {
        // Example for response: SALT|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key
        // Order is reverse of request hash components, with SALT first.
        $hashString = $salt . '|' . ($data['status'] ?? '') .
                      '||||||' . ($data['udf5'] ?? '') . '|' . ($data['udf4'] ?? '') . '|' . ($data['udf3'] ?? '') . '|' . ($data['udf2'] ?? '') . '|' . ($data['udf1'] ?? '') .
                      '|' . ($data['email'] ?? '') . '|' . ($data['firstname'] ?? '') . '|' . ($data['productinfo'] ?? '') . '|' . ($data['amount'] ?? '') . '|' . ($data['txnid'] ?? '') . '|' . ($data['key'] ?? '');

        $generatedHash = strtolower(hash('sha512', $hashString));
        return ($data['hash'] ?? '') === $generatedHash;
    }

    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);

        if (strtoupper($this->config['region']) === 'IN') {
            return $this->initializeIndia($sanitizedData);
        }
        return $this->initializeLatam($sanitizedData);
    }

    private function initializeLatam(array $sanitizedData): array
    {
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('PayUGateway (LATAM): Invalid or missing amount.');
        }
        if (empty($sanitizedData['orderId'])) {
            throw new InitializationException('PayUGateway (LATAM): Missing orderId (referenceCode).');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('PayUGateway (LATAM): Missing currency.');
        }
        if (empty($sanitizedData['buyerEmail'])) {
            throw new InitializationException('PayUGateway (LATAM): Missing buyerEmail.');
        }

        $referenceCode = $sanitizedData['orderId'];
        $amount = sprintf('%.2f', $sanitizedData['amount']);
        $currency = strtoupper($sanitizedData['currency']);

        $signature = hash('sha256', $this->config['apiKey'] . '~' . $this->config['merchantId'] . '~' . $referenceCode . '~' . $amount . '~' . $currency);

        $payload = [
            'accountId' => $this->config['accountId'],
            'referenceCode' => $referenceCode,
            'description' => $sanitizedData['description'] ?? 'Payment via PayU',
            'amount' => $amount,
            'currency' => $currency,
            'signature' => $signature,
            'buyerEmail' => $sanitizedData['buyerEmail'],
            'buyerFullName' => $sanitizedData['buyerFullName'] ?? '',
            'responseUrl' => $this->config['returnUrl'],
            'confirmationUrl' => $this->config['notifyUrl'],
        ];

        // PayU LATAM hosts the checkout page; the payment is submitted via a form POST.
        $redirectUrl = $this->getApiBaseUrl() . '/payments-api/4.0/checkout';

        return [
            'status' => 'pending_redirect',
            'message' => 'Redirect to PayU payment page.',
            'redirectUrl' => $redirectUrl,
            'formData' => $payload,
            'orderId' => $referenceCode,
            'signature' => $signature,
        ];
    }

    private function initializeIndia(array $sanitizedData): array
    {
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('PayUGateway (India): Invalid or missing amount.');
        }
        if (empty($sanitizedData['orderId'])) {
            throw new InitializationException('PayUGateway (India): Missing orderId (txnid).');
        }
        if (empty($sanitizedData['firstName'])) {
            throw new InitializationException('PayUGateway (India): Missing firstName.');
        }
        if (empty($sanitizedData['email'])) {
            throw new InitializationException('PayUGateway (India): Missing email.');
        }
        if (empty($sanitizedData['phone'])) {
            throw new InitializationException('PayUGateway (India): Missing phone.');
        }
        if (empty($sanitizedData['productInfo'])) {
            throw new InitializationException('PayUGateway (India): Missing productInfo.');
        }

        $payload = [
            'key' => $this->config['merchantKey'],
            'txnid' => $sanitizedData['orderId'],
            'amount' => sprintf('%.2f', $sanitizedData['amount']),
            'productinfo' => $sanitizedData['productInfo'],
            'firstname' => $sanitizedData['firstName'],
            'email' => $sanitizedData['email'],
            'phone' => $sanitizedData['phone'],
            'surl' => $this->config['returnUrl'],
            'furl' => $this->config['cancelUrl'],
            'curl' => $this->config['cancelUrl'], // often same as furl
            'hash' => '', // Will be generated
            'udf1' => $sanitizedData['udf1'] ?? '',
            'udf2' => $sanitizedData['udf2'] ?? '',
            'udf3' => $sanitizedData['udf3'] ?? '',
            'udf4' => $sanitizedData['udf4'] ?? '',
            'udf5' => $sanitizedData['udf5'] ?? '',
            // 'service_provider' => 'payu_paisa', // For PayUMoney, often not needed for PayUBiz
        ];
        $payload['hash'] = $this->generateIndiaHash($payload, $this->config['salt']);

        // For PayU India, this typically involves redirecting the user with POST data.
        $redirectUrl = $this->getApiBaseUrl() . '/_payment';

        return [
            'status' => 'pending_redirect',
            'message' => 'Redirect to PayU payment page.',
            'redirectUrl' => $redirectUrl,
            'formData' => $payload, // Data to be POSTed to the redirectUrl
            'orderId' => $sanitizedData['orderId'],
        ];
    }

    public function process(array $data): array
    {
        // Process is typically handling the POST back from PayU to surl/furl or a webhook to notifyUrl.
        $sanitizedData = $this->sanitize($data); // Data from POST back or webhook

        if (strtoupper($this->config['region']) === 'IN') {
            // Verify hash for India response
            if (!$this->verifyIndiaResponseHash($sanitizedData, $this->config['salt'])) {
                throw new ProcessingException('PayUGateway (India): Response hash mismatch.');
            }

            $status = $sanitizedData['status'] ?? 'failure'; // 'success', 'failure', 'pending'
            $isSuccess = strtolower($status) === 'success';
            $isFailed = strtolower($status) === 'failure';
            $isPending = strtolower($status) === 'pending';

            return [
                'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
                'message' => 'PayU (India) payment processed. Status: ' . $status,
                'transactionId' => $sanitizedData['payuMoneyId'] ?? ($sanitizedData['mihpayid'] ?? null), // Gateway transaction ID
                'orderId' => $sanitizedData['txnid'] ?? null,
                'paymentStatus' => $status,
                'rawData' => $sanitizedData
            ];
        }

        // LATAM: webhook / confirmation notification
        if (!$this->verifyLatamSignature($sanitizedData)) {
            throw new ProcessingException('PayUGateway (LATAM): Invalid signature in notification.');
        }

        $state = strtolower($sanitizedData['state_pol'] ?? $sanitizedData['transactionState'] ?? '');
        $pol = (string) ($sanitizedData['pol'] ?? '');

        // PayU LATAM state_pol: 4 = approved, 6 = declined, 7 = pending, 12 = expired
        if ($state === 'approved' || $pol === '4') {
            $resultStatus = 'success';
        } elseif ($pol === '7' || $state === 'pending') {
            $resultStatus = 'pending';
        } else {
            $resultStatus = 'failed';
        }

        return [
            'status' => $resultStatus,
            'message' => 'PayU (LATAM) payment processed. State: ' . ($sanitizedData['state_pol'] ?? $sanitizedData['transactionState'] ?? 'unknown'),
            'transactionId' => $sanitizedData['transactionId'] ?? ($sanitizedData['transaction_id'] ?? null),
            'orderId' => $sanitizedData['reference_sale'] ?? ($sanitizedData['referenceCode'] ?? null),
            'paymentStatus' => $resultStatus,
            'rawData' => $sanitizedData
        ];
    }

    private function verifyLatamSignature(array $data): bool
    {
        $signature = $data['sign'] ?? '';
        $referenceCode = $data['reference_sale'] ?? ($data['referenceCode'] ?? '');
        $amount = $data['value'] ?? ($data['TX_VALUE'] ?? '');
        $currency = $data['currency'] ?? ($data['currencyCode'] ?? '');

        if ($referenceCode === '' || $amount === '' || $currency === '' || $signature === '') {
            return false;
        }

        $expected = hash('sha256', $this->config['apiKey'] . '~' . $this->config['merchantId'] . '~' . $referenceCode . '~' . sprintf('%.2f', $amount) . '~' . strtoupper($currency));

        return hash_equals($expected, $signature);
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['orderId'])) { // Your order ID (txnid)
            throw new VerificationException('PayUGateway: Missing orderId (txnid) for verification.');
        }

        if (strtoupper($this->config['region']) === 'IN') {
            // PayU India has a verify_payment API.
            // POST request with fields: key, command (verify_payment), var1 (txnid), hash
            $verifyPayload = [
                'key' => $this->config['merchantKey'],
                'command' => 'verify_payment',
                'var1' => $sanitizedData['orderId'],
                'hash' => '',
            ];
            $hashString = $verifyPayload['key'] . '|' . $verifyPayload['command'] . '|' . $verifyPayload['var1'] . '|' . $this->config['salt'];
            $verifyPayload['hash'] = strtolower(hash('sha512', $hashString));

            try {
                // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/merchant/postservice.php?form=2', $verifyPayload, ['Content-Type' => 'application/x-www-form-urlencoded']);
                // Mocked verify_payment response
                $mockApiStatus = 'success'; // 'success', 'failure', 'pending' or other specific codes
                $mockTransactionDetails = [];

                if ($sanitizedData['orderId'] === 'fail_verify_order') {
                    $mockApiStatus = 'failure';
                } elseif ($sanitizedData['orderId'] === 'pending_verify_order') {
                    $mockApiStatus = 'pending';
                } else {
                     $mockTransactionDetails = [
                        'mihpayid' => 'payu_verify_' . uniqid(),
                        'mode' => 'CC',
                        'status' => 'success',
                        'unmappedstatus' => 'captured',
                        'key' => $this->config['merchantKey'],
                        'txnid' => $sanitizedData['orderId'],
                        'amount' => $sanitizedData['original_amount_for_test'] ?? '100.00',
                        'cardCategory' => 'domestic',
                        'discount' => '0.00',
                        'net_amount_debit' => $sanitizedData['original_amount_for_test'] ?? '100.00',
                        'addedon' => gmdate("Y-m-d H:i:s"),
                        'productinfo' => 'Test Product',
                        'firstname' => 'Test User',
                        'lastname' => '',
                        'address1' => '',
                        'address2' => '',
                        'city' => '',
                        'state' => '',
                        'country' => '',
                        'zipcode' => '',
                        'email' => 'test@example.com',
                        'phone' => '9999999999',
                        'udf1' => '',
                        'udf2' => '',
                        'udf3' => '',
                        'udf4' => '',
                        'udf5' => '',
                        'hash' => 'mock_verified_hash', // In real scenario, this hash should be validated
                        'field1' => '',
                        'field2' => '',
                        'field3' => '',
                        'field4' => '',
                        'field5' => '',
                        'field6' => '',
                        'field7' => '',
                        'field8' => '',
                        'field9' => 'SUCCESS', // Payment status
                        'payment_source' => 'payu',
                        'PG_TYPE' => 'AXISPG',
                        'bank_ref_num' => 'bankref' . uniqid(),
                        'bankcode' => 'CC',
                        'error' => 'E000',
                        'error_Message' => 'No Error'
                     ];
                }

                $apiResponse = [
                    'status' => ($mockApiStatus === 'success' ? 1 : 0), // API call status
                    'msg' => $mockApiStatus === 'success' ? 'Transaction Fetched Successfully' : 'No Transaction Found',
                    'transaction_details' => [$sanitizedData['orderId'] => $mockTransactionDetails] // Or a single object if only one txnid queried
                ];

                // Simulate API call error
                if ($sanitizedData['orderId'] === 'api_error_verify_order') {
                    throw new VerificationException('PayUGateway (India): API error during verification (simulated).');
                }

                if (($apiResponse['status'] ?? 0) != 1 || empty($apiResponse['transaction_details'][$sanitizedData['orderId']])) {
                    throw new VerificationException('PayUGateway (India): Failed to verify payment. ' . ($apiResponse['msg'] ?? 'Transaction not found or API error.'));
                }

                $transactionDetails = $apiResponse['transaction_details'][$sanitizedData['orderId']];
                $paymentStatus = $transactionDetails['status'] ?? 'failure'; // status from verify_payment API
                $isSuccess = strtolower($paymentStatus) === 'success';
                $isPending = strtolower($paymentStatus) === 'pending';

                return [
                    'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                    'message' => 'PayU (India) verification result: ' . $paymentStatus . ' - ' . ($transactionDetails['error_Message'] ?? ''),
                    'transactionId' => $transactionDetails['mihpayid'] ?? null,
                    'orderId' => $transactionDetails['txnid'] ?? $sanitizedData['orderId'],
                    'paymentStatus' => $paymentStatus,
                    'rawData' => $transactionDetails
                ];
            } catch (\Exception $e) {
                throw new VerificationException('PayUGateway (India): Verification request failed. ' . $e->getMessage(), 0, $e);
            }
        }

        // LATAM: query order details via the Payments API.
        try {
            $orderDetails = $this->getLatamOrderDetails($sanitizedData['orderId']);

            $state = $orderDetails['orderStatus'] ?? $orderDetails['transactionState'] ?? '';
            $state = strtolower((string) $state);

            if (str_contains($state, 'approved') || $state === 'success') {
                $resultStatus = 'success';
            } elseif (str_contains($state, 'pending') || $state === 'in_progress') {
                $resultStatus = 'pending';
            } else {
                $resultStatus = 'failed';
            }

            return [
                'status' => $resultStatus,
                'message' => 'PayU (LATAM) verification result: ' . $state,
                'transactionId' => $orderDetails['transactionId'] ?? null,
                'orderId' => $sanitizedData['orderId'],
                'paymentStatus' => $state,
                'rawData' => $orderDetails,
            ];
        } catch (\Exception $e) {
            throw new VerificationException('PayUGateway (LATAM): Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    private function getLatamOrderDetails(string $referenceCode): array
    {
        // PayU LATAM Payments API command: GET_ORDER_DETAIL_BY_REFERENCE_CODE
        $payload = [
            'language' => 'en',
            'command' => 'GET_ORDER_DETAIL_BY_REFERENCE_CODE',
            'merchant' => [
                'apiLogin' => $this->config['apiLogin'],
                'apiKey' => $this->config['apiKey'],
            ],
            'details' => [
                'referenceCode' => $referenceCode,
            ],
            'test' => (bool) $this->config['isSandbox'],
        ];

        try {
            // $response = $this->httpClient(
            //     'POST',
            //     $this->getApiBaseUrl() . '/payments-api/4.0/service/PayUOperations',
            //     $payload,
            //     ['Content-Type' => 'application/json', 'Accept' => 'application/json']
            // );
            // Mocked response
            $response = [
                'code' => 'SUCCESS',
                'result' => [
                    'payload' => [
                        'referenceCode' => $referenceCode,
                        'orderStatus' => $referenceCode === 'pending_verify_order' ? 'PENDING' : 'APPROVED',
                        'transactionId' => 'payu_latam_tx_' . uniqid(),
                    ],
                ],
            ];

            if ($referenceCode === 'fail_verify_order') {
                $response['code'] = 'ERROR';
                $response['result']['payload']['orderStatus'] = 'DECLINED';
            }
        } catch (\Exception $e) {
            throw new VerificationException('PayUGateway (LATAM): API request failed. ' . $e->getMessage(), 0, $e);
        }

        if (($response['code'] ?? '') !== 'SUCCESS') {
            throw new VerificationException('PayUGateway (LATAM): API returned error code: ' . ($response['code'] ?? 'unknown'));
        }

        return $response['result']['payload'] ?? [];
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // PayU transaction ID (mihpayid/payuMoneyId)
            throw new RefundException('PayUGateway: Missing transactionId for refund.');
        }
        if (empty($sanitizedData['orderId'])) { // Your original order ID (txnid)
            throw new RefundException('PayUGateway: Missing orderId (txnid) for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('PayUGateway: Invalid or missing amount for refund.');
        }

        if (strtoupper($this->config['region']) === 'IN') {
            // PayU India refund API (cancel_refund_transaction)
            // POST fields: key, command (cancel_refund_transaction), var1 (payuid), var2 (merchant_txn_id), var3 (amount), var4 (refund_id), hash
            $refundId = 'refund_' . $sanitizedData['orderId'] . '_' . uniqid(); // Your unique ID for this refund attempt
            $refundPayload = [
                'key' => $this->config['merchantKey'],
                'command' => 'cancel_refund_transaction',
                'var1' => $sanitizedData['transactionId'], // payuId
                'var2' => $sanitizedData['orderId'],      // txnid
                'var3' => sprintf('%.2f', $sanitizedData['amount']),
                'var4' => $refundId,
                'hash' => '',
            ];
            $hashString = $refundPayload['key'] . '|' . $refundPayload['command'] . '|' . $refundPayload['var1'] . '|' . $refundPayload['var2'] . '|' . $refundPayload['var3'] . '|' . $refundPayload['var4'] . '|' . $this->config['salt'];
            $refundPayload['hash'] = strtolower(hash('sha512', $hashString));

            try {
                // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/merchant/postservice.php?form=2', $refundPayload, ['Content-Type' => 'application/x-www-form-urlencoded']);
                // Mocked refund response
                $mockApiStatus = 1; // 1 for success, 0 for failure
                $mockMessage = 'Refund request successfully initiated, it will be processed in 24-48 hours.';
                $mockErrorCode = 'E000';

                if ($sanitizedData['amount'] == 999.99) { // Simulate refund failure
                    $mockApiStatus = 0;
                    $mockMessage = 'Refund failed due to insufficient funds (simulated).';
                    $mockErrorCode = 'E123';
                }

                $apiResponse = [
                    'status' => $mockApiStatus,
                    'msg' => $mockMessage,
                    'error_code' => $mockErrorCode,
                    'mihpayid' => $sanitizedData['transactionId'], // original payu id
                    'merchant_ref_no' => $refundId, // your refund id
                    // 'bank_ref_num' => 'bank_refund_ref_xxx' // if available immediately
                ];

                if (($apiResponse['status'] ?? 0) != 1) {
                     // Error codes like E203: Refund request already initiated, E204: Invalid Payuid, etc.
                    throw new RefundException('PayUGateway (India): Failed to initiate refund. ' . ($apiResponse['msg'] ?? 'API error.'));
                }

                // Refund status is often asynchronous, webhook confirms final status.
                return [
                    'status' => 'pending', // Refund request accepted, pending final processing
                    'message' => 'PayU (India) refund request status: ' . $apiResponse['msg'],
                    'refundId' => $apiResponse['merchant_ref_no'] ?? $refundId,
                    'transactionId' => $apiResponse['mihpayid'] ?? $sanitizedData['transactionId'],
                    'paymentStatus' => 'refund_pending',
                    'rawData' => $apiResponse
                ];
            } catch (\Exception $e) {
                throw new RefundException('PayUGateway (India): Refund request failed. ' . $e->getMessage(), 0, $e);
            }
        }

        // LATAM: submit a REFUND transaction via the Payments API.
        try {
            $refundPayload = [
                'language' => 'en',
                'command' => 'SUBMIT_TRANSACTION',
                'merchant' => [
                    'apiLogin' => $this->config['apiLogin'],
                    'apiKey' => $this->config['apiKey'],
                ],
                'transaction' => [
                    'order' => [
                        'accountId' => $this->config['accountId'],
                        'referenceCode' => $sanitizedData['orderId'],
                        'description' => 'Refund for ' . $sanitizedData['orderId'],
                        'additionalValues' => [
                            'TX_VALUE' => [
                                'value' => sprintf('%.2f', $sanitizedData['amount']),
                                'currency' => $sanitizedData['currency'] ?? 'USD',
                            ],
                        ],
                    ],
                    'type' => 'REFUND',
                    'parentTransactionId' => $sanitizedData['transactionId'],
                    'reason' => $sanitizedData['reason'] ?? 'Requested by customer',
                ],
                'test' => (bool) $this->config['isSandbox'],
            ];

            // $response = $this->httpClient(
            //     'POST',
            //     $this->getApiBaseUrl() . '/payments-api/4.0/service/PayUOperations',
            //     $refundPayload,
            //     ['Content-Type' => 'application/json', 'Accept' => 'application/json']
            // );
            // Mocked response
            $response = [
                'code' => 'SUCCESS',
                'error' => null,
                'transactionResponse' => [
                    'transactionId' => 'payu_latam_refund_' . uniqid(),
                    'state' => 'PENDING',
                    'responseCode' => 'APPROVED',
                    'responseMessage' => 'Refund request accepted',
                ],
            ];

            if ($sanitizedData['amount'] == 999.99) { // Simulate refund failure
                $response['code'] = 'ERROR';
                $response['transactionResponse']['responseCode'] = 'DECLINED';
                $response['transactionResponse']['responseMessage'] = 'Refund failed due to insufficient funds (simulated).';
            }
        } catch (\Exception $e) {
            throw new RefundException('PayUGateway (LATAM): Refund request failed. ' . $e->getMessage(), 0, $e);
        }

        if (($response['code'] ?? '') !== 'SUCCESS') {
            throw new RefundException('PayUGateway (LATAM): Failed to initiate refund. ' . ($response['transactionResponse']['responseMessage'] ?? 'API error.'));
        }

        return [
            'status' => 'pending',
            'message' => 'PayU (LATAM) refund request status: ' . ($response['transactionResponse']['responseMessage'] ?? 'pending'),
            'refundId' => $response['transactionResponse']['transactionId'] ?? null,
            'transactionId' => $sanitizedData['transactionId'],
            'paymentStatus' => 'refund_pending',
            'rawData' => $response,
        ];
    }
}
