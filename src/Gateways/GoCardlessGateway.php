<?php

namespace Hadi\Payment\Gateways;

use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Exceptions\PaymentException;
use Hadi\Payment\Exceptions\InvalidConfigurationException;

class GoCardlessGateway extends PaymentGateway
{
    protected array $config = [];
    protected const API_VERSION = '2015-07-06';
    protected const BASE_URI = 'https://api.gocardless.com/';
    protected const SANDBOX_BASE_URI = 'https://api-sandbox.gocardless.com/';

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->config['base_uri'] = $this->config['mode'] === 'live' ? self::BASE_URI : self::SANDBOX_BASE_URI;
    }

    public function getDefaultConfig(): array
    {
        return [
            'accessToken' => '',
            'webhookSecret' => '',
            'mode' => 'sandbox', // 'live' or 'sandbox'
        ];
    }

    public function validateConfig(array $config): void
    {
        if (empty($config['accessToken'])) {
            throw new InvalidConfigurationException('Access token is required for GoCardless.');
        }
        if (empty($config['webhookSecret'])) {
            throw new InvalidConfigurationException('Webhook secret is required for GoCardless.');
        }
    }

    public function initialize(array $parameters = []): array
    {
        $this->sanitize($parameters);

        // Typically involves creating a redirect flow for mandate creation
        // or setting up a billing request
        // For simplicity, we'll assume parameters include customer details and amount

        if (empty($parameters['amount']) || empty($parameters['currency']) || empty($parameters['customer'])) {
            throw new PaymentException('Amount, currency, and customer details are required for GoCardless payment initialization.');
        }

        // Mock mandate creation or billing request setup
        $redirectUrl = $this->config['base_uri'] . 'redirect_flows/' . uniqid('RF'); // Placeholder

        return [
            'status' => 'pending',
            'message' => 'GoCardless payment initialized. Redirect customer to complete mandate.',
            'redirect_url' => $redirectUrl,
            'transaction_id' => uniqid('gc_'),
            'data' => $parameters,
        ];
    }

    public function process(array $parameters = []): array
    {
        $this->sanitize($parameters);

        // This step usually happens after a mandate is in place.
        // We'd create a payment against a mandate.
        if (empty($parameters['mandate_id']) || empty($parameters['amount']) || empty($parameters['currency'])) {
            throw new PaymentException('Mandate ID, amount, and currency are required to process a GoCardless payment.');
        }

        // Mock API call to create a payment
        $response = $this->httpClient('POST', $this->config['base_uri'] . 'payments', [
            'payments' => [
                'amount' => $parameters['amount'] * 100, // Amount in minor units
                'currency' => $parameters['currency'],
                'links' => [
                    'mandate' => $parameters['mandate_id'],
                ],
                'metadata' => $parameters['metadata'] ?? [],
            ],
        ], [
            'Authorization' => 'Bearer ' . $this->config['accessToken'],
            'GoCardless-Version' => self::API_VERSION,
            'Content-Type' => 'application/json',
        ]);

        if (isset($response['error'])) {
            throw new PaymentException('GoCardless API Error: ' . ($response['error']['message'] ?? 'Unknown error.'));
        }

        $paymentId = $response['payments']['id'] ?? uniqid('pay_');

        return [
            'status' => 'pending', // Payments are usually not instant
            'message' => 'GoCardless payment submitted successfully.',
            'transaction_id' => $paymentId,
            'provider_response' => $response,
        ];
    }

    public function verify(array $parameters = []): array
    {
        $this->sanitize($parameters);

        if (empty($parameters['transaction_id'])) {
            throw new PaymentException('Transaction ID (Payment ID) is required to verify a GoCardless payment.');
        }

        // Mock API call to retrieve a payment
        $response = $this->httpClient('GET', $this->config['base_uri'] . 'payments/' . $parameters['transaction_id'], [], [
            'Authorization' => 'Bearer ' . $this->config['accessToken'],
            'GoCardless-Version' => self::API_VERSION,
        ]);

        if (isset($response['error'])) {
            throw new PaymentException('GoCardless API Error: ' . ($response['error']['message'] ?? 'Unknown error.'));
        }

        $payment = $response['payments'] ?? null;

        if (!$payment) {
            return [
                'status' => 'failed',
                'message' => 'Payment not found.',
                'transaction_id' => $parameters['transaction_id'],
            ];
        }

        $statusMap = [
            'pending_customer_approval' => 'pending',
            'pending_submission' => 'pending',
            'submitted' => 'pending',
            'confirmed' => 'success',
            'paid_out' => 'success',
            'cancelled' => 'failed',
            'customer_approval_denied' => 'failed',
            'failed' => 'failed',
            'charged_back' => 'failed', // Or 'refunded' depending on context
        ];

        return [
            'status' => $statusMap[$payment['status']] ?? 'unknown',
            'message' => 'GoCardless payment status: ' . $payment['status'],
            'transaction_id' => $payment['id'],
            'amount' => $payment['amount'] / 100,
            'currency' => $payment['currency'],
            'provider_response' => $response,
        ];
    }

    public function refund(array $parameters = []): array
    {
        $this->sanitize($parameters);

        if (empty($parameters['transaction_id']) || empty($parameters['amount'])) {
            throw new PaymentException('Transaction ID and amount are required for GoCardless refund.');
        }

        // Mock API call to create a refund
        // GoCardless refunds are associated with payouts or specific payments.
        // This is a simplified version.
        $response = $this->httpClient('POST', $this->config['base_uri'] . 'refunds', [
            'refunds' => [
                'amount' => $parameters['amount'] * 100, // Amount in minor units
                'links' => [
                    'payment' => $parameters['transaction_id'],
                ],
                'metadata' => $parameters['metadata'] ?? [],
            ],
        ], [
            'Authorization' => 'Bearer ' . $this->config['accessToken'],
            'GoCardless-Version' => self::API_VERSION,
            'Content-Type' => 'application/json',
        ]);

        if (isset($response['error'])) {
            throw new PaymentException('GoCardless API Error: ' . ($response['error']['message'] ?? 'Unknown error.'));
        }

        $refundId = $response['refunds']['id'] ?? uniqid('rf_');

        return [
            'status' => 'pending', // Refunds are often not immediate
            'message' => 'GoCardless refund processed successfully.',
            'refund_id' => $refundId,
            'transaction_id' => $parameters['transaction_id'],
            'provider_response' => $response,
        ];
    }

    // Placeholder for actual HTTP client logic (e.g., using Guzzle)
    protected function httpClient(string $method, string $url, array $data = [], array $headers = []): array
    {
        // This is a mock implementation. In a real scenario, use Guzzle or similar.
        // Simulate API response based on action
        if (strpos($url, '/payments') !== false && $method === 'POST') {
            return ['payments' => ['id' => uniqid('pay_gc_'), 'status' => 'pending_submission']];
        } elseif (strpos($url, '/payments/') !== false && $method === 'GET') {
            return ['payments' => ['id' => basename($url), 'status' => 'confirmed', 'amount' => 5000, 'currency' => 'GBP']];
        } elseif (strpos($url, '/refunds') !== false && $method === 'POST') {
            return ['refunds' => ['id' => uniqid('rf_gc_'), 'status' => 'created']];
        }
        return ['error' => ['message' => 'Mock HTTP client error or unhandled endpoint.']];
    }
}
