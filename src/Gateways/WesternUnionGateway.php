<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class WesternUnionGateway extends PaymentGateway
{
    // Western Union has various APIs: Digital Money Transfer, Agent APIs, etc.
    // This mock will focus on a conceptual digital money transfer scenario.
    private const API_BASE_URL_SANDBOX = 'https://api.sandbox.westernunion.com/v2'; // Hypothetical Sandbox URL
    private const API_BASE_URL_PRODUCTION = 'https://api.westernunion.com/v2'; // Hypothetical Production URL

    protected function getDefaultConfig(): array
    {
        return [
            'apiKey' => '',             // WU API Key
            'apiSecret' => '',          // WU API Secret or OAuth credentials
            'programId' => '',          // Specific program or partner ID if applicable
            'isSandbox' => true,
            'timeout' => 120,           // WU transactions can take longer
            'defaultSenderCountry' => 'US',
            'defaultReceiverCountry' => 'MX',
            'notificationUrl' => 'https://example.com/wu/webhook', // For status updates
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['apiKey'])) {
            throw new InvalidConfigurationException('WesternUnionGateway: apiKey is required.');
        }
        if (empty($config['apiSecret'])) {
            throw new InvalidConfigurationException('WesternUnionGateway: apiSecret is required.');
        }
        // 'programId' might be optional depending on the specific WU API being used.
    }

    private function getApiBaseUrl(): string
    {
        return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX : self::API_BASE_URL_PRODUCTION;
    }

    private function getRequestHeaders(): array
    {
        // This is a common pattern; WU might use OAuth2 Bearer token or other custom headers.
        return [
            'Authorization' => 'Bearer ' . $this->generateMockAuthToken(), // Placeholder
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            // 'X-WU-Program-Id' => $this->config['programId'] // Example custom header
        ];
    }

    private function generateMockAuthToken(): string
    {
        // In a real scenario, this would involve an OAuth2 token exchange or using API key/secret directly.
        return 'mock_wu_auth_token_' . hash('sha256', $this->config['apiKey'] . $this->config['apiSecret']);
    }

    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);

        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('WesternUnionGateway: Invalid or missing amount.');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('WesternUnionGateway: Missing currency.');
        }
        if (empty($sanitizedData['receiverFirstName']) || empty($sanitizedData['receiverLastName'])) {
            throw new InitializationException('WesternUnionGateway: Receiver first and last name are required.');
        }
        if (empty($sanitizedData['receiverCountry'])) {
            throw new InitializationException('WesternUnionGateway: Receiver country is required.');
        }
        if (empty($sanitizedData['orderId'])) { // Your internal reference
            throw new InitializationException('WesternUnionGateway: Missing orderId.');
        }

        // Payload for initiating a money transfer quote or actual transfer
        $payload = [
            'senderDetails' => [
                'country' => $sanitizedData['senderCountry'] ?? $this->config['defaultSenderCountry'],
                'firstName' => $sanitizedData['senderFirstName'] ?? 'John',
                'lastName' => $sanitizedData['senderLastName'] ?? 'Doe',
                'email' => $sanitizedData['senderEmail'] ?? 'sender@example.com',
            ],
            'receiverDetails' => [
                'country' => $sanitizedData['receiverCountry'],
                'firstName' => $sanitizedData['receiverFirstName'],
                'lastName' => $sanitizedData['receiverLastName'],
                'phoneNumber' => $sanitizedData['receiverPhone'] ?? null,
            ],
            'paymentDetails' => [
                'amount' => (float)$sanitizedData['amount'],
                'currency' => strtoupper($sanitizedData['currency']),
                'sourceOfFunds' => $sanitizedData['sourceOfFunds'] ?? 'Bank Account', // e.g., Bank Account, Card, Cash
                'paymentType' => $sanitizedData['paymentType'] ?? 'SEND', // SEND, STAGE_FOR_PICKUP
            ],
            'transactionDetails' => [
                'purposeOfTransaction' => $sanitizedData['purposeOfTransaction'] ?? 'Family Support',
                'merchantReferenceId' => $sanitizedData['orderId'],
                'notificationUrl' => $this->config['notificationUrl'],
            ]
        ];

        try {
            // Endpoint: /money-transfers/initiate  OR /quotes
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/money-transfers', $payload, $this->getRequestHeaders());

            // Mocked response for initiating a transfer
            if (($sanitizedData['amount'] ?? 0) == 9999.99) {
                throw new InitializationException('WesternUnionGateway: Simulated API error - amount invalid.');
            }
            $mockMtcn = 'WU' . rand(1000000000, 9999999999); // Money Transfer Control Number
            $mockStatus = 'PENDING_CUSTOMER_ACTION'; // e.g. Needs funding, Awaiting ID Verification, etc.

            $apiResponse = [
                'mtcn' => $mockMtcn,
                'status' => $mockStatus,
                'estimatedDelivery' => '1-2 business days',
                'fees' => 5.00,
                'exchangeRate' => 1.0,
                'amountToSend' => $payload['paymentDetails']['amount'],
                'amountToReceive' => $payload['paymentDetails']['amount'] - 5.00, // Simplified
                'currency' => $payload['paymentDetails']['currency'],
                'nextSteps' => 'Customer needs to fund the transfer via provided payment options or visit an agent.',
                'redirectUrl' => $sanitizedData['fundingRedirectUrl'] ?? 'https://wu.example.com/fund/' . $mockMtcn, // If WU provides a page
            ];

            // if ($response['status_code'] >= 300 || empty($response['body']['mtcn'])) {
            //     $errorMsg = $response['body']['errors'][0]['message'] ?? 'Failed to initialize WU transfer.';
            //     throw new InitializationException('WesternUnionGateway: ' . $errorMsg);
            // }

            return [
                'status' => 'pending_funding', // Or 'pending_user_action'
                'message' => 'Western Union transfer initiated. MTCN: ' . $apiResponse['mtcn'] . '. Status: ' . $apiResponse['status'],
                'transactionId' => $apiResponse['mtcn'], // MTCN is the key identifier
                'orderId' => $sanitizedData['orderId'],
                'gatewaySpecificData' => [
                    'estimatedDelivery' => $apiResponse['estimatedDelivery'],
                    'fees' => $apiResponse['fees'],
                    'exchangeRate' => $apiResponse['exchangeRate'],
                    'amountToReceive' => $apiResponse['amountToReceive'],
                    'nextSteps' => $apiResponse['nextSteps'],
                ],
                'redirectUrl' => $apiResponse['redirectUrl'], // If applicable
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new InitializationException('WesternUnionGateway: Transfer initialization failed. ' . $e->getMessage(), 0, $e);
        }
    }


    public function process(array $data): array
    {
        // Process would typically handle webhook notifications from WU about transfer status changes.
        $sanitizedData = $this->sanitize($data); // Assuming $data is the decoded webhook payload

        if (empty($sanitizedData['mtcn'])) {
            throw new ProcessingException('WesternUnionGateway: MTCN missing in notification payload.');
        }
        if (empty($sanitizedData['statusEvent'])) { // e.g., PAID_OUT, CANCELLED, ON_HOLD, AVAILABLE_FOR_PICKUP
            throw new ProcessingException('WesternUnionGateway: Status event missing in notification payload.');
        }

        // Mock: WU might require signature verification for webhooks
        // $this->verifyWebhookSignature($data);

        $mtcn = $sanitizedData['mtcn'];
        $statusEvent = $sanitizedData['statusEvent'];
        $message = 'WU Webhook: MTCN ' . $mtcn . ', Event: ' . $statusEvent;
        $currentStatus = 'pending';

        switch (strtoupper($statusEvent)) {
            case 'AVAILABLE_FOR_PICKUP':
            case 'FUNDS_AVAILABLE':
                $currentStatus = 'awaiting_pickup';
                $message .= '. Funds are ready for the receiver.';
                break;
            case 'PAID_OUT':
            case 'DELIVERED_TO_BANK':
                $currentStatus = 'success';
                $message .= '. Transfer completed successfully.';
                break;
            case 'CANCELLED':
            case 'REFUNDED':
                $currentStatus = 'failed'; // Or 'cancelled' / 'refunded'
                $message .= '. Transfer ' . strtolower($statusEvent) . '.';
                break;
            case 'ON_HOLD':
            case 'NEEDS_ATTENTION':
                $currentStatus = 'pending_action';
                $message .= '. Transfer requires attention.';
                break;
            default:
                $message .= '. Status update received.';
        }

        return [
            'status' => $currentStatus,
            'message' => $message,
            'transactionId' => $mtcn,
            'orderId' => $sanitizedData['merchantReferenceId'] ?? null, // If provided in webhook
            'paymentStatus' => $statusEvent,
            'rawData' => $sanitizedData
        ];
    }


    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // MTCN
            throw new VerificationException('WesternUnionGateway: Missing transactionId (MTCN) for verification.');
        }
        $mtcn = $sanitizedData['transactionId'];

        try {
            // Endpoint: /money-transfers/{mtcn}/status
            // $response = $this->httpClient('GET', $this->getApiBaseUrl() . '/money-transfers/' . $mtcn . '/status', [], $this->getRequestHeaders());

            // Mocked status response
            $mockStatus = 'AVAILABLE_FOR_PICKUP';
            $receiverName = "Jane Receiver";
            if ($mtcn === 'WU_FAIL_VERIFY') {
                $mockStatus = 'CANCELLED';
            } elseif ($mtcn === 'WU_PAID_VERIFY') {
                $mockStatus = 'PAID_OUT';
            }

            $apiResponse = [
                'mtcn' => $mtcn,
                'status' => $mockStatus,
                'senderName' => 'John Doe',
                'receiverName' => $receiverName,
                'amountSent' => ($sanitizedData['originalAmountForTest'] ?? 100.00),
                'currency' => 'USD',
                'dateInitiated' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'estimatedPayoutDate' => date('Y-m-d', strtotime('+1 day')),
            ];
             // if ($response['status_code'] !== 200 || empty($response['body']['status'])) {
            //     throw new VerificationException('WesternUnionGateway: Failed to verify transfer status. ' . ($response['body']['errors'][0]['message'] ?? 'API Error'));
            // }

            $paymentStatus = $apiResponse['status'] ?? 'UNKNOWN';
            $isSuccess = in_array($paymentStatus, ['PAID_OUT', 'DELIVERED_TO_BANK']);
            $isPending = in_array($paymentStatus, ['PENDING_FUNDING', 'IN_PROGRESS', 'AVAILABLE_FOR_PICKUP', 'ON_HOLD']);
            $isFailed = in_array($paymentStatus, ['CANCELLED', 'REFUSED', 'EXPIRED']);

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : ($isFailed ? 'failed' : 'unknown')),
                'message' => 'Western Union transfer status for MTCN ' . $mtcn . ': ' . $paymentStatus,
                'transactionId' => $apiResponse['mtcn'],
                'orderId' => $sanitizedData['orderId'] ?? null, // If you mapped it initially
                'paymentStatus' => $paymentStatus,
                'gatewaySpecificData' => [
                    'receiverName' => $apiResponse['receiverName'],
                     'estimatedPayoutDate' => $apiResponse['estimatedPayoutDate'],
                ],
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new VerificationException('WesternUnionGateway: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        // Refunding a WU transfer is typically a cancellation process if not yet paid out,
        // or a more complex refund request if it has been paid.
        // This mock assumes cancellation of a pending transfer.
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // MTCN
            throw new RefundException('WesternUnionGateway: Missing transactionId (MTCN) for refund/cancellation.');
        }
        if (empty($sanitizedData['reason'])) {
            throw new RefundException('WesternUnionGateway: Missing reason for refund/cancellation.');
        }
        $mtcn = $sanitizedData['transactionId'];
        $payload = [
            'reasonCode' => $sanitizedData['reasonCode'] ?? '01', // Example: 01 = Sender Request
            'reasonDescription' => $sanitizedData['reason'],
            'merchantReferenceId' => $sanitizedData['orderId'] ?? null,
        ];

        try {
            // Endpoint: /money-transfers/{mtcn}/cancel or /refunds
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/money-transfers/' . $mtcn . '/cancel', $payload, $this->getRequestHeaders());

            // Mocked refund/cancellation response
            $mockStatus = 'CANCELLED_PENDING_REFUND'; // Or 'REFUND_INITIATED'
            if ($mtcn === 'WU_CANT_CANCEL') {
                // throw new RefundException('WesternUnionGateway: Transfer cannot be cancelled (already paid or other reason).');
                 $mockStatus = 'CANCELLATION_REJECTED';
            }

            $apiResponse = [
                'mtcn' => $mtcn,
                'status' => $mockStatus, // e.g. CANCELLED_PENDING_REFUND, REFUND_INITIATED, CANCELLATION_REJECTED
                'message' => $mockStatus !== 'CANCELLATION_REJECTED' ? 'Cancellation request processed.' : 'Cancellation rejected.',
                'refundDetails' => $mockStatus !== 'CANCELLATION_REJECTED' ? 'Refund will be processed to original funding source.' : null
            ];
            // if ($response['status_code'] >= 300 || empty($response['body']['status']) || !in_array($response['body']['status'], ['CANCELLED', 'REFUND_INITIATED'])) {
            //    $errorMsg = $response['body']['errors'][0]['message'] ?? 'Failed to process WU refund/cancellation.';
            //    throw new RefundException('WesternUnionGateway: ' . $errorMsg);
            // }

            if ($mockStatus === 'CANCELLATION_REJECTED') {
                 throw new RefundException('WesternUnionGateway: Transfer cannot be cancelled (simulated). Status: ' . $mockStatus);
            }

            return [
                'status' => 'pending', // Refund is usually async
                'message' => 'Western Union cancellation/refund for MTCN ' . $mtcn . ' initiated. Status: ' . $apiResponse['status'],
                'refundId' => $mtcn . '_REFUND_' . uniqid(), // No specific refund ID usually, use MTCN
                'transactionId' => $mtcn,
                'paymentStatus' => $apiResponse['status'],
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new RefundException('WesternUnionGateway: Refund/cancellation request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
