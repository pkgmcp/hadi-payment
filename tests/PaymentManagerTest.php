<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Services\PaymentManager;
use Hadi\Payment\PaymentResponse;

class PaymentManagerTest extends TestCase
{
    public function test_initialize_payment_returns_payment_response(): void
    {
        $manager = app(PaymentManager::class);

        $response = $manager->initializePayment('bkash', [
            'amount' => 100,
            'orderId' => 'ORD-' . uniqid(),
        ]);

        $this->assertInstanceOf(PaymentResponse::class, $response);
        $this->assertTrue($response->success);
        $this->assertSame('pending_user_action', $response->status);
    }

    public function test_verify_payment_returns_payment_response(): void
    {
        $manager = app(PaymentManager::class);

        $response = $manager->verifyPayment('bkash', 'mock_trx_1');

        $this->assertInstanceOf(PaymentResponse::class, $response);
    }

    public function test_refund_payment_returns_payment_response(): void
    {
        $manager = app(PaymentManager::class);

        $response = $manager->refundPayment('bkash', 'PAY123', 50, 'Test refund');

        $this->assertInstanceOf(PaymentResponse::class, $response);
        $this->assertTrue($response->success);
    }

    public function test_supported_gateways_are_listed(): void
    {
        $manager = app(PaymentManager::class);

        $this->assertTrue($manager->isGatewaySupported('stripe'));
        $this->assertTrue($manager->isGatewaySupported('rocket'));
    }
}
