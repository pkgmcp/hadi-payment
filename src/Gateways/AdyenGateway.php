<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class AdyenGateway extends PaymentGateway
{
    // Adyen uses dynamic URLs based on environment and specific service
    // For Checkout API: [random]-[company_name]-checkout-[environment].adyenpayments.com/checkout/[version]
    // For Classic API: [random]-[company_name]-[live|test].adyenpayments.com/pal/servlet/Payment/[version]
    // This mock will use a generic placeholder.
    private const API_CHECKOUT_BASE_URL_SANDBOX = 'https://checkout-test.adyen.com/v69'; // Example for Checkout API v69
    private const API_CHECKOUT_BASE_URL_PRODUCTION_TEMPLATE = 'https://{PREFIX}-checkout-live.adyenpayments.com/checkout/v69';
    // For Classic API (Refunds, Modifications)
    private const API_PAL_BASE_URL_SANDBOX = 'https://pal-test.adyen.com/pal/servlet/Payment/v64'; // Example for v64
    private const API_PAL_BASE_URL_PRODUCTION_TEMPLATE = 'https://{PREFIX}-pal-live.adyenpayments.com/pal/servlet/Payment/v64';

    private const API_BASE_URL_TEST = 'https://checkout-test.adyen.com/v69';

    protected function getDefaultConfig(): array
    {
        return [
            'merchantAccount' => '', // Your Adyen Merchant Account
            'apiKey' => '',          // API Key for Checkout API or Basic Auth for Classic API
            'clientKey' => '',       // Client Key for frontend components (optional for this mock)
            'liveEndpointPrefix' => '', // Your unique live URL prefix (e.g., 1234567890abcdef-MyCompany)
            'isSandbox' => true,
            'hmacKey' => '',         // For webhook notification verification
            'timeout' => 60,
            'environment' => 'test',
            'returnUrl' => 'https://example.com/adyen/return',
        ];
    }

    protected function validateConfig(array $config): void
    {
        foreach (['merchantAccount', 'apiKey'] as $key) {
            if (empty($config[$key])) {
                throw new InvalidConfigurationException("Adyen: {$key} is required.");
            }
        }
        if (!$config['isSandbox'] && empty($config['liveEndpointPrefix'])) {
            throw new InvalidConfigurationException("Adyen: liveEndpointPrefix is required for production environment.");
        }
        if (strtolower($config['environment']) === 'live' && empty($config['liveEndpointPrefix'])) {
            throw new InvalidConfigurationException('AdyenGateway: liveEndpointPrefix is required for live environment.');
        }
        if (empty($config['hmacKey'])) {
            throw new InvalidConfigurationException('AdyenGateway: hmacKey for webhook verification is required.');
        }
    }

    private function getCheckoutApiBaseUrl(): string
    {
        if ($this->config['isSandbox']) {
            return self::API_CHECKOUT_BASE_URL_SANDBOX;
        }
        return str_replace('{PREFIX}', $this->config['liveEndpointPrefix'], self::API_CHECKOUT_BASE_URL_PRODUCTION_TEMPLATE);
    }

    private function getPalApiBaseUrl(): string
    {
        if ($this->config['isSandbox']) {
            return self::API_PAL_BASE_URL_SANDBOX;
        }
        return str_replace('{PREFIX}', $this->config['liveEndpointPrefix'], self::API_PAL_BASE_URL_PRODUCTION_TEMPLATE);
    }

    private function getApiBaseUrl(): string
    {
        if (strtolower($this->config['environment']) === 'live') {
            if (empty($this->config['liveEndpointPrefix'])) {
                 throw new InvalidConfigurationException('AdyenGateway: liveEndpointPrefix missing for live environment.');
            }
            return 'https://' . $this->config['liveEndpointPrefix'] . '-checkout-live.adyenpayments.com/v69';
        }
        return self::API_BASE_URL_TEST;
    }

    private function getRequestHeaders(?string $idempotencyKey = null): array
    {
        $headers = [
            'X-API-Key' => $this->config['apiKey'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        if ($idempotencyKey) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }
        return $headers;
    }

    // Placeholder for Adyen webhook signature verification
    protected function verifyWebhookSignature(string $payload, string $signature): bool
    {
        // $calculatedSignature = base64_encode(hash_hmac('sha256', $payload, hex2bin($this->config['hmacKey']), true));
        // return hash_equals($calculatedSignature, $signature);
        if ($signature === 'FAIL_ADYEN_SIGNATURE') {
            return false;
        }
        return true;
    }

    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('Adyen: Invalid or missing amount. Amount in minor units (e.g., cents).');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('Adyen: Missing currency code.');
        }
        if (empty($sanitizedData['orderId'])) { // merchantReference
            throw new InitializationException('Adyen: Missing orderId (merchantReference).');
        }
        if (empty($sanitizedData['paymentMethod'])) {
            throw new InitializationException('Adyen: Missing paymentMethod.');
        }

        $idempotencyKey = $sanitizedData['orderId'] . '-' . uniqid('init_', true);
        $payload = [
            'merchantAccount' => $this->config['merchantAccount'],
            'amount' => [
                'currency' => strtoupper($sanitizedData['currency']),
                'value' => (int) $sanitizedData['amount'], // Adyen expects amount in minor units
            ],
            'reference' => $sanitizedData['orderId'], // Your unique transaction reference
            'paymentMethod' => $sanitizedData['paymentMethod'],
            'returnUrl' => $sanitizedData['returnUrl'] ?? $this->config['returnUrl'],
            'channel' => 'Web', // Default channel
        ];

        try {
            // $response = $this->httpClient('POST', $this->getCheckoutApiBaseUrl() . '/payments', $payload, $this->getRequestHeaders());
            // Mocked Response
            if ($payload['amount']['value'] == 99900) { // 999.00 in currency
                 throw new InitializationException('Adyen: API rejected payment (simulated).');
            }

            $mockPspReference = 'ADYENPSP' . strtoupper(uniqid());
            $resultCode = 'RedirectShopper'; // Could be Authorised, Refused, Error, ChallengeShopper etc.
            $action = null;
            if ($resultCode === 'RedirectShopper') {
                $action = [
                    'type' => 'redirect',
                    'paymentMethodType' => 'scheme',
                    'url' => 'https://checkout-test.adyen.com/redirect?token=MOCK_ADYEN_TOKEN_' . uniqid(),
                    'method' => 'GET'
                ];
            }

            $responseBody = [
                'resultCode' => $resultCode,
                'pspReference' => $mockPspReference,
                'merchantReference' => $payload['reference'],
            ];
            if ($action) {
                $responseBody['action'] = $action;
            }

            $response = ['body' => $responseBody, 'status_code' => 200];

            if ($response['status_code'] !== 200 || empty($response['body']['pspReference'])) {
                throw new InitializationException('Adyen: Failed to initialize payment. API Error: ' . ($response['body']['refusalReason'] ?? 'Unknown error'));
            }

            $status = 'pending_user_action';
            $message = 'Adyen payment initiated.';
            $paymentUrl = null;

            if (!empty($response['body']['action']) && $response['body']['action']['type'] === 'redirect') {
                $message .= ' Redirect user.';
                $paymentUrl = $response['body']['action']['url'];
            } elseif ($response['body']['resultCode'] === 'Authorised') {
                $status = 'success'; // Or 'authorized' if capture is separate
                 $message = 'Adyen payment authorized.';
            } else {
                 // Could be requires_client_action (for 3DS2 challenge) or other states
                 $message .= ' Additional action may be required or check status.';
            }


            return [
                'status' => $status,
                'message' => $message,
                'gatewayReferenceId' => $response['body']['pspReference'], // PSP Reference
                'orderId' => $response['body']['merchantReference'],
                'paymentUrl' => $paymentUrl, // If redirect
                'action' => $response['body']['action'] ?? null, // For client-side handling (e.g. 3DS2 challenge)
                'resultCode' => $response['body']['resultCode'],
                'rawData' => $response['body']
            ];
        } catch (\Exception $e) {
            throw new InitializationException('Adyen: Payment initialization failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function process(array $data): array
    {
        // Process for Adyen:
        // 1. Handling /payments/details call after redirect or challenge.
        // 2. Handling webhook notifications.
        // This mock simulates handling a webhook notification.
        $sanitizedData = $this->sanitize($data); // Assuming $data is the decoded webhook notification item

        if (empty($sanitizedData['notificationItems']) || !is_array($sanitizedData['notificationItems'])) {
            throw new ProcessingException('Adyen: Invalid webhook format, missing notificationItems.');
        }
        $notificationWrapper = $sanitizedData['notificationItems'][0] ?? null;
        if (!$notificationWrapper || empty($notificationWrapper['NotificationRequestItem'])) {
            throw new ProcessingException('Adyen: Invalid notification item structure.');
        }
        $notification = $notificationWrapper['NotificationRequestItem'];

        // Mocked HMAC check - in reality, get $hmacSignatureFromHeader from actual HTTP headers.
        // $hmacSignatureFromHeader = $data['mockHmacSignature'] ?? null;
        // if (!$hmacSignatureFromHeader || !$this->isValidHmac($notification, $hmacSignatureFromHeader)) {
        //    throw new ProcessingException('Adyen: Webhook HMAC signature verification failed.');
        // }

        $pspReference = $notification['pspReference'] ?? null;
        $merchantReference = $notification['merchantReference'] ?? null;
        $eventCode = $notification['eventCode'] ?? 'UNKNOWN';
        $success = (isset($notification['success']) && strtolower($notification['success']) === 'true');
        $reason = $notification['reason'] ?? '';

        if (!$pspReference) {
            throw new ProcessingException('Adyen: Missing pspReference in notification.');
        }

        $status = 'pending';
        $message = 'Webhook: ' . $eventCode . ', Success: ' . ($success ? 'Yes' : 'No') . ($reason ? ', Reason: ' . $reason : '');

        if ($eventCode === 'AUTHORISATION') {
            $status = $success ? 'success' : 'failed';
        } elseif ($eventCode === 'REFUND') {
            $status = $success ? 'refund_processed' : 'refund_failed';
        } elseif ($eventCode === 'CAPTURE') {
            $status = $success ? 'success' : 'capture_failed';
        } elseif (in_array($eventCode, ['CANCELLATION', 'CAPTURE_FAILED', 'REFUND_FAILED'])) {
            $status = 'failed';
        }

        return ['status' => $status, 'message' => $message, 'transactionId' => $pspReference, 'orderId' => $merchantReference, 'paymentStatus' => $eventCode, 'rawData' => $notification];
    }

    public function verify(array $data): array
    {
        // Adyen doesn't have a direct "verify" or "status" API endpoint like some gateways.
        // Status is typically confirmed via webhooks.
        // For an explicit check, one might re-use parts of process logic or if a specific API for audit/reconciliation exists.
        // This mock will assume that verify means checking data that was previously stored from a webhook.
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['gatewayReferenceId'])) { // PSP Reference
            throw new VerificationException('Adyen: Missing gatewayReferenceId (PSP Reference) for verification.');
        }

        // This is a conceptual mock; real verification involves checking your DB state updated by webhooks.
        // Or using Adyen's Reporting/Reconciliation APIs which are more complex.
        // Let's simulate a "lookup" that returns a previously known status.

        $mockResultCode = 'Authorised';
        $mockPspRef = $sanitizedData['gatewayReferenceId'];
        $mockSuccess = true;

        if ($mockPspRef === 'ADYEN_PSP_MOCK_FAIL') {
            $mockResultCode = 'Refused';
            $mockSuccess = false;
        } elseif ($mockPspRef === 'ADYEN_PSP_MOCK_PEND') {
            $mockResultCode = 'Pending';
            $mockSuccess = true; // Pending is still a 'successful' API call
        }

        $responseBody = [
            'pspReference' => $mockPspRef,
            'merchantReference' => $sanitizedData['orderId'] ?? 'ORD_MOCK_' . uniqid(),
            'paymentMethod' => ['brand' => 'visa', 'type' => 'scheme'],
            'amount' => ['currency' => 'USD', 'value' => ($sanitizedData['original_amount_for_test'] ?? 100) * 100],
            'eventCode' => $mockResultCode,
            'success' => $mockSuccess,
            'reason' => $mockSuccess ? '' : 'Refused by bank'
        ];

        $isTransactionSuccess = $responseBody['eventCode'] === 'AUTHORISATION' && $responseBody['success'];
        $isTransactionPending = $responseBody['eventCode'] === 'Pending';

        return [
            'status' => $isTransactionSuccess ? 'success' : ($isTransactionPending ? 'pending' : 'failed'),
            'message' => 'Adyen (simulated) verification. Event: ' . $responseBody['eventCode'] . '. Success: ' . ($responseBody['success'] ? 'true' : 'false'),
            'transactionId' => $responseBody['pspReference'],
            'orderId' => $responseBody['merchantReference'],
            'paymentStatus' => $responseBody['eventCode'],
            'rawData' => $responseBody
        ];
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Original PSP Reference of the payment to refund
            throw new RefundException('Adyen: Missing transactionId (original PSP Reference) for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('Adyen: Invalid or missing amount for refund. Amount in minor units.');
        }

        $originalPspReference = $sanitizedData['transactionId'];
        $idempotencyKey = $sanitizedData['orderId'] . '-refund-' . uniqid('', true);
        $payload = [
            'merchantAccount' => $this->config['merchantAccount'],
            'amount' => ['currency' => strtoupper($sanitizedData['currency'] ?? 'USD'), 'value' => (int)$sanitizedData['amount']],
            'reference' => $sanitizedData['orderId'],
        ];

        $mockStatusResponse = '[refund-received]';
        $refundPspReference = 'ADYEN_REFUND_' . strtoupper(uniqid('', true));
        if ($sanitizedData['amount'] == 99902) {
            $mockStatusResponse = '[refund-failed]';
        }

        $apiResponse = ['pspReference' => $refundPspReference, 'response' => $mockStatusResponse, 'merchantReference' => $payload['reference']];
        $isAccepted = $mockStatusResponse === '[refund-received]';

        return ['status' => $isAccepted ? 'pending' : 'failed', 'message' => 'Refund status: ' . $mockStatusResponse, 'refundId' => $refundPspReference, 'transactionId' => $originalPspReference, 'paymentStatus' => 'refund_pending', 'rawData' => $apiResponse];
    }

    // Adyen webhook HMAC SHA256 verification helper
    private function isValidHmac(array $notificationItemData, string $hmacSignature): bool
    {
        if (empty($this->config['hmacKey'])) {
            return false;
        }
        $dataToSign = [
            $notificationItemData['pspReference'] ?? '',
            $notificationItemData['originalReference'] ?? '',
            $notificationItemData['merchantAccountCode'] ?? '',
            $notificationItemData['merchantReference'] ?? '',
            isset($notificationItemData['amount']['value']) ? (string)$notificationItemData['amount']['value'] : '',
            isset($notificationItemData['amount']['currency']) ? (string)$notificationItemData['amount']['currency'] : '',
            $notificationItemData['eventCode'] ?? '',
            isset($notificationItemData['success']) ? ($notificationItemData['success'] ? 'true' : 'false') : ''
        ];
        $signableString = implode(':', $dataToSign);
        $expectedSignature = base64_encode(hash_hmac('sha256', $signableString, hex2bin($this->config['hmacKey']), true));
        return hash_equals($expectedSignature, $hmacSignature);
    }
}
