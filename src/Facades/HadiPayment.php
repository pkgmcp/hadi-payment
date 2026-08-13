<?php

declare(strict_types=1);

namespace Hadi\Payment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Hadi\Payment\Contracts\PaymentGatewayInterface gateway(string $name)
 * @method static array initialize(string $gateway, array $data)
 * @method static array process(string $gateway, array $data)
 * @method static array verify(string $gateway, array $data)
 * @method static array refund(string $gateway, array $data)
 * @method static \Hadi\Payment\PaymentResponse initializePayment(string $gateway, array $paymentData)
 * @method static \Hadi\Payment\PaymentResponse verifyPayment(string $gateway, string $paymentId)
 * @method static \Hadi\Payment\PaymentResponse refundPayment(string $gateway, string $paymentId, float $amount, string $reason = '')
 * @method static \Hadi\Payment\PaymentResponse getPaymentStatus(string $gateway, string $paymentId)
 * @method static array getSupportedGateways()
 * @method static bool isGatewaySupported(string $gateway)
 * @method static \Hadi\Payment\HadiPayment registerGateway(string $name, string $class)
 *
 * @see \Hadi\Payment\HadiPayment
 */
class HadiPayment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hadi\Payment\HadiPayment::class;
    }
}
