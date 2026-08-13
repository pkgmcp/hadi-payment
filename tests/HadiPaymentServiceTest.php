<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\HadiPayment;
use Hadi\Payment\Factories\GatewayFactory;
use Hadi\Payment\Facades\HadiPayment as HadiPaymentFacade;
use Hadi\Payment\PaymentResponse;
use Hadi\Payment\Services\PaymentManager;

class HadiPaymentServiceTest extends TestCase
{
    public function test_provider_registers_services(): void
    {
        $this->assertInstanceOf(HadiPayment::class, app(HadiPayment::class));
        $this->assertInstanceOf(PaymentManager::class, app(PaymentManager::class));
        $this->assertInstanceOf(GatewayFactory::class, app(GatewayFactory::class));
    }

    public function test_facade_resolves_the_service(): void
    {
        $this->assertInstanceOf(HadiPayment::class, HadiPaymentFacade::getFacadeRoot());
    }

    public function test_config_is_merged(): void
    {
        $this->assertSame('bkash', config('hadi-payment.default_gateway'));
        $this->assertSame('BDT', config('hadi-payment.defaults.currency'));
    }

    public function test_it_initializes_a_payment_via_the_service(): void
    {
        $response = HadiPaymentFacade::initializePayment('bkash', [
            'amount' => 250,
            'orderId' => 'ORD-' . uniqid(),
        ]);

        $this->assertInstanceOf(PaymentResponse::class, $response);
        $this->assertTrue($response->success);
        $this->assertNotNull($response->redirectUrl);
        $this->assertNotNull($response->paymentId);
    }

    public function test_it_returns_supported_gateways(): void
    {
        $gateways = HadiPaymentFacade::getSupportedGateways();

        $this->assertGreaterThanOrEqual(60, count($gateways));
        $this->assertTrue(HadiPaymentFacade::isGatewaySupported('bkash'));
        $this->assertFalse(HadiPaymentFacade::isGatewaySupported('nonexistent'));
    }

    public function test_it_exposes_the_low_level_array_api(): void
    {
        $result = HadiPaymentFacade::initialize('bkash', [
            'amount' => 500,
            'orderId' => 'ORD-' . uniqid(),
        ]);

        $this->assertIsArray($result);
        $this->assertSame('pending_user_action', $result['status']);
        $this->assertArrayHasKey('paymentUrl', $result);
    }
}
