<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Gateways\BanglaQrGateway;

class BanglaQrGatewayTest extends TestCase
{
    public function test_initialize_returns_qr_payload(): void
    {
        $gateway = $this->makeGateway([
            'status_code' => 200,
            'body' => [
                'status' => 'success',
                'data' => [
                    'qr_reference' => 'QR-REF-1',
                    'qr_content' => 'BANGLABANKQRDATA-1',
                    'qr_image' => 'data:image/png;base64,AAA',
                    'expires_at' => '2026-08-15T10:00:00Z',
                ],
            ],
        ]);

        $result = $gateway->initialize([
            'amount' => 500,
            'orderId' => 'ORD-QR-1',
        ]);

        $this->assertSame('pending_user_action', $result['status']);
        $this->assertSame('QR-REF-1', $result['gatewayReferenceId']);
        $this->assertSame('BANGLABANKQRDATA-1', $result['qrContent']);
        $this->assertSame('data:image/png;base64,AAA', $result['qrImage']);
        $this->assertSame('2026-08-15T10:00:00Z', $result['expiresAt']);
    }

    public function test_initialize_requires_merchant_id_and_api_key(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        new class ([], [
            'api_key' => 'k',
        ]) extends BanglaQrGateway {
            public function __construct(array $fixture, array $config)
            {
                parent::__construct($config);
            }
        };
    }

    public function test_initialize_throws_when_gateway_rejects(): void
    {
        $this->expectException(InitializationException::class);

        $this->makeGateway([
            'status_code' => 400,
            'body' => ['status' => 'error', 'message' => 'Merchant not registered'],
        ])->initialize(['amount' => 100, 'orderId' => 'ORD-QR-2']);
    }

    public function test_verify_and_refund_inherit_bangladesh_flow(): void
    {
        $gateway = $this->makeGateway([
            'status_code' => 200,
            'body' => ['status' => 'success', 'data' => ['status' => 'COMPLETED', 'amount' => 500, 'currency' => 'BDT']],
        ]);

        $verified = $gateway->verify(['transactionId' => 'TXN-QR-1']);
        $this->assertSame('success', $verified['status']);
        $this->assertSame('TXN-QR-1', $verified['transactionId']);

        $refunded = $gateway->refund(['transactionId' => 'TXN-QR-1', 'amount' => 500, 'reason' => 'test']);
        $this->assertSame('success', $refunded['status']);
        $this->assertSame('TXN-QR-1', $refunded['transactionId']);
    }

    public function test_gateway_factory_resolves_banglaqr(): void
    {
        $factory = new \Hadi\Payment\Factories\GatewayFactory();
        $gateway = $factory->create('banglaqr', [
            'merchant_id' => 'm',
            'api_key' => 'k',
            'sandbox' => true,
        ]);

        $this->assertInstanceOf(BanglaQrGateway::class, $gateway);
        $this->assertTrue($gateway->isConfigured());
    }

    private function makeGateway(array $fixture = [], array $config = []): BanglaQrGateway
    {
        $defaults = ['merchant_id' => 'm', 'api_key' => 'k', 'sandbox' => true];

        return new class ($fixture, array_merge($defaults, $config)) extends BanglaQrGateway {
            private array $fixture;

            public function __construct(array $fixture, array $config)
            {
                $this->fixture = $fixture;
                parent::__construct($config);
            }

            protected function httpClient(string $method, string $url, array $data = [], array $headers = []): array
            {
                return $this->fixture;
            }
        };
    }
}
