<?php

declare(strict_types=1);

namespace Hadi\Payment;

use Illuminate\Support\ServiceProvider;
use Hadi\Payment\Cache\PaymentCache;
use Hadi\Payment\Factories\GatewayFactory;
use Hadi\Payment\Http\Middleware\PaymentGatewayMiddleware;
use Hadi\Payment\Http\Middleware\PaymentSecurityMiddleware;
use Hadi\Payment\Http\Middleware\RateLimitMiddleware;
use Hadi\Payment\Http\Middleware\WebhookSignatureMiddleware;
use Hadi\Payment\Listeners\PaymentEventListener;
use Hadi\Payment\Services\AIAgentService;
use Hadi\Payment\Services\CountryCatalog;
use Hadi\Payment\Services\InvoiceService;
use Hadi\Payment\Services\PaymentGatewayService;
use Hadi\Payment\Services\PaymentHistoryService;
use Hadi\Payment\Services\PaymentLogger;
use Hadi\Payment\Services\PaymentManager;
use Hadi\Payment\Services\PaymentRecordingService;
use Hadi\Payment\Services\PaymentSecurityService;
use Hadi\Payment\Services\PaymentValidator;
use Hadi\Payment\Services\QRCodeService;
use Hadi\Payment\Services\TransactionReportService;

class HadiPaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/hadi-payment.php', 'hadi-payment');

        $this->app->singleton(GatewayFactory::class, function ($app) {
            return new GatewayFactory();
        });

        $this->app->singleton(CountryCatalog::class, function ($app) {
            return new CountryCatalog();
        });

        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager($app->make(GatewayFactory::class));
        });

        $this->app->singleton(PaymentLogger::class, function ($app) {
            return new PaymentLogger(config('hadi-payment.logging.enabled', true));
        });

        $this->app->singleton(PaymentValidator::class, function ($app) {
            return new PaymentValidator();
        });

        $this->app->singleton(PaymentGatewayService::class, function ($app) {
            return new PaymentGatewayService(
                $app->make(PaymentManager::class),
                $app->make(PaymentLogger::class),
                $app->make(PaymentValidator::class),
                $app->make(PaymentRecordingService::class)
            );
        });

        $this->app->singleton(PaymentRecordingService::class, function ($app) {
            return new PaymentRecordingService();
        });

        $this->app->singleton(HadiPayment::class, function ($app) {
            return new HadiPayment(
                $app->make(GatewayFactory::class),
                $app->make(PaymentManager::class)
            );
        });

        $this->app->singleton(PaymentSecurityService::class, function ($app) {
            return new PaymentSecurityService();
        });

        $this->app->singleton(AIAgentService::class, function ($app) {
            return new AIAgentService();
        });

        $this->app->singleton(PaymentHistoryService::class, function ($app) {
            return new PaymentHistoryService();
        });

        $this->app->singleton(TransactionReportService::class, function ($app) {
            return new TransactionReportService($app->make(GatewayFactory::class));
        });

        $this->app->singleton(QRCodeService::class, function ($app) {
            return new QRCodeService();
        });

        $this->app->singleton(InvoiceService::class, function ($app) {
            return new InvoiceService();
        });

        $this->app->singleton(PaymentCache::class, function ($app) {
            return new PaymentCache();
        });

        $this->app->singleton(PaymentEventListener::class, function ($app) {
            return new PaymentEventListener(
                $app->make(PaymentLogger::class),
                $app->make(AIAgentService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/hadi-payment.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'hadi-payment');

        $this->app->make('events')->subscribe(PaymentEventListener::class);

        $this->app['router']->aliasMiddleware('payment.security', PaymentSecurityMiddleware::class);
        $this->app['router']->aliasMiddleware('payment.rate-limit', RateLimitMiddleware::class);
        $this->app['router']->aliasMiddleware('payment.webhook-signature', WebhookSignatureMiddleware::class);
        $this->app['router']->aliasMiddleware('payment.gateway', PaymentGatewayMiddleware::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/hadi-payment.php' => config_path('hadi-payment.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/hadi-payment'),
            ], 'views');
        }
    }
}
