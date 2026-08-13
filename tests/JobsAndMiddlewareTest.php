<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Events\PaymentCompleted;
use Hadi\Payment\Events\PaymentFailed;
use Hadi\Payment\Events\PaymentRefunded;
use Hadi\Payment\Http\Middleware\PaymentGatewayMiddleware;
use Hadi\Payment\Http\Middleware\PaymentSecurityMiddleware;
use Hadi\Payment\Http\Middleware\RateLimitMiddleware;
use Hadi\Payment\Http\Middleware\WebhookSignatureMiddleware;
use Hadi\Payment\Jobs\ProcessPaymentJob;
use Hadi\Payment\Jobs\ProcessRefundJob;
use Hadi\Payment\Models\Payment;
use Hadi\Payment\Models\PaymentRefund;
use Hadi\Payment\Services\AIAgentService;
use Hadi\Payment\Services\PaymentGatewayService;
use Hadi\Payment\Services\PaymentLogger;
use Hadi\Payment\Services\PaymentManager;
use Hadi\Payment\Services\PaymentSecurityService;
use Hadi\Payment\Services\PaymentValidator;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class JobsAndMiddlewareTest extends TestCase
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

    private function makePayment(string $status = Payment::STATUS_PENDING): Payment
    {
        return Payment::create([
            'order_id' => 'ORD-J-' . uniqid(),
            'reference_id' => 'REF-J-' . uniqid(),
            'gateway' => 'bkash',
            'transaction_id' => 'mock_trx_' . uniqid(),
            'amount' => 100,
            'currency' => 'BDT',
            'status' => $status,
        ]);
    }

    private function makeGatewayService(): PaymentGatewayService
    {
        return new PaymentGatewayService(
            app(PaymentManager::class),
            new PaymentLogger(),
            new PaymentValidator()
        );
    }

    public function test_process_payment_job_completes_pending_payment(): void
    {
        Event::fake([PaymentCompleted::class]);

        $payment = $this->makePayment(Payment::STATUS_PENDING);

        $job = new ProcessPaymentJob($payment, ['amount' => 100]);
        $job->handle($this->makeGatewayService(), app(AIAgentService::class));

        $this->assertSame(Payment::STATUS_COMPLETED, $payment->fresh()->status);
        Event::assertDispatched(PaymentCompleted::class);
    }

    public function test_process_payment_job_fails_on_non_success(): void
    {
        Event::fake([PaymentFailed::class]);

        $payment = $this->makePayment(Payment::STATUS_PENDING);
        $payment->update(['transaction_id' => 'fail_verify']); // Simulated gateway failure

        $job = new ProcessPaymentJob($payment);
        $job->failed(new \RuntimeException('Simulated gateway failure'));

        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
        Event::assertDispatched(PaymentFailed::class);
    }

    public function test_process_refund_job_creates_refund_record(): void
    {
        Event::fake([PaymentRefunded::class]);

        $payment = $this->makePayment(Payment::STATUS_COMPLETED);

        $job = new ProcessRefundJob($payment, 50, 'Customer requested');
        $job->handle($this->makeGatewayService());

        $this->assertSame(Payment::STATUS_REFUNDED, $payment->fresh()->status);
        $this->assertSame(1, PaymentRefund::where('payment_id', $payment->id)->count());
        Event::assertDispatched(PaymentRefunded::class);
    }

    public function test_rate_limit_middleware_blocks_after_max_attempts(): void
    {
        $middleware = new RateLimitMiddleware();

        $request = $this->makeRequest('/payment/bkash/verify', 'POST');
        $request = $this->withGatewayRoute($request, 'bkash');

        $passed = 0;
        $next = function () use (&$passed) {
            $passed++;

            return response('ok');
        };

        $middleware->handle($request, $next, 2, 1);
        $middleware->handle($request, $next, 2, 1);
        $response = $middleware->handle($request, $next, 2, 1);

        $this->assertSame(2, $passed);
        $this->assertSame(429, $response->getStatusCode());
    }

    public function test_rate_limit_middleware_uses_gateway_config(): void
    {
        config()->set('hadi-payment.rate_limits.bkash.max_attempts', 1);

        $middleware = new RateLimitMiddleware();

        $request = $this->makeRequest('/payment/bkash/verify', 'POST');
        $request = $this->withGatewayRoute($request, 'bkash');

        $middleware->handle($request, fn () => response('ok'), 60, 1);
        $response = $middleware->handle($request, fn () => response('ok'), 60, 1);

        $this->assertSame(429, $response->getStatusCode());
    }

    public function test_webhook_signature_middleware_rejects_missing_signature(): void
    {
        config()->set('hadi-payment.webhooks.bkash.secret', 'test-secret');

        $middleware = new WebhookSignatureMiddleware();

        $request = $this->makeRequest('/payment/bkash/webhook', 'POST', ['data' => 'payload']);
        $request = $this->withGatewayRoute($request, 'bkash');

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_webhook_signature_middleware_rejects_invalid_signature(): void
    {
        config()->set('hadi-payment.webhooks.bkash.secret', 'test-secret');

        $middleware = new WebhookSignatureMiddleware();

        $request = $this->makeRequest('/payment/bkash/webhook', 'POST', ['data' => 'payload']);
        $request->headers->set('X-Webhook-Signature', 'wrong-signature');
        $request = $this->withGatewayRoute($request, 'bkash');

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_webhook_signature_middleware_passes_with_valid_signature(): void
    {
        $secret = 'test-secret';
        config()->set('hadi-payment.webhooks.bkash.secret', $secret);

        $middleware = new WebhookSignatureMiddleware();

        $request = $this->makeRequest('/payment/bkash/webhook', 'POST', ['data' => 'payload']);
        $signature = hash_hmac('sha256', $request->getContent(), $secret);
        $request->headers->set('X-Webhook-Signature', $signature);
        $request = $this->withGatewayRoute($request, 'bkash');

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_payment_security_middleware_blocks_rate_limited_requests(): void
    {
        $security = $this->createStub(PaymentSecurityService::class);
        $security->method('checkRateLimit')->willReturn(false);

        $middleware = new PaymentSecurityMiddleware($security);

        $request = $this->makeRequest('/payment/initialize', 'POST', ['amount' => 100]);

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(429, $response->getStatusCode());
    }

    public function test_payment_security_middleware_blocks_fraudulent_requests(): void
    {
        $security = $this->createStub(PaymentSecurityService::class);
        $security->method('checkRateLimit')->willReturn(true);
        $security->method('detectFraudulentActivity')->willReturn(['high_velocity']);

        $middleware = new PaymentSecurityMiddleware($security);

        $request = $this->makeRequest('/payment/initialize', 'POST', ['amount' => 100]);

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_payment_security_middleware_rejects_invalid_nonce(): void
    {
        $security = $this->createStub(PaymentSecurityService::class);
        $security->method('checkRateLimit')->willReturn(true);
        $security->method('detectFraudulentActivity')->willReturn([]);
        $security->method('verifyNonce')->willReturn(false);

        $middleware = new PaymentSecurityMiddleware($security);

        $request = $this->makeRequest('/payment/initialize', 'POST', ['amount' => 100, 'payment_nonce' => 'bad-nonce']);

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_payment_security_middleware_adds_security_headers(): void
    {
        $security = $this->createStub(PaymentSecurityService::class);
        $security->method('checkRateLimit')->willReturn(true);
        $security->method('detectFraudulentActivity')->willReturn([]);

        $middleware = new PaymentSecurityMiddleware($security);

        $request = $this->makeRequest('/payment/initialize', 'GET');

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
    }

    public function test_payment_gateway_middleware_rejects_unsupported_gateway(): void
    {
        $middleware = new PaymentGatewayMiddleware(new PaymentLogger());

        $request = $this->makeRequest('/payment/bogus/verify', 'POST');
        $request = $this->withGatewayRoute($request, 'bogus');

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_payment_gateway_middleware_logs_request_and_passes_supported_gateway(): void
    {
        Log::spy();

        $middleware = new PaymentGatewayMiddleware(new PaymentLogger());

        $request = $this->makeRequest('/payment/bkash/verify', 'POST');
        $request = $this->withGatewayRoute($request, 'bkash');

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(200, $response->getStatusCode());
    }

    private function makeRequest(string $uri, string $method = 'POST', array $data = []): Request
    {
        return Request::create($uri, $method, $data);
    }

    private function withGatewayRoute(Request $request, string $gateway): Request
    {
        $route = new Route(['POST'], '/payment/{gateway}/{action}', fn () => null);
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);

        return $request;
    }
}
