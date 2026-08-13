<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\RefundException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Gateways\PrivacyComGateway;
use Hadi\Payment\Gateways\RevolutVirtualCardGateway;
use Hadi\Payment\Gateways\StripeIssuingGateway;
use Hadi\Payment\Gateways\VirtualCardGateway;
use Hadi\Payment\Gateways\WiseVirtualCardGateway;

class VirtualCardGatewayTest extends TestCase
{
    private array $fixtures = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = [
            'issue' => [
                'status_code' => 200,
                'body' => [
                    'status' => 'success',
                    'data' => [
                        'card_id' => 'VC123',
                        'card_url' => 'https://sandbox.vc-gw.com/cards/VC123',
                        'last4' => '4242',
                        'currency' => 'USD',
                    ],
                ],
            ],
            'card' => [
                'status_code' => 200,
                'body' => [
                    'status' => 'success',
                    'data' => ['status' => 'ACTIVE', 'last4' => '4242', 'currency' => 'USD'],
                ],
            ],
            'credit' => [
                'status_code' => 200,
                'body' => [
                    'status' => 'success',
                    'data' => ['refund_id' => 'CRD123', 'credited_amount' => 40],
                ],
            ],
        ];
    }

    private function makeGateway(): VirtualCardGateway
    {
        return new class ($this->fixtures, [
            'api_key' => 'k', 'sandbox' => true,
        ]) extends VirtualCardGateway {
            private array $fixtures;

            public function __construct(array $fixtures, array $config)
            {
                $this->fixtures = $fixtures;
                parent::__construct($config);
            }

            protected function apiBaseUrls(): array
            {
                return ['sandbox' => 'https://sandbox.test-vc.com/api/v1', 'production' => 'https://api.test-vc.com/api/v1'];
            }

            protected function requiredConfigKeys(): array
            {
                return ['api_key'];
            }

            protected function gatewaySlug(): string
            {
                return 'testvc';
            }

            protected function httpClient(string $method, string $url, array $data = [], array $headers = []): array
            {
                if (str_contains($url, '/issue')) {
                    return $this->fixtures['issue'];
                }

                if (str_contains($url, '/credit')) {
                    return $this->fixtures['credit'];
                }

                return $this->fixtures['card'];
            }
        };
    }

    public function test_validate_config_requires_keys(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        new class (['sandbox' => true]) extends VirtualCardGateway {
            protected function apiBaseUrls(): array
            {
                return ['sandbox' => 'https://sandbox.test/api/v1', 'production' => 'https://api.test/api/v1'];
            }

            protected function requiredConfigKeys(): array
            {
                return ['api_key'];
            }

            protected function gatewaySlug(): string
            {
                return 'testvc';
            }
        };
    }

    public function test_initialize_issues_virtual_card(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->initialize([
            'amount' => 100,
            'currency' => 'USD',
            'orderId' => 'ORD-1',
            'customer' => ['name' => 'Alice', 'email' => 'a@b.com'],
        ]);

        $this->assertSame('pending_user_action', $result['status']);
        $this->assertSame('VC123', $result['gatewayReferenceId']);
        $this->assertSame('https://sandbox.vc-gw.com/cards/VC123', $result['paymentUrl']);
    }

    public function test_initialize_rejects_missing_currency(): void
    {
        $this->expectException(InitializationException::class);

        $this->makeGateway()->initialize(['amount' => 100, 'orderId' => 'ORD-1']);
    }

    public function test_verify_maps_card_state(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->verify(['cardId' => 'VC123']);

        $this->assertSame('pending', $result['status']);
        $this->assertSame('VC123', $result['cardId']);
        $this->assertSame('4242', $result['last4']);
    }

    public function test_verify_requires_card_id(): void
    {
        $this->expectException(VerificationException::class);

        $this->makeGateway()->verify([]);
    }

    public function test_process_aliases_verify(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->process(['transactionId' => 'VC123']);

        $this->assertSame('pending', $result['status']);
    }

    public function test_refund_credits_card(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->refund(['cardId' => 'VC123', 'amount' => 40, 'reason' => 'test']);

        $this->assertSame('success', $result['status']);
        $this->assertSame('CRD123', $result['refundId']);
    }

    public function test_refund_requires_card_id(): void
    {
        $this->expectException(RefundException::class);

        $this->makeGateway()->refund([]);
    }

    public function test_is_configured(): void
    {
        $this->assertTrue($this->makeGateway()->isConfigured());
    }

    public function test_concrete_virtual_card_drivers_resolve(): void
    {
        $this->assertInstanceOf(VirtualCardGateway::class, new PrivacyComGateway(['api_key' => 'k']));
        $this->assertInstanceOf(VirtualCardGateway::class, new RevolutVirtualCardGateway(['api_key' => 'k']));
        $this->assertInstanceOf(VirtualCardGateway::class, new StripeIssuingGateway(['api_key' => 'k']));
        $this->assertInstanceOf(VirtualCardGateway::class, new WiseVirtualCardGateway(['api_key' => 'k']));
    }
}
