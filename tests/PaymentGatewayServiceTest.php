<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Exceptions\ValidationException;
use Hadi\Payment\PaymentResponse;
use Hadi\Payment\Services\PaymentGatewayService;
use Hadi\Payment\Services\PaymentLogger;
use Hadi\Payment\Services\PaymentManager;
use Hadi\Payment\Services\PaymentValidator;

class PaymentGatewayServiceTest extends TestCase
{
    private PaymentGatewayService $service;
    private PaymentLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new PaymentLogger();
        $this->service = new PaymentGatewayService(
            app(PaymentManager::class),
            $this->logger,
            new PaymentValidator()
        );
    }

    public function test_initialize_payment_returns_response_and_logs(): void
    {
        $response = $this->service->initializePayment('bkash', [
            'amount' => 150,
            'orderId' => 'ORD-' . uniqid(),
        ]);

        $this->assertInstanceOf(PaymentResponse::class, $response);
        $this->assertTrue($response->success);
        $this->assertCount(1, $this->logger->getLogs());
        $this->assertSame('initialize_payment', $this->logger->getLogs()[0]['operation']);
    }

    public function test_initialize_payment_rejects_invalid_data(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->initializePayment('bkash', ['amount' => 0]);
    }

    public function test_verify_payment_and_refund(): void
    {
        $verify = $this->service->verifyPayment('bkash', 'PAY-V-1');
        $this->assertInstanceOf(PaymentResponse::class, $verify);
        $this->assertSame('verify_payment', $this->logger->getLogs()[0]['operation']);

        $refund = $this->service->refundPayment('bkash', 'PAY-V-1', 25, 'Test refund');
        $this->assertInstanceOf(PaymentResponse::class, $refund);
        $this->assertTrue($refund->success);
        $this->assertSame('refund_payment', $this->logger->getLogs()[1]['operation']);
    }

    public function test_get_payment_status_logs(): void
    {
        $status = $this->service->getPaymentStatus('bkash', 'PAY-S-1');

        $this->assertInstanceOf(PaymentResponse::class, $status);
        $this->assertSame('get_payment_status', $this->logger->getLogs()[0]['operation']);
    }

    public function test_gateway_support_queries(): void
    {
        $this->assertTrue($this->service->isGatewaySupported('stripe'));
        $this->assertFalse($this->service->isGatewaySupported('not-a-real-gateway'));

        $gateways = $this->service->getSupportedGateways();
        $this->assertArrayHasKey('bkash', $gateways);
    }

    public function test_get_gateway_config(): void
    {
        $config = $this->service->getGatewayConfig('bkash');

        $this->assertSame('test', $config['appKey']);
    }

    public function test_invalid_payment_id_throws(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->verifyPayment('bkash', '');
    }
}
