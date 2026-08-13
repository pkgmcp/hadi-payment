<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Exceptions\ConfigurationException;
use Hadi\Payment\Factories\GatewayFactory;
use Hadi\Payment\Gateways\BkashGateway;
use Hadi\Payment\Gateways\Generic\MobileMoneyGateway;
use Hadi\Payment\Gateways\RocketGateway;
use Hadi\Payment\PaymentGateway;

class GatewayFactoryTest extends TestCase
{
    public function test_it_registers_all_builtin_gateways(): void
    {
        $factory = new GatewayFactory();

        $this->assertGreaterThanOrEqual(60, count($factory->getAvailableGateways()));
        $this->assertTrue($factory->isGatewayAvailable('bkash'));
        $this->assertTrue($factory->isGatewayAvailable('nagad'));
        $this->assertTrue($factory->isGatewayAvailable('stripe'));
        $this->assertTrue($factory->isGatewayAvailable('aamarpay'));
        $this->assertTrue($factory->isGatewayAvailable('amarpay'));
    }

    public function test_it_registers_catalog_gateways(): void
    {
        $factory = new GatewayFactory();

        $this->assertGreaterThanOrEqual(300, count($factory->getAvailableGateways()));
        $this->assertTrue($factory->isGatewayAvailable('mtn-momo'));
        $this->assertTrue($factory->isGatewayAvailable('mercadopago'));
        $this->assertTrue($factory->isGatewayAvailable('jazzcash'));
    }

    public function test_catalog_gateway_resolves_to_generic_driver(): void
    {
        $factory = new GatewayFactory();

        $gateway = $factory->create('mtn-momo', [
            'merchant_id' => 'm',
            'endpoint' => 'https://api.example.test',
        ]);

        $this->assertInstanceOf(MobileMoneyGateway::class, $gateway);
        $this->assertTrue($gateway->isConfigured());
    }

    public function test_catalog_gateway_uses_concrete_driver_when_registered(): void
    {
        $factory = new GatewayFactory();

        $gateway = $factory->create('bkash', [
            'appKey' => 'k', 'appSecret' => 's', 'username' => 'u', 'password' => 'p',
        ]);

        $this->assertInstanceOf(BkashGateway::class, $gateway);
    }

    public function test_catalog_gateway_name_is_resolved(): void
    {
        $factory = new GatewayFactory();

        $this->assertSame('MTN Mobile Money', $factory->getGatewayName('mtn-momo'));
        $this->assertSame('Bkash', $factory->getGatewayName('bkash'));
        $this->assertNotSame('mtn-momo', $factory->getGatewayName('mtn-momo'));
    }

    public function test_it_creates_a_gateway_instance(): void
    {
        $factory = new GatewayFactory();

        $gateway = $factory->create('bkash', [
            'appKey' => 'k', 'appSecret' => 's', 'username' => 'u', 'password' => 'p',
        ]);

        $this->assertInstanceOf(PaymentGateway::class, $gateway);
        $this->assertInstanceOf(BkashGateway::class, $gateway);
    }

    public function test_it_throws_for_unknown_gateway(): void
    {
        $this->expectException(ConfigurationException::class);

        (new GatewayFactory())->create('not-a-gateway', []);
    }

    public function test_it_registers_custom_gateways(): void
    {
        $factory = new GatewayFactory();
        $factory->registerGateway('custom-rocket', RocketGateway::class);

        $this->assertTrue($factory->isGatewayAvailable('custom-rocket'));
        $this->assertInstanceOf(RocketGateway::class, $factory->create('custom-rocket', [
            'api_key' => 'k', 'secret_key' => 's', 'merchant_id' => 'm',
        ]));
    }

    public function test_is_configured_reflects_configuration_state(): void
    {
        $factory = new GatewayFactory();

        $configured = $factory->create('bkash', [
            'appKey' => 'k', 'appSecret' => 's', 'username' => 'u', 'password' => 'p',
        ]);
        $this->assertTrue($configured->isConfigured());
    }

    public function test_it_throws_when_required_config_is_missing(): void
    {
        $this->expectException(\Hadi\Payment\Exceptions\InvalidConfigurationException::class);

        new BkashGateway(['appKey' => 'k', 'appSecret' => 's']);
    }
}
