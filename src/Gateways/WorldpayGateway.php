<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\ProcessingException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Exceptions\RefundException;

class WorldpayGateway extends PaymentGateway
{
    // Note: Worldpay has various integration methods and API versions.
    // This is a conceptual mock for their JSON-based direct API (Access Worldpay).
    private const API_BASE_URL_SANDBOX = 'https://try.access.worldpay.com';
    private const API_BASE_URL_PRODUCTION = 'https://access.worldpay.com';

    protected function getDefaultConfig(): array
    {
        return [
            'serviceKey' => '', // Format: T_S_xxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxx (for test)
            'clientKey' => '',  // Format: T_C_xxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxx (for test)
            'isSandbox' => true,
            'timeout' => 60,
            'webhookSecret' => '', // For verifying webhook authenticity
        ];
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['serviceKey'])) {
            throw new InvalidConfigurationException('WorldpayGateway: serviceKey is required.');
        }
        // Client key might be used for specific client-side tokenization, service key for server-to-server.
        // if (empty($config['clientKey'])) {
        //     throw new InvalidConfigurationException('WorldpayGateway: clientKey is required.');
        // }
    }

    private function getApiBaseUrl(): string
    {
        return $this->config['isSandbox'] ? self::API_BASE_URL_SANDBOX : self::API_BASE_URL_PRODUCTION;
    }

    private function getRequestHeaders(): array
    {
        return [
            'Authorization' => $this->config['serviceKey'], // Service Key acts as the auth token for server-side calls
            'Content-Type' => 'application/vnd.worldpay.payments-v6+json', // Example content type
            'Accept' => 'application/vnd.worldpay.payments-v6+json',
        ];
    }

    public function initialize(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new InitializationException('WorldpayGateway: Invalid or missing amount (minor units, e.g., pence/cents).');
        }
        if (empty($sanitizedData['currency'])) {
            throw new InitializationException('WorldpayGateway: Missing currency.');
        }
        if (empty($sanitizedData['orderId'])) {
            throw new InitializationException('WorldpayGateway: Missing orderId.');
        }
        if (empty($sanitizedData['paymentToken'])) { // This would be a token from Worldpay's client-side SDK or a saved token
            throw new InitializationException('WorldpayGateway: Missing paymentToken.');
        }

        $payload = [
            'transactionReference' => $sanitizedData['orderId'],
            'instruction' => [
                'value' => [
                    'amount' => (int) $sanitizedData['amount'], // Amount in minor units
                    'currency' => strtoupper($sanitizedData['currency']),
                ],
                'paymentInstrument' => [
                    'type' => 'card/token',
                    'token' => $sanitizedData['paymentToken'], // e.g., 'TOKEN_EVENT_1234567890'
                    'billingAddress' => $sanitizedData['billingAddress'] ?? null, // Optional, but recommended
                ],
                // 'narrative' => ['line1' => $sanitizedData['description'] ?? 'Payment'],
            ]
        ];

        try {
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/payments', $payload, $this->getRequestHeaders());
            // Mocked response
            if ($sanitizedData['amount'] == 99999) {
                 throw new InitializationException('WorldpayGateway: Simulated API error during payment creation.');
            }
            $mockGatewayReferenceId = 'wp_pay_' . uniqid();
            $mockOutcome = 'authorised';

            if ($sanitizedData['paymentToken'] === 'token_declined') {
                $mockOutcome = 'refused';
            }

            $response = ['body' => [
                    'outcome' => $mockOutcome, // e.g. authorised, refused, error
                    '_links' => [
                        'payments:event' => ['href' => $this->getApiBaseUrl() . '/events/' . $mockGatewayReferenceId]
                    ],
                    'instruction' => ['value' => $payload['instruction']['value']],
                    'transactionReference' => $payload['transactionReference'],
                ],
                'status_code' => $mockOutcome === 'authorised' ? 201 : 200 // 201 for successful creation
            ];

            if ($response['status_code'] < 200 || $response['status_code'] >= 300 || empty($response['body']['_links']['payments:event']['href'])) {
                 $errorMsg = $response['body']['description'] ?? ('WorldpayGateway: Failed to process payment. Outcome: ' . ($response['body']['outcome'] ?? 'unknown'));
                throw new InitializationException($errorMsg);
            }

            $isSuccess = strtolower($response['body']['outcome']) === 'authorised';
            $gatewayRef = basename($response['body']['_links']['payments:event']['href']);

            return [
                'status' => $isSuccess ? 'success' : 'failed',
                'message' => 'Worldpay payment outcome: ' . $response['body']['outcome'],
                'gatewayReferenceId' => $gatewayRef, // This is often an event ID or payment ID
                'orderId' => $response['body']['transactionReference'] ?? $sanitizedData['orderId'],
                'paymentStatus' => $response['body']['outcome'],
                'rawData' => $response['body']
            ];
        } catch (\Exception $e) {
            throw new InitializationException('WorldpayGateway: Payment creation failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function process(array $data): array
    {
        // Worldpay uses webhooks for asynchronous updates. Signature verification is crucial.
        $sanitizedData = $this->sanitize($data); // Data from webhook

        // Simplified: In reality, verify $this->config['webhookSecret'] against a signature header.
        if (empty($sanitizedData['eventId']) || empty($sanitizedData['type']) || empty($sanitizedData['resource']['id'])) {
            throw new ProcessingException('WorldpayGateway: Invalid webhook data.');
        }

        $eventType = $sanitizedData['type']; // e.g., "payments.authorised", "payments.captured", "payments.refused"
        $payment = $sanitizedData['resource']; // Contains payment details
        $paymentId = $payment['id']; // This is often the `events` ID from the API response
        $outcome = $payment['outcome'] ?? ($sanitizedData['latestEvent'] ?? 'unknown'); // `outcome` for payment events, `latestEvent` for some refund webhooks

        $isSuccess = in_array(strtolower($outcome), ['authorised', 'captured', 'sent_for_refund', 'refunded']);
        $isFailed = in_array(strtolower($outcome), ['refused', 'error', 'cancelled']);

        return [
            'status' => $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending'),
            'message' => 'WorldpayGateway webhook processed. Type: ' . $eventType . ', Outcome: ' . $outcome,
            'transactionId' => $paymentId, // Event ID or related payment ID
            'orderId' => $payment['transactionReference'] ?? null,
            'paymentStatus' => $outcome,
            'rawData' => $sanitizedData
        ];
    }

    public function verify(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        // Use the event ID or payment ID obtained from initialize or webhook
        if (empty($sanitizedData['gatewayReferenceId'])) {
            throw new VerificationException('WorldpayGateway: Missing gatewayReferenceId (Event ID or Payment ID) for verification.');
        }
        $eventId = $sanitizedData['gatewayReferenceId'];

        try {
            // Typically, you'd GET from the event URL like /events/{eventId} or /payments/{paymentId}
            // $response = $this->httpClient('GET', $this->getApiBaseUrl() . '/events/' . $eventId, [], $this->getRequestHeaders());
            // Mocked response
            $mockOutcome = 'authorised';
            if ($eventId === 'fail_verify_ref') {
                $mockOutcome = 'refused';
            } elseif ($eventId === 'pending_verify_ref') {
                $mockOutcome = 'pending'; // Or some other status like 'awaiting_capture'
            }
            $response = ['body' => [
                    'id' => $eventId,
                    'type' => 'payments.authorised', // Example type
                    'outcome' => $mockOutcome,
                    'transactionReference' => 'order_' . uniqid(),
                    'value' => ['amount' => $sanitizedData['original_amount_for_test'] ?? 1000, 'currency' => 'GBP'],
                ],
                'status_code' => 200
            ];

            if ($response['status_code'] !== 200 || empty($response['body']['outcome'])) {
                throw new VerificationException('WorldpayGateway: Failed to verify payment event. ' . ($response['body']['description'] ?? 'API error'));
            }

            $eventDetails = $response['body'];
            $paymentStatus = $eventDetails['outcome'] ?? 'unknown';
            $isSuccess = in_array(strtolower($paymentStatus), ['authorised', 'captured', 'settled_by_acquirer']);
            $isPending = strtolower($paymentStatus) === 'pending';

            return [
                'status' => $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed'),
                'message' => 'WorldpayGateway verification result: ' . $paymentStatus,
                'transactionId' => $eventDetails['id'],
                'orderId' => $eventDetails['transactionReference'] ?? null,
                'paymentStatus' => $paymentStatus,
                'rawData' => $eventDetails
            ];
        } catch (\Exception $e) {
            throw new VerificationException('WorldpayGateway: Verification request failed. ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(array $data): array
    {
        $sanitizedData = $this->sanitize($data);
        if (empty($sanitizedData['orderId'])) { // Worldpay refunds are often linked to the original order/transaction reference
            throw new RefundException('WorldpayGateway: Missing orderId for refund.');
        }
        if (empty($sanitizedData['amount']) || !is_numeric($sanitizedData['amount']) || $sanitizedData['amount'] <= 0) {
            throw new RefundException('WorldpayGateway: Invalid or missing amount for refund (minor units).');
        }

        // The refund endpoint is often /orders/{orderCode}/refund or similar, using the original transaction/order reference.
        // For this mock, let's assume we use the `transactionReference` (our orderId) to initiate a refund.
        // And the refund happens on the original payment instrument, so no new token needed usually.

        $payload = [
            'value' => [
                'amount' => (int) $sanitizedData['amount'],
                'currency' => strtoupper($sanitizedData['currency'] ?? 'GBP'),
            ],
            'reason' => $sanitizedData['reason'] ?? 'Merchant requested refund',
            'reference' => $sanitizedData['refundReference'] ?? 'REF-' . uniqid() // Your unique reference for this refund
        ];

        try {
            // The endpoint for refunds might be different, e.g., /payments/{paymentId}/refunds or using order codes.
            // $response = $this->httpClient('POST', $this->getApiBaseUrl() . '/orders/' . $sanitizedData['orderId'] .'/refund', $payload, $this->getRequestHeaders());

            // Mocked response for refund
            if (($sanitizedData['amount'] ?? 0) == 99999) {
                 throw new RefundException('WorldpayGateway: API rejected refund (simulated).');
            }
            $mockRefundEventId = 'wp_ref_evt_' . uniqid();
            $response = ['body' => [
                    '_links' => [
                         'payments:event' => ['href' => $this->getApiBaseUrl() . '/events/' . $mockRefundEventId]
                    ],
                    'status' => 'accepted', // Refund request accepted, will be processed asynchronously
                    'reference' => $payload['reference'],
                ],
                'status_code' => 202 // Accepted
            ];

            if ($response['status_code'] !== 202 || empty($response['body']['_links']['payments:event'])) {
                throw new RefundException('WorldpayGateway: Failed to initiate refund. ' . ($response['body']['description'] ?? 'Unknown error'));
            }

            $refundEventUrl = $response['body']['_links']['payments:event']['href'];
            // The actual status of the refund will come via webhook (e.g., payments.refunded)
            // or by querying the refund event URL.

            return [
                'status' => 'pending', // Refund is pending, final status via webhook or polling event URL
                'message' => 'Worldpay refund request accepted. Status: ' . ($response['body']['status'] ?? 'pending'),
                'refundId' => basename($refundEventUrl), // This is the event ID for the refund action
                'transactionId' => $sanitizedData['transactionId'] ?? $sanitizedData['orderId'], // Original payment/order ID
                'paymentStatus' => 'refund_pending',
                'rawData' => $response['body']
            ];
        } catch (\Exception $e) {
            throw new RefundException('WorldpayGateway: Refund request failed. ' . $e->getMessage(), 0, $e);
        }
    }
}
