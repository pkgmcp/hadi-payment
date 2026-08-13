<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class AuthorizeNetGateway extends PaymentGateway
{
    private const API_BASE_URL_SANDBOX = 'https://apitest.authorize.net/xml/v1/request.api';
    private const API_BASE_URL_PRODUCTION = 'https://api.authorize.net/xml/v1/request.api'; // Or api2 for JSON

    protected function getDefaultConfig(): array
    {
        return [
            'apiLoginId' => '',        // Your API Login ID
            'transactionKey' => '',   // Your Transaction Key
            'isSandbox' => true,
            'timeout' => 60,
            'signatureKey' => '',     // Optional: For Webhook Signature Verification
            'solutionId' => 'AAA100000', // Optional: Solution ID if provided by a partner
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['apiLoginId'])) {
            throw new InvalidConfigurationException('AuthorizeNetGateway: apiLoginId is required.');
        }
        if (empty($config['transactionKey'])) {
            throw new InvalidConfigurationException('AuthorizeNetGateway: transactionKey is required.');
        }
    }

    private function getApiBaseUrl(): string
    {
        return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX : self::API_BASE_URL_PRODUCTION;
    }

    private function buildRequest(string $requestType, array $data = []): array
    {
        $merchantAuthentication = [
            'name' => $this->config['apiLoginId'],
            'transactionKey' => $this->config['transactionKey'],
        ];

        $request = [
            $requestType => array_merge([
                'merchantAuthentication' => $merchantAuthentication,
                'refId' => $data['refId'] ?? 'ref' . uniqid(),
            ], $data)
        ];
        return $request;
    }

    private function sendRequest(string $requestType, array $payloadData = []): array
    {
        $requestBody = $this->buildRequest($requestType, $payloadData);
        $headers = ['Content-Type' => 'application/json'];

        // Note: Authorize.Net's primary API is XML, but JSON is also supported on some endpoints.
        // This mock will assume JSON for simplicity with httpClient, but in reality, you might need an XML builder/parser.
        // $response = $this->httpClient('POST', $this->getApiBaseUrl(), json_encode($requestBody), $headers, true);

        // Mocked response structure (simplified)
        $mockResponse = [];
        $transactionId = 'txn_' . uniqid();
        $responseCode = 1; // 1 = Approved, 2 = Declined, 3 = Error, 4 = Held for Review

        if (isset($payloadData['transactionRequest']['amount']) && $payloadData['transactionRequest']['amount'] == '999.99') {
            $responseCode = 2; // Simulate decline
        } elseif (isset($payloadData['transactionRequest']['amount']) && $payloadData['transactionRequest']['amount'] == '888.88') {
            $responseCode = 3; // Simulate error
        }

        if ($requestType === 'createTransactionRequest' || $requestType === 'getHostedPaymentPageRequest') {
            $mockResponse = [
                'transactionResponse' => [
                    'responseCode' => (string)$responseCode,
                    'transId' => $transactionId,
                    'refId' => $payloadData['refId'] ?? $requestBody[$requestType]['refId'],
                    'messages' => [['code' => '1', 'description' => 'Transaction successful.']],
                    'authCode' => 'AUTH' . rand(100000, 999999),
                ],
                'messages' => ['resultCode' => $responseCode == 1 ? 'Ok' : 'Error', 'message' => [['code' => 'I00001', 'text' => 'Successful.']]],
            ];
            if ($requestType === 'getHostedPaymentPageRequest') {
                $mockResponse['token'] = 'token_' . uniqid(); // For hosted page form
            }
        } elseif ($requestType === 'getTransactionDetailsRequest') {
             $mockResponse = [
                'transaction' => [
                    'transId' => $payloadData['transId'],
                    'transactionStatus' => $responseCode == 1 ? 'settledSuccessfully' : 'declined',
                    'responseCode' => (string)$responseCode,
                    'refId' => $payloadData['refId'] ?? null,
                    'submitTimeUTC' => gmdate("Y-m-d\TH:i:s\Z"),
                ],
                'messages' => ['resultCode' => 'Ok', 'message' => [['code' => 'I00001', 'text' => 'Successful.']]],
             ];
        }

        // Simulate a direct error message if responseCode is 3
        if ($responseCode == 3) {
             $mockResponse['messages']['message'][0] = ['code' => 'E00027', 'text' => 'The transaction was unsuccessful.'];
        }

        return [
            'body' => $mockResponse,
            'status_code' => 200 // Authorize.Net often returns 200 even for declines/errors, status is in body
        ];
    }

    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('AuthorizeNetGateway: Invalid or missing amount.');
        }
        if (empty($sanitizedData['orderId'])) {
            throw new InitializationException('AuthorizeNetGateway: Missing orderId.');
        }
        if (empty($sanitizedData['returnUrl'])) { // Required for hosted payment page
            throw new InitializationException('AuthorizeNetGateway: Missing returnUrl for hosted payment page.');
        }

        // This will mock getting a token for Authorize.Net Hosted Payment Page
        $payload = [
            'refId' => $sanitizedData['orderId'],
            'transactionRequest' => [
                'transactionType' => 'authCaptureTransaction',
                'amount' => sprintf('%.2f', $sanitizedData['amount']),
                'order' => [
                    'invoiceNumber' => $sanitizedData['orderId'],
                    'description' => $sanitizedData['description'] ?? 'Payment for order ' . $sanitizedData['orderId'],
                ],
            ],
            'hostedPaymentSettings' => [
                'setting' => [
                    ['settingName' => 'hostedPaymentReturnOptions', 'settingValue' => json_encode(['showReceipt' => true, 'url' => $sanitizedData['returnUrl'], 'urlText' => 'Continue', 'cancelUrl' => $sanitizedData['cancelUrl'] ?? $sanitizedData['returnUrl'] . '?cancel=true'])],
                    ['settingName' => 'hostedPaymentButtonOptions', 'settingValue' => json_encode(['text' => 'Pay'])],
                    ['settingName' => 'hostedPaymentStyleOptions', 'settingValue' => json_encode(['bgColor' => 'blue'])], // Example styling
                    ['settingName' => 'hostedPaymentPaymentOptions', 'settingValue' => json_encode(['cardCodeRequired' => true, 'showCreditCard' => true, 'showBankAccount' => false])],
                    ['settingName' => 'hostedPaymentSecurityOptions', 'settingValue' => json_encode(['captcha' => false])],
                    ['settingName' => 'hostedPaymentShippingAddressOptions', 'settingValue' => json_encode(['show' => false, 'required' => false])],
                    ['settingName' => 'hostedPaymentBillingAddressOptions', 'settingValue' => json_encode(['show' => true, 'required' => true])],
                    ['settingName' => 'hostedPaymentCustomerOptions', 'settingValue' => json_encode(['showEmail' => true, 'requiredEmail' => true])],
                    ['settingName' => 'hostedPaymentOrderOptions', 'settingValue' => json_encode(['show' => true, 'merchantName' => $this->config['merchantName'] ?? 'Your Company'])],
                ]
            ]
        ];

        try {
            $response = $this->sendRequest('getHostedPaymentPageRequest', $payload);

            $messages = $response['body']['messages'] ?? [];
            if (($messages['resultCode'] ?? 'Error') !== 'Ok' || empty($response['body']['token'])) {
                $errorText = $messages['message'][0]['text'] ?? 'Failed to get hosted payment page token.';
                throw new InitializationException('AuthorizeNetGateway: ' . $errorText);
            }

            return [
                'status' => 'pending_redirect',
                'message' => 'Redirect to Authorize.Net hosted payment page.',
                'paymentFormToken' => $response['body']['token'],
                'redirectUrl' => ($this->config['isSandbox'] ? 'https://test.authorize.net/payment/payment' : 'https://accept.authorize.net/payment/payment'), // Base URL for POSTing the form
                'orderId' => $sanitizedData['orderId'],
                'rawData' => $response['body']
            ];
        } catch (\Exception $e) {
            throw new InitializationException('AuthorizeNetGateway: Hosted page initialization failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function process(array $data): array
    {
        // Processing typically involves handling a webhook (Silent Post URL) or a redirect back from Authorize.Net.
        // This mock will simulate a redirect/webhook response processing.
        // IMPORTANT: Verify webhook signature (using $this->config['signatureKey']) in a real scenario!
        $sanitizedData = $this->sanitize($data); // Data from webhook or POST back

        $transactionId = $sanitizedData['x_trans_id'] ?? $sanitizedData['transId'] ?? null;
        $responseCode = $sanitizedData['x_response_code'] ?? $sanitizedData['responseCode'] ?? null; // 1=Approved, 2=Declined, 3=Error
        $orderId = $sanitizedData['x_invoice_num'] ?? $sanitizedData['orderId'] ?? null;
        $amount = $sanitizedData['x_amount'] ?? $sanitizedData['amount'] ?? null;

        if (!$transactionId || !$responseCode) {
            throw new ProcessingException('AuthorizeNetGateway: Invalid data received. Missing transaction ID or response code.');
        }

        $isSuccess = ((string)$responseCode === '1');
        $isFailed = ((string)$responseCode === '2' || (string)$responseCode === '3');
        $message = $sanitizedData['x_response_reason_text'] ?? 'Transaction processed.';

        return [
            'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
            'message' => 'AuthorizeNetGateway: ' . $message,
            'transactionId' => $transactionId,
            'orderId' => $orderId,
            'paymentStatus' => $isSuccess ? 'approved' : ($isFailed ? 'declined' : 'unknown'),
            'amount' => $amount,
            'rawData' => $sanitizedData
        ];
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) {
            throw new VerificationException('AuthorizeNetGateway: Missing transactionId for verification.');
        }
        $transactionId = $sanitizedData['transactionId'];

        try {
            $payload = ['transId' => $transactionId];
            if ($transactionId === 'fail_verify_ref') { // Simulate a condition for this mock
                // This would normally be based on the actual API response
                 $response = ['body' => [
                    'transaction' => ['transId' => $transactionId, 'transactionStatus' => 'declined', 'responseCode' => '2'],
                    'messages' => ['resultCode' => 'Ok', 'message' => [['code' => 'I00001', 'text' => 'Transaction details retrieved.']]]
                 ], 'status_code' => 200];
            } elseif ($transactionId === 'pending_verify_ref') {
                 $response = ['body' => [
                    'transaction' => ['transId' => $transactionId, 'transactionStatus' => 'fdsPendingReview', 'responseCode' => '4'],
                    'messages' => ['resultCode' => 'Ok', 'message' => [['code' => 'I00001', 'text' => 'Transaction details retrieved.']]]
                 ], 'status_code' => 200];
            } else {
                $response = $this->sendRequest('getTransactionDetailsRequest', $payload);
            }

            $messages = $response['body']['messages'] ?? [];
            if (($messages['resultCode'] ?? 'Error') !== 'Ok' || empty($response['body']['transaction'])) {
                $errorText = $messages['message'][0]['text'] ?? 'Failed to get transaction details.';
                throw new VerificationException('AuthorizeNetGateway: ' . $errorText);
            }

            $transactionDetails = $response['body']['transaction'];
            $paymentStatus = $transactionDetails['transactionStatus'] ?? 'unknown';
            // Common statuses: authorizedPendingCapture, capturedPendingSettlement, settledSuccessfully, declined, voided, refunded, fdsPendingReview etc.
            $isSuccess = in_array($paymentStatus, ['settledSuccessfully', 'capturedPendingSettlement']);
            $isPending = in_array($paymentStatus, ['authorizedPendingCapture', 'fdsPendingReview', 'underReview']);

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'AuthorizeNetGateway verification result: ' . $paymentStatus,
                'transactionId' => $transactionDetails['transId'],
                'orderId' => $transactionDetails['order']['invoiceNumber'] ?? $transactionDetails['refId'] ?? null,
                'paymentStatus' => $paymentStatus,
                'rawData' => $transactionDetails
            ];
        } catch (\Exception $e) {
            throw new VerificationException('AuthorizeNetGateway: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Original transaction ID to refund
            throw new RefundException('AuthorizeNetGateway: Missing transactionId for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('AuthorizeNetGateway: Invalid or missing amount for refund.');
        }
        if (empty($sanitizedData['orderId'])) { // Your internal order ID, for reference
            throw new RefundException('AuthorizeNetGateway: Missing orderId for refund.');
        }
        // Authorize.Net requires the last 4 digits of the credit card for refunds unless it's a linked refund.
        // For this mock, we'll assume it's a linked refund (refTransId provided).
        // if (empty($sanitizedData['creditCardLastFour'])) {
        //     throw new RefundException('AuthorizeNetGateway: Missing creditCardLastFour for refund.');
        // }

        $payload = [
            'refId' => $sanitizedData['orderId'] . '_refund_' . uniqid(),
            'transactionRequest' => [
                'transactionType' => 'refundTransaction',
                'amount' => sprintf('%.2f', $sanitizedData['amount']),
                'payment' => [
                    'creditCard' => [
                        // 'cardNumber' => 'XXXX' . $sanitizedData['creditCardLastFour'],
                        // 'expirationDate' => 'XXXX', // Not always needed for refunds if refTransId is used
                    ]
                ],
                'refTransId' => $sanitizedData['transactionId'], // The original transaction ID
                'order' => [
                    'invoiceNumber' => $sanitizedData['orderId'],
                    'description' => $sanitizedData['reason'] ?? 'Refund for order ' . $sanitizedData['orderId'],
                ]
            ]
        ];

        try {
             // Simulate an error for a specific amount
            if ($sanitizedData['amount'] == '777.77') {
                $response = ['body' => [
                    'transactionResponse' => ['responseCode' => '3', 'errors' => [['errorCode' => '54', 'errorText' => 'The referenced transaction does not meet the criteria for issuing a credit.']]],
                    'messages' => ['resultCode' => 'Error', 'message' => [['code' => 'E00027', 'text' => 'The transaction was unsuccessful.']]]
                ], 'status_code' => 200];
            } else {
                $response = $this->sendRequest('createTransactionRequest', $payload);
            }

            $transactionResponse = $response['body']['transactionResponse'] ?? null;
            $messages = $response['body']['messages'] ?? [];

            if (($messages['resultCode'] ?? 'Error') !== 'Ok' || ($transactionResponse['responseCode'] ?? '3') !== '1') {
                $errorText = $transactionResponse['errors'][0]['errorText'] ?? ($transactionResponse['messages'][0]['description'] ?? ($messages['message'][0]['text'] ?? 'Refund failed.'));
                throw new RefundException('AuthorizeNetGateway: ' . $errorText);
            }

            return [
                'status' => 'success',
                'message' => 'AuthorizeNetGateway refund processed successfully.',
                'refundId' => $transactionResponse['transId'],
                'transactionId' => $sanitizedData['transactionId'], // Original transaction ID
                'paymentStatus' => 'refunded',
                'rawData' => $transactionResponse
            ];
        } catch (\Exception $e) {
            throw new RefundException('AuthorizeNetGateway: Refund request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
