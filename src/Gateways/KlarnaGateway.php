<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class KlarnaGateway extends PaymentGateway
{
    // Klarna has different APIs: Klarna Payments, Klarna Checkout, Klarna Order Management.
    // This mock will conceptually represent Klarna Payments.
    private const API_BASE_URL_SANDBOX_EU = 'https://api.playground.klarna.com'; // EU, NA, OC variants
    private const API_BASE_URL_PRODUCTION_EU = 'https://api.klarna.com';

    protected array $config = []; // Added to store merged config if needed
    private array $lastInitializeData = []; // For createKlarnaOrder mock

    protected function getDefaultConfig(): array
    {
        return [
            'username' => '', // Klarna API Username (UID)
            'password' => '', // Klarna API Password
            'region' => 'EU', // EU, NA (North America), OC (Oceania) - affects base URL
            'isSandbox' => true,
            'timeout' => 60,
            'purchaseCountry' => 'SE', // Example: Sweden
            'purchaseCurrency' => 'SEK', // Example: Swedish Krona
            'locale' => 'sv-SE', // Example: Swedish locale
            'merchantUrls' => [
                'confirmation' => 'https://example.com/klarna/confirmation?order_id={checkout.order.id}',
                'notification' => 'https://example.com/klarna/notification?order_id={checkout.order.id}', // Webhook
                // 'push' => 'https://example.com/klarna/push?order_id={checkout.order.id}' // Alternative webhook
            ],
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['username'])) {
            throw new InvalidConfigurationException('KlarnaGateway: username (API UID) is required.');
        }
        if (empty($config['password'])) {
            throw new InvalidConfigurationException('KlarnaGateway: password (API Password) is required.');
        }
        if (empty($config['merchantUrls']['confirmation'])) {
            throw new InvalidConfigurationException('KlarnaGateway: merchantUrls.confirmation is required.');
        }
        if (empty($config['merchantUrls']['notification'])) {
            throw new InvalidConfigurationException('KlarnaGateway: merchantUrls.notification is required.');
        }
    }

    private function getApiBaseUrl(): string
    {
        $region = strtoupper($this->config['region']);
        // Simplified: In reality, Klarna has specific URLs for NA and OC regions.
        // e.g. api-na.playground.klarna.com, api-oc.playground.klarna.com
        $base = $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX_EU : self::API_BASE_URL_PRODUCTION_EU;
        if ($region === 'NA') {
            $base = str_replace('api.', 'api-na.', $base);
        } elseif ($region === 'OC') {
            $base = str_replace('api.', 'api-oc.', $base);
        }
        return $base;
    }

    private function getRequestHeaders(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->config['username'] . ':' . $this->config['password']),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            // 'Klarna-Api-Version' => '1.0' // If specific API versioning is needed
        ];
    }

    public function initialize(array $data): array
    {
        $this->lastInitializeData = $data; // Store original data
        // This typically involves creating a credit session or a Klarna Payments order.
        // The response contains a client_token to be used with Klarna's JS SDK for rendering payment options.
        $sanitizedData = $this->sanitize($data);

        if (empty($sanitizedData['orderAmount']) || !is_numeric($sanitizedData['orderAmount']) || $sanitizedData['orderAmount'] <= 0) {
            throw new InitializationException('KlarnaGateway: Invalid or missing orderAmount (in minor units, e.g. cents).');
        }
        if (empty($sanitizedData['orderLines']) || !is_array($sanitizedData['orderLines'])) {
            throw new InitializationException('KlarnaGateway: Missing or invalid orderLines array.');
        }
        if (empty($sanitizedData['merchantReference1'])) {
            throw new InitializationException('KlarnaGateway: Missing merchantReference1 (your order ID).');
        }

        $payload = [
            'purchase_country' => $this->config['purchaseCountry'],
            'purchase_currency' => strtoupper($this->config['purchaseCurrency']),
            'locale' => $this->config['locale'],
            'order_amount' => (int)$sanitizedData['orderAmount'],
            'order_tax_amount' => (int)($sanitizedData['orderTaxAmount'] ?? 0),
            'order_lines' => $sanitizedData['orderLines'], // Array of line item objects
            'merchant_urls' => $this->config['merchantUrls'],
            'merchant_reference1' => $sanitizedData['merchantReference1'], // Your unique order ID
            // 'billing_address' => [ ... ], // Optional but recommended
            // 'shipping_address' => [ ... ], // Optional if physical goods
        ];

        try {
            // For Klarna Payments, you first create a credit session: POST /payments/v1/sessions
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/payments/v1/sessions', $payload, $this->getRequestHeaders());

            // Mocked response for creating a credit session
            if ($sanitizedData['orderAmount'] == 99999) {
                 throw new InitializationException('KlarnaGateway: Simulated API error creating session (amount 99999).');
            }
            $mockSessionId = 'kl_sess_' . uniqid();
            $mockClientToken = 'mock_client_token_for_klarna_sdk_' . uniqid();
            $mockPaymentMethodCategories = [
                ['identifier' => 'pay_later', 'name' => 'Pay Later'],
                ['identifier' => 'slice_it', 'name' => 'Slice It'],
            ];

            $apiResponse = [
                'session_id' => $mockSessionId,
                'client_token' => $mockClientToken,
                'payment_method_categories' => $mockPaymentMethodCategories
            ];
            $statusCode = 200;

            // if ($response['status_code'] >= 300 || empty($response['body']['client_token'])) {
            //     $errorMsg = $response['body']['error_messages'][0] ?? ($response['body']['error_code'] ?? 'KlarnaGateway: Failed to create credit session.');
            //     throw new InitializationException($errorMsg);
            // }

            return [
                'status' => 'pending_render', // Client token obtained, needs to be rendered client-side
                'message' => 'Klarna credit session created. Use client_token with Klarna JS SDK.',
                'clientToken' => $apiResponse['client_token'],
                'sessionId' => $apiResponse['session_id'],
                'paymentMethodCategories' => $apiResponse['payment_method_categories'],
                'orderId' => $sanitizedData['merchantReference1'],
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new InitializationException('KlarnaGateway: Credit session creation failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function process(array $data): array
    {
        // Processing involves handling the redirect from Klarna to your confirmation URL,
        // which includes an authorization_token. This token is then used to create an order.
        // Or, handling a webhook notification.
        $sanitizedData = $this->sanitize($data);

        // Scenario 1: Handling redirect with authorization_token (after customer completes Klarna steps)
        if (isset($sanitizedData['authorization_token']) && isset($sanitizedData['sessionId'])) {
            return $this->createKlarnaOrder($sanitizedData['sessionId'], $sanitizedData['authorization_token'], $sanitizedData['orderId'] ?? null);
        }

        // Scenario 2: Handling webhook notification from merchant_urls.notification
        // Klarna webhooks should be verified by checking the Klarna-Signature header (HMAC-SHA256 of body + shared secret)
        // IMPORTANT: Implement webhook signature verification in a real scenario.
        $klarnaOrderId = $sanitizedData['order_id'] ?? null;
        $eventType = $sanitizedData['event_type'] ?? null; // e.g., ORDER_CAPTURED, ORDER_CANCELLED, FRAUD_STATUS_ACCEPTED

        if ($klarnaOrderId && $eventType) {
            $status = 'pending'; // Default
            $message = 'Klarna webhook received. Event: ' . $eventType . ', Order ID: ' . $klarnaOrderId;
            $paymentStatus = $eventType; // Use Klarna's event type as paymentStatus for now

            if (in_array($eventType, ['FRAUD_STATUS_ACCEPTED', 'ORDER_AUTHORIZED'])) {
                $status = 'success'; // Or 'authorized' if capture is separate
            } elseif ($eventType === 'ORDER_CAPTURED') {
                $status = 'success';
            } elseif (in_array($eventType, ['FRAUD_STATUS_REJECTED', 'ORDER_CANCELLED'])) {
                $status = 'failed';
            }

            return [
                'status' => $status,
                'message' => $message,
                'transactionId' => $klarnaOrderId, // Klarna Order ID
                'orderId' => $sanitizedData['merchant_reference1'] ?? null, // Your original order ID if available in webhook
                'paymentStatus' => $paymentStatus,
                'eventType' => $eventType,
                'rawData' => $sanitizedData
            ];
        }

        throw new ProcessingException('KlarnaGateway: Invalid data for processing. Missing authorization_token/sessionId or webhook data.');
    }

    // This function would be called after the customer authorizes the payment with Klarna and is redirected back.
    private function createKlarnaOrder(string $sessionId, string $authorizationToken, ?string $merchantOrderId): array
    {
        // Use the authorization_token to create an order: POST /payments/v1/authorizations/{authorizationToken}/order
        // OR for Klarna Checkout: POST /checkout/v3/orders/{klarna_order_id}/authorizations using the order ID from session
        // This mock uses the Klarna Payments flow with /payments/v1/authorizations/{authorizationToken}/order
        // The payload is usually the same as the session creation payload (order_amount, order_lines, etc.)
        // Fetch original session data or assume it's available from context.

        // Mocked data for creating order (would typically re-use or fetch session data)
        $mockOrderData = [
            'purchase_country' => $this->config['purchaseCountry'],
            'purchase_currency' => strtoupper($this->config['purchaseCurrency']),
            'locale' => $this->config['locale'],
            'order_amount' => (int)($this->lastInitializeData['orderAmount'] ?? 10000), // retrieve from where it was stored
            'order_tax_amount' => (int)($this->lastInitializeData['orderTaxAmount'] ?? 0),
            'order_lines' => $this->lastInitializeData['orderLines'] ?? [['name' => 'Mock Item', 'quantity' => 1, 'unit_price' => 10000, 'total_amount' => 10000]],
            'merchant_urls' => $this->config['merchantUrls'],
            'merchant_reference1' => $merchantOrderId ?? ('mref_' . $sessionId),
        ];

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . "/payments/v1/authorizations/{$authorizationToken}/order", $mockOrderData, $this->getRequestHeaders());
            // Mocked response for creating an order
            $klarnaOrderId = 'kl_ord_' . uniqid();
            $fraudStatus = 'ACCEPTED'; // Or PENDING, REJECTED

            if ($authorizationToken === 'auth_token_fraud_pending') {
                $fraudStatus = 'PENDING';
            } elseif ($authorizationToken === 'auth_token_fraud_rejected') {
                $fraudStatus = 'REJECTED';
            }

            $apiResponse = [
                'order_id' => $klarnaOrderId,
                'fraud_status' => $fraudStatus,
                'redirect_url' => $this->config['merchantUrls']['confirmation'] . '?klarna_order_id=' . $klarnaOrderId, // Confirmation URL
                // 'authorized_payment_method' => [ ... ]
            ];
            $statusCode = 200;

            // if ($response['status_code'] >= 300 || empty($response['body']['order_id'])) {
            //      $errorMsg = $response['body']['error_messages'][0] ?? ($response['body']['error_code'] ?? 'KlarnaGateway: Failed to create order.');
            //     throw new ProcessingException($errorMsg);
            // }

            $isSuccess = $apiResponse['fraud_status'] === 'ACCEPTED';
            $isPending = $apiResponse['fraud_status'] === 'PENDING';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending_confirmation' : 'failed'),
                'message' => 'Klarna order created. Fraud status: ' . $apiResponse['fraud_status'],
                'transactionId' => $apiResponse['order_id'], // Klarna Order ID
                'orderId' => $merchantOrderId ?? $mockOrderData['merchant_reference1'], // Your Order ID
                'paymentStatus' => $apiResponse['fraud_status'],
                'confirmationRedirectUrl' => $apiResponse['redirect_url'], // You should redirect the customer here
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new ProcessingException('KlarnaGateway: Order creation with authorization_token failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Klarna Order ID
            throw new VerificationException('KlarnaGateway: Missing transactionId (Klarna Order ID) for verification.');
        }
        $klarnaOrderId = $sanitizedData['transactionId'];

        try {
            // GET /ordermanagement/v1/orders/{klarnaOrderId}
            // $response = $this->httpClient('GET', $this->getApiBaseUrl() . '/ordermanagement/v1/orders/' . $klarnaOrderId, [], $this->getRequestHeaders());
            // Mocked response for fetching order details
            $mockStatus = 'AUTHORIZED'; // AUTHORIZED, CAPTURED, CANCELLED, EXPIRED, COMPLETED
            if ($klarnaOrderId === 'kl_ord_fail_verify') {
                $mockStatus = 'CANCELLED';
            } elseif ($klarnaOrderId === 'kl_ord_pending_verify') {
                $mockStatus = 'PENDING'; // A conceptual pending status for Klarna OM
            }
            $apiResponse = [
                'order_id' => $klarnaOrderId,
                'status' => $mockStatus,
                'order_amount' => ($sanitizedData['original_amount_for_test'] ?? 10000), // minor units
                'purchase_currency' => $this->config['purchaseCurrency'],
                'merchant_reference1' => 'mref_' . uniqid(),
                'fraud_status' => 'ACCEPTED',
            ];
            // if ($response['status_code'] !== 200 || empty($response['body']['order_id'])) {
            //     throw new VerificationException('KlarnaGateway: Failed to verify order. ' . ($response['body']['error_code'] ?? 'API error'));
            // }

            $paymentStatus = $apiResponse['status'] ?? 'UNKNOWN';
            $isSuccess = in_array($paymentStatus, ['AUTHORIZED', 'CAPTURED', 'COMPLETED']);
            $isPending = $paymentStatus === 'PENDING'; // Or other Klarna specific pending statuses

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'Klarna order verification result: ' . $paymentStatus,
                'transactionId' => $apiResponse['order_id'],
                'orderId' => $apiResponse['merchant_reference1'] ?? null,
                'paymentStatus' => $paymentStatus,
                'rawData' => $apiResponse
            ];
        } catch (\Exception $e) {
            throw new VerificationException('KlarnaGateway: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['transactionId'])) { // Klarna Order ID
            throw new RefundException('KlarnaGateway: Missing transactionId (Klarna Order ID) for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('KlarnaGateway: Invalid or missing amount for refund (minor units).');
        }

        $klarnaOrderId = $sanitizedData['transactionId'];
        $payload = [
            'refunded_amount' => (int)$sanitizedData['amount'],
            'description' => $sanitizedData['reason'] ?? 'Merchant requested refund',
            // 'order_lines' => [ ... ] // Optional: if refunding specific lines
        ];

        try {
            // POST /ordermanagement/v1/orders/{klarnaOrderId}/refunds
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/ordermanagement/v1/orders/' . $klarnaOrderId . '/refunds', $payload, $this->getRequestHeaders());
            // Mocked response (Klarna refund API usually returns 201 Created with no body, or a refund_id)
            $mockRefundId = 'kl_ref_' . uniqid();
            $statusCode = 201; // Created
            $responseBody = ['refund_id' => $mockRefundId]; // Some Klarna refund APIs return a refund_id

            if (($sanitizedData['amount'] ?? 0) == 99999) {
                // throw new RefundException('KlarnaGateway: API rejected refund (simulated amount 99999).');
                $statusCode = 400; // Simulate failure
                $responseBody = ['error_code' => 'REFUND_NOT_ALLOWED', 'error_messages' => ['Refund amount too high']];
            }

            // if ($response['status_code'] !== 201 && $response['status_code'] !== 204) { // 204 if no body
            //     $errorMsg = $response['body']['error_messages'][0] ?? ($response['body']['error_code'] ?? 'KlarnaGateway: Failed to process refund.');
            //     throw new RefundException($errorMsg);
            // }

            if ($statusCode >= 300) {
                 throw new RefundException('KlarnaGateway: Failed to process refund. (' . ($responseBody['error_code'] ?? 'Simulated Error') . ')');
            }

            // Refund status is often asynchronous, confirmed via webhook.
            return [
                'status' => 'success', // Assuming direct success, or 'pending' if async and webhook confirms
                'message' => 'Klarna refund initiated successfully.' . (isset($responseBody['refund_id']) ? ' Refund ID: ' . $responseBody['refund_id'] : ''),
                'refundId' => $responseBody['refund_id'] ?? $klarnaOrderId . '_refund', // Klarna might return a specific refund ID
                'transactionId' => $klarnaOrderId,
                'paymentStatus' => 'refund_pending', // Or 'refunded' if confirmed sync
                'rawData' => $responseBody
            ];
        } catch (\Exception $e) {
            throw new RefundException('KlarnaGateway: Refund request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
