<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Events\PaymentCompleted;
use Hadi\Payment\Events\PaymentFailed;
use Hadi\Payment\Events\PaymentInitialized;
use Hadi\Payment\Events\PaymentRefunded;
use Hadi\Payment\Models\Payment;
use Hadi\Payment\PaymentResponse;
use Hadi\Payment\Services\PaymentGatewayService;
use Hadi\Payment\Services\PaymentLogger;
use Hadi\Payment\Services\PaymentManager;
use Hadi\Payment\Services\PaymentRecordingService;
use Hadi\Payment\Services\PaymentValidator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

class PaymentRecordingServiceTest extends TestCase
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

        $app['config']->set('hadi-payment.ai_agent.enabled', false);
    }

    private function makeService(bool $eventsEnabled = true): PaymentGatewayService
    {
        config()->set('hadi-payment.events.enabled', $eventsEnabled);

        return new PaymentGatewayService(
            app(PaymentManager::class),
            new PaymentLogger(),
            new PaymentValidator(),
            app(PaymentRecordingService::class)
        );
    }

    public function test_sync_initialize_creates_payment_and_dispatches_initialized(): void
    {
        Event::fake();

        $service = $this->makeService();
        $response = $service->initializePayment('bkash', [
            'order_id' => 'ORD-REC-1',
            'amount' => 120,
            'currency' => 'BDT',
        ]);

        Event::assertDispatched(PaymentInitialized::class);
        $this->assertTrue($response->success);
        $this->assertSame(1, Payment::where('gateway', 'bkash')->count());
        $this->assertSame(Payment::STATUS_PENDING, Payment::first()->status);
    }

    public function test_sync_verify_dispatches_completed_and_updates_payment(): void
    {
        Event::fake();

        $service = $this->makeService();
        $service->initializePayment('bkash', [
            'order_id' => 'ORD-REC-2',
            'amount' => 200,
            'currency' => 'BDT',
        ]);

        $payment = Payment::first();
        $this->assertNotNull($payment);

        $service->verifyPayment('bkash', (string) $payment->transaction_id);

        Event::assertDispatched(PaymentCompleted::class);
        $this->assertSame(Payment::STATUS_COMPLETED, $payment->fresh()->status);
    }

    public function test_sync_refund_dispatches_refunded_and_updates_payment(): void
    {
        Event::fake();

        $service = $this->makeService();
        $service->initializePayment('bkash', [
            'order_id' => 'ORD-REC-3',
            'amount' => 300,
            'currency' => 'BDT',
        ]);

        $payment = Payment::first();
        $this->assertNotNull($payment);

        $service->refundPayment('bkash', (string) $payment->transaction_id, 50, 'Customer request');

        Event::assertDispatched(PaymentRefunded::class);
        $this->assertSame(Payment::STATUS_REFUNDED, $payment->fresh()->status);
        $this->assertSame(50.0, (float) $payment->fresh()->refunded_amount);
    }

    public function test_recording_service_dispatches_failed_for_non_success_response(): void
    {
        Event::fake();

        $service = $this->makeService();
        $service->initializePayment('bkash', [
            'order_id' => 'ORD-REC-5',
            'amount' => 500,
            'currency' => 'BDT',
        ]);

        $payment = Payment::first();
        $this->assertNotNull($payment);

        $recorder = app(PaymentRecordingService::class);
        $recorder->recordVerified(
            'bkash',
            (string) $payment->transaction_id,
            PaymentResponse::failure('Gateway declined', ['code' => 'declined'])
        );

        Event::assertDispatched(PaymentFailed::class);
        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
    }

    public function test_chain_disabled_by_default_no_payment_no_events(): void
    {
        Event::fake();
        Notification::fake();

        $service = $this->makeService(false);
        $response = $service->initializePayment('bkash', [
            'order_id' => 'ORD-REC-6',
            'amount' => 600,
            'currency' => 'BDT',
        ]);

        Event::assertNotDispatched(PaymentInitialized::class);
        $this->assertSame(0, Payment::count());
        $this->assertTrue($response->success);
    }

    public function test_full_chain_reaches_notification_when_recipient_configured(): void
    {
        Notification::fake();
        config()->set('hadi-payment.notifications.mail.recipient', 'ops@example.com');

        $service = $this->makeService();
        $service->initializePayment('bkash', [
            'order_id' => 'ORD-REC-7',
            'amount' => 700,
            'currency' => 'BDT',
        ]);

        $payment = Payment::first();
        $this->assertNotNull($payment);

        $service->verifyPayment('bkash', (string) $payment->transaction_id);

        Notification::assertSentOnDemand(
            \Hadi\Payment\Notifications\PaymentNotification::class,
            static fn (\Hadi\Payment\Notifications\PaymentNotification $n): bool => $n->type === 'payment_completed'
        );
    }
}
