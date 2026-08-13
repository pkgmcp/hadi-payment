<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Hadi\Payment\HadiPaymentServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            HadiPaymentServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('hadi-payment.gateways.bkash', [
            'appKey' => 'test',
            'appSecret' => 'test',
            'username' => 'test',
            'password' => 'test',
            'isSandbox' => true,
        ]);

        $app['config']->set('hadi-payment.gateways.nagad', [
            'merchantId' => 'test',
            'merchantPrivateKey' => 'test',
            'nagadPublicKey' => 'test',
            'isSandbox' => true,
        ]);
    }
}
