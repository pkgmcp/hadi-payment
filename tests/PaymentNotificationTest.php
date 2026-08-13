<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Models\Payment;
use Hadi\Payment\Notifications\PaymentNotification;
use Hadi\Payment\Events\PaymentCompleted;
use Hadi\Payment\Events\PaymentFailed;
use Hadi\Payment\Events\PaymentRefunded;
use Hadi\Payment\Listeners\PaymentEventListener;
use Hadi\Payment\Services\PaymentLogger;
use Hadi\Payment\Services\AIAgentService;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

class PaymentNotificationTest extends TestCase
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

        $app['config']->set('hadi-payment.notifications.mail.enabled', true);
    }

    private function makePayment(): Payment
    {
        return Payment::create([
            'order_id' => 'ORD-N-' . uniqid(),
            'reference_id' => 'REF-N-' . uniqid(),
            'gateway' => 'bkash',
            'transaction_id' => 'mock_trx_' . uniqid(),
            'amount' => 250.50,
            'currency' => 'BDT',
            'status' => Payment::STATUS_COMPLETED,
        ]);
    }

    public function test_notification_channels_include_database_and_mail_when_enabled(): void
    {
        $notification = new PaymentNotification($this->makePayment(), 'payment_completed');

        $channels = $notification->via(new AnonymousNotifiable());

        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);
    }

    public function test_notification_channels_skip_mail_when_disabled(): void
    {
        config()->set('hadi-payment.notifications.mail.enabled', false);

        $notification = new PaymentNotification($this->makePayment(), 'payment_completed');

        $channels = $notification->via(new AnonymousNotifiable());

        $this->assertSame(['database'], $channels);
    }

    public function test_to_database_contains_payment_context(): void
    {
        $payment = $this->makePayment();
        $notification = new PaymentNotification($payment, 'payment_completed', ['note' => 'ok']);

        $data = $notification->toDatabase(new AnonymousNotifiable());

        $this->assertSame($payment->id, $data['payment_id']);
        $this->assertSame('payment_completed', $data['type']);
        $this->assertSame('bkash', $data['gateway']);
        $this->assertSame('250.50', $data['amount']);
        $this->assertSame(['note' => 'ok'], $data['data']);
    }

    public function test_to_mail_builds_typed_message(): void
    {
        $notification = new PaymentNotification($this->makePayment(), 'payment_failed', ['reason' => 'Insufficient funds']);

        $message = $notification->toMail(new AnonymousNotifiable());

        $this->assertSame('Payment Failed', $message->subject);
    }

    public function test_notification_is_dispatched_for_completed_payment(): void
    {
        Notification::fake();

        $payment = $this->makePayment();

        Notification::route('mail', 'buyer@example.com')
            ->notify(new PaymentNotification($payment, 'payment_completed'));

        Notification::assertSentOnDemand(PaymentNotification::class);
    }

    public function test_notification_links_resolve_to_real_route(): void
    {
        $payment = $this->makePayment();
        $notification = new PaymentNotification($payment, 'payment_completed');

        $message = $notification->toMail(new AnonymousNotifiable());

        $actionUrl = $message->actionUrl;
        $this->assertNotNull($actionUrl);
        $this->assertStringContainsString('/payment/' . $payment->id, $actionUrl);
        $this->assertNotNull(
            \Illuminate\Support\Facades\Route::getRoutes()->getByName('payment.show'),
            'payment.show route should be registered by the package'
        );
    }

    public function test_listener_sends_notification_on_completed_with_recipient(): void
    {
        Notification::fake();

        config()->set('hadi-payment.notifications.mail.recipient', 'ops@example.com');

        $payment = $this->makePayment();
        $listener = app(PaymentEventListener::class);
        $listener->handlePaymentCompleted(new PaymentCompleted($payment));

        Notification::assertSentOnDemand(
            PaymentNotification::class,
            static fn (PaymentNotification $n): bool => $n->type === 'payment_completed'
        );
    }

    public function test_listener_sends_notification_on_failed_with_recipient(): void
    {
        Notification::fake();

        config()->set('hadi-payment.notifications.mail.recipient', 'ops@example.com');

        $payment = $this->makePayment();
        $listener = app(PaymentEventListener::class);
        $listener->handlePaymentFailed(new PaymentFailed($payment, ['reason' => 'Insufficient funds']));

        Notification::assertSentOnDemand(
            PaymentNotification::class,
            static fn (PaymentNotification $n): bool => $n->type === 'payment_failed'
        );
    }

    public function test_listener_sends_notification_on_refunded_with_recipient(): void
    {
        Notification::fake();

        config()->set('hadi-payment.notifications.mail.recipient', 'ops@example.com');

        $payment = $this->makePayment();
        $listener = app(PaymentEventListener::class);
        $listener->handlePaymentRefunded(new PaymentRefunded($payment, ['refund_amount' => 50.0]));

        Notification::assertSentOnDemand(
            PaymentNotification::class,
            static fn (PaymentNotification $n): bool => $n->type === 'payment_refunded'
        );
    }

    public function test_listener_skips_notification_without_recipient(): void
    {
        Notification::fake();

        config()->set('hadi-payment.notifications.mail.recipient', null);

        $payment = $this->makePayment();
        $listener = app(PaymentEventListener::class);
        $listener->handlePaymentCompleted(new PaymentCompleted($payment));

        Notification::assertNothingSent();
    }
}
