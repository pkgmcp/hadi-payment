<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Models\Payment;
use Hadi\Payment\Models\PaymentLog;
use Hadi\Payment\Models\PaymentRefund;

class DatabaseMigrationTest extends TestCase
{
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function test_migrations_run_and_models_persist(): void
    {
        $payment = Payment::create([
            'order_id' => 'ORD-1001',
            'reference_id' => 'REF-1001',
            'gateway' => 'bkash',
            'payment_id' => 'BPAY-1',
            'transaction_id' => 'TXN-1',
            'amount' => 150.50,
            'currency' => 'BDT',
            'status' => Payment::STATUS_COMPLETED,
            'gateway_response' => ['status' => '0010001'],
            'processed_at' => now(),
            'refunded_at' => null,
        ]);

        $this->assertDatabaseHas('payments', ['order_id' => 'ORD-1001']);
        $this->assertTrue($payment->isCompleted());
        $this->assertEquals('150.50', $payment->amount);
        $this->assertEquals(['status' => '0010001'], $payment->gateway_response);

        $payment->logs()->create([
            'gateway' => 'bkash',
            'operation' => 'initialize',
            'status' => 'success',
            'message' => 'Payment initialized',
        ]);

        $this->assertSame(1, PaymentLog::where('payment_id', $payment->id)->count());

        $payment->refunds()->create([
            'amount' => 50.00,
            'reason' => 'Partial refund',
            'status' => PaymentRefund::STATUS_COMPLETED,
        ]);

        $this->assertSame(1, PaymentRefund::where('payment_id', $payment->id)->count());
    }

    public function test_gateway_column_accepts_arbitrary_gateway_names(): void
    {
        Payment::create([
            'order_id' => 'ORD-2001',
            'gateway' => 'stripe',
            'amount' => 10,
            'currency' => 'USD',
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('payments', ['gateway' => 'stripe']);
    }
}
