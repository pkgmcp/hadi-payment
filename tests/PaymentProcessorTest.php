<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\PaymentProcessor;
use Hadi\Payment\Gateways\BkashGateway;
use Hadi\Payment\Gateways\NagadGateway;
use Hadi\Payment\Exceptions\GatewayNotFoundException;

class PaymentProcessorTest extends TestCase
{
    public function test_it_processes_gateways_with_a_consistent_api(): void
    {
        $processor = new PaymentProcessor();

        $processor->addGateway('bkash', BkashGateway::class, [
            'appKey' => 'k', 'appSecret' => 's', 'username' => 'u', 'password' => 'p',
        ]);
        $processor->addGateway('nagad', NagadGateway::class, [
            'merchantId' => 'm', 'merchantPrivateKey' => 'k', 'nagadPublicKey' => 'p',
        ]);

        $result = $processor->initialize('bkash', ['amount' => 99, 'orderId' => 'ORD1']);

        $this->assertArrayHasKey('gatewayReferenceId', $result);
        $this->assertArrayHasKey('paymentUrl', $result);

        $verify = $processor->verify('nagad', ['gatewayReferenceId' => 'NAGAD_CALLID_X']);
        $this->assertArrayHasKey('status', $verify);
    }

    public function test_it_throws_for_unknown_gateway(): void
    {
        $this->expectException(GatewayNotFoundException::class);

        (new PaymentProcessor())->getGateway('ghost');
    }

    public function test_it_rejects_invalid_gateway_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new PaymentProcessor())->addGateway('bad', \stdClass::class, []);
    }
}
