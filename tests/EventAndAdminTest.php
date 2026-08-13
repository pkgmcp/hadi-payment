<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Events\PaymentInitialized;
use Hadi\Payment\Listeners\PaymentEventListener;
use Hadi\Payment\Models\Payment;
use Illuminate\Support\Facades\Event;

class EventAndAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(database_path('migrations'));
    }

    public function test_payment_event_listener_is_subscribed(): void
    {
        $subscribed = Event::getRawListeners();

        $this->assertArrayHasKey(PaymentInitialized::class, $subscribed);

        $found = false;
        foreach ($subscribed[PaymentInitialized::class] as $listener) {
            if (is_array($listener)) {
                $target = $listener[0] ?? null;
                if (is_object($target) && $target instanceof PaymentEventListener) {
                    $found = true;
                    break;
                }
            } elseif (str_contains((string) $listener, PaymentEventListener::class)) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'PaymentEventListener should listen to PaymentInitialized');
    }

    public function test_admin_dashboard_view_exists(): void
    {
        $this->assertTrue(
            view()->exists('hadi-payment::admin.dashboard'),
            'admin.dashboard view should exist'
        );
        $this->assertTrue(
            view()->exists('hadi-payment::admin.payments.index'),
            'admin.payments.index view should exist'
        );
        $this->assertTrue(
            view()->exists('hadi-payment::admin.payments.show'),
            'admin.payments.show view should exist'
        );
        $this->assertTrue(
            view()->exists('hadi-payment::admin.problems.index'),
            'admin.problems.index view should exist'
        );
        $this->assertTrue(
            view()->exists('hadi-payment::admin.problems.show'),
            'admin.problems.show view should exist'
        );
        $this->assertTrue(
            view()->exists('hadi-payment::admin.invoices.index'),
            'admin.invoices.index view should exist'
        );
        $this->assertTrue(
            view()->exists('hadi-payment::admin.invoices.show'),
            'admin.invoices.show view should exist'
        );
    }

    public function test_admin_routes_are_registered(): void
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();

        $expected = [
            'admin.payment.dashboard',
            'admin.payment.index',
            'admin.payment.show',
            'admin.payment.problems',
            'admin.payment.invoices',
            'admin.payment.export',
        ];

        foreach ($expected as $name) {
            $route = $routes->getByName($name);
            $this->assertNotNull($route, "Route [{$name}] should be registered");
        }
    }

    public function test_qr_code_routes_are_registered(): void
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();

        $expected = [
            'qr-code.payment',
            'qr-code.payment-url',
            'qr-code.payment-data',
            'qr-code.invoice',
            'qr-code.refund',
            'qr-code.receipt',
            'qr-code.custom',
            'qr-code.with-logo',
            'qr-code.styled',
            'qr-code.batch',
            'qr-code.validate',
            'qr-code.stats',
            'qr-code.cleanup',
        ];

        foreach ($expected as $name) {
            $route = $routes->getByName($name);
            $this->assertNotNull($route, "Route [{$name}] should be registered");
        }
    }

    public function test_generate_custom_qr_code_endpoint(): void
    {
        $this->app['config']->set('hadi-payment.qr_code', [
            'storage_path' => 'qr-codes-test',
            'size' => 200,
            'format' => 'svg',
        ]);

        $response = $this->postJson('/qr-code/custom', ['data' => 'hello-world']);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.data', 'hello-world');
        $this->assertNotEmpty($response->json('data.qr_code'));

        $missing = $this->postJson('/qr-code/custom', []);
        $missing->assertStatus(400);
    }

    public function test_payment_show_view_and_route_exist(): void
    {
        $this->assertTrue(
            view()->exists('hadi-payment::payment-show'),
            'payment-show view should exist'
        );

        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('payment.show');
        $this->assertNotNull($route, 'payment.show route should be registered');
    }

    public function test_middleware_aliases_are_registered(): void
    {
        $aliases = $this->app['router']->getMiddleware();

        $this->assertSame(
            \Hadi\Payment\Http\Middleware\PaymentSecurityMiddleware::class,
            $aliases['payment.security']
        );
        $this->assertSame(
            \Hadi\Payment\Http\Middleware\RateLimitMiddleware::class,
            $aliases['payment.rate-limit']
        );
        $this->assertSame(
            \Hadi\Payment\Http\Middleware\WebhookSignatureMiddleware::class,
            $aliases['payment.webhook-signature']
        );
        $this->assertSame(
            \Hadi\Payment\Http\Middleware\PaymentGatewayMiddleware::class,
            $aliases['payment.gateway']
        );
    }

    public function test_gateway_and_webhook_routes_apply_middleware(): void
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();

        $webhook = $routes->getByName('payment.webhook');
        $this->assertNotNull($webhook, 'payment.webhook route should be registered');
        $this->assertContains('payment.webhook-signature', $webhook->middleware());
        $this->assertContains('payment.rate-limit:60,1', $webhook->middleware());

        $callback = $routes->getByName('payment.callback');
        $this->assertNotNull($callback, 'payment.callback route should be registered');
        $this->assertContains('payment.gateway', $callback->middleware());
    }

    public function test_payment_event_can_be_dispatched(): void
    {
        $payment = new Payment();
        $payment->id = 1;
        $payment->transaction_id = 'TXN-001';
        $payment->gateway = 'bkash';
        $payment->amount = 100.00;
        $payment->currency = 'BDT';
        $payment->status = 'pending';

        Event::fake();

        Event::dispatch(new PaymentInitialized($payment, ['source' => 'test']));

        Event::assertDispatched(PaymentInitialized::class);
    }
}
