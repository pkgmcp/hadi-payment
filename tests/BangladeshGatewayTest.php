<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\RefundException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Gateways\BangladeshGateway;

class BangladeshGatewayTest extends TestCase
{
    private array $fixtures = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = [
            'success' => [
                'status_code' => 200,
                'body' => [
                    'status' => 'success',
                    'data' => [
                        'transaction_id' => 'TXN123',
                        'payment_url' => 'https://sandbox.test-gw.com/pay/TXN123',
                        'amount' => 100,
                        'currency' => 'BDT',
                    ],
                ],
            ],
            'pending' => [
                'status_code' => 200,
                'body' => [
                    'status' => 'success',
                    'data' => ['status' => 'PENDING', 'amount' => 100, 'currency' => 'BDT'],
                ],
            ],
        ];
    }

    private function makeGateway(): BangladeshGateway
    {
        return new class ($this->fixtures, [
            'api_key' => 'k', 'secret_key' => 's', 'merchant_id' => 'm', 'sandbox' => true,
        ]) extends BangladeshGateway {
            private array $fixtures;

            public function __construct(array $fixtures, array $config)
            {
                $this->fixtures = $fixtures;
                parent::__construct($config);
            }

            protected function apiBaseUrls(): array
            {
                return ['sandbox' => 'https://sandbox.test-gw.com/api/v1', 'production' => 'https://api.test-gw.com/api/v1'];
            }

            protected function requiredConfigKeys(): array
            {
                return ['api_key', 'secret_key', 'merchant_id'];
            }

            protected function gatewaySlug(): string
            {
                return 'testgw';
            }

            protected function httpClient(string $method, string $url, array $data = [], array $headers = []): array
            {
                if (str_contains($url, '/verify/')) {
                    return $this->fixtures['pending'];
                }

                if (str_contains($url, '/refund')) {
                    return [
                        'status_code' => 200,
                        'body' => [
                            'status' => 'success',
                            'data' => ['refund_id' => 'REF123', 'refunded_amount' => 40],
                        ],
                    ];
                }

                return $this->fixtures['success'];
            }
        };
    }

    public function test_validate_config_requires_keys(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        new class ([
            'api_key' => 'k',
        ]) extends BangladeshGateway {
            protected function apiBaseUrls(): array
            {
                return ['sandbox' => 'https://sandbox.test/api/v1', 'production' => 'https://api.test/api/v1'];
            }

            protected function requiredConfigKeys(): array
            {
                return ['api_key', 'secret_key', 'merchant_id'];
            }

            protected function gatewaySlug(): string
            {
                return 'testgw';
            }
        };
    }

    public function test_initialize_returns_normalized_response(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->initialize([
            'amount' => 100,
            'orderId' => 'ORD-1',
            'customer' => ['name' => 'Alice', 'mobile' => '01700000000', 'email' => 'a@b.com'],
        ]);

        $this->assertSame('pending_user_action', $result['status']);
        $this->assertSame('TXN123', $result['gatewayReferenceId']);
        $this->assertSame('https://sandbox.test-gw.com/pay/TXN123', $result['paymentUrl']);
    }

    public function test_initialize_rejects_invalid_amount(): void
    {
        $this->expectException(InitializationException::class);

        $this->makeGateway()->initialize(['amount' => -5, 'orderId' => 'ORD-1']);
    }

    public function test_verify_maps_status(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->verify(['transactionId' => 'TXN123']);

        $this->assertSame('pending', $result['status']);
        $this->assertSame('TXN123', $result['transactionId']);
    }

    public function test_verify_requires_transaction_id(): void
    {
        $this->expectException(VerificationException::class);

        $this->makeGateway()->verify([]);
    }

    public function test_process_aliases_verify(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->process(['transactionId' => 'TXN123']);

        $this->assertSame('pending', $result['status']);
    }

    public function test_refund_returns_refund_id(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->refund(['transactionId' => 'TXN123', 'amount' => 40, 'reason' => 'test']);

        $this->assertSame('success', $result['status']);
        $this->assertSame('REF123', $result['refundId']);
    }

    public function test_refund_requires_transaction_id(): void
    {
        $this->expectException(RefundException::class);

        $this->makeGateway()->refund([]);
    }

    public function test_is_configured(): void
    {
        $this->assertTrue($this->makeGateway()->isConfigured());
    }
}
