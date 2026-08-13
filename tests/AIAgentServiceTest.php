<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Models\Payment;
use Hadi\Payment\Services\AIAgentService;

class AIAgentServiceTest extends TestCase
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

    private function makePayment(array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'order_id' => 'ORD-AI-' . uniqid(),
            'gateway' => 'bkash',
            'transaction_id' => 'TXN-AI-' . uniqid(),
            'amount' => 500,
            'currency' => 'BDT',
            'status' => Payment::STATUS_COMPLETED,
        ], $overrides));
    }

    public function test_analyze_payment_patterns_returns_structure(): void
    {
        $service = new AIAgentService();
        $payment = $this->makePayment(['amount' => 10000]);

        $analysis = $service->analyzePaymentPatterns($payment);

        $this->assertArrayHasKey('risk_score', $analysis);
        $this->assertArrayHasKey('anomalies', $analysis);
        $this->assertArrayHasKey('recommendations', $analysis);
        $this->assertArrayHasKey('fraud_indicators', $analysis);
        $this->assertIsInt($analysis['risk_score']);
    }

    public function test_analyze_detects_round_number_anomaly(): void
    {
        $service = new AIAgentService();
        $payment = $this->makePayment(['amount' => 100]);

        $analysis = $service->analyzePaymentPatterns($payment);

        $this->assertContains('round_number_amount', $analysis['anomalies']);
    }

    public function test_generate_notifications_does_not_crash(): void
    {
        $service = new AIAgentService();
        $payment = $this->makePayment();

        $analysis = $service->analyzePaymentPatterns($payment);
        $analysis['risk_score'] = 90;

        $service->generateNotifications($payment, $analysis);

        $this->assertTrue(true);
    }

    public function test_provide_customer_support_returns_structure(): void
    {
        $service = new AIAgentService();

        $response = $service->provideCustomerSupport('My payment failed, what should I do?');

        $this->assertArrayHasKey('suggestions', $response);
        $this->assertArrayHasKey('automated_responses', $response);
        $this->assertArrayHasKey('escalation_required', $response);
        $this->assertArrayHasKey('confidence_score', $response);
        $this->assertIsBool($response['escalation_required']);
    }

    public function test_generate_insights_returns_structure(): void
    {
        $service = new AIAgentService();
        $this->makePayment(['status' => Payment::STATUS_COMPLETED]);
        $this->makePayment(['status' => Payment::STATUS_FAILED]);

        $insights = $service->generateInsights();

        $this->assertArrayHasKey('payment_trends', $insights);
        $this->assertArrayHasKey('gateway_performance', $insights);
        $this->assertArrayHasKey('fraud_patterns', $insights);
        $this->assertArrayHasKey('recommendations', $insights);
        $this->assertSame(2, $insights['payment_trends']['total_payments']);
    }

    public function test_suggest_optimal_gateway_returns_supported_gateway(): void
    {
        $service = new AIAgentService();
        $this->makePayment(['gateway' => 'bkash', 'status' => Payment::STATUS_COMPLETED]);

        $gateway = $service->suggestOptimalGateway(['amount' => 100, 'currency' => 'BDT', 'user_id' => 1]);

        $this->assertIsString($gateway);
        $this->assertNotEmpty($gateway);
    }

    public function test_predict_payment_failure_returns_structure(): void
    {
        $service = new AIAgentService();
        $payment = $this->makePayment();

        $prediction = $service->predictPaymentFailure($payment);

        $this->assertArrayHasKey('failure_probability', $prediction);
        $this->assertArrayHasKey('risk_factors', $prediction);
        $this->assertArrayHasKey('mitigation_strategies', $prediction);
        $this->assertIsNumeric($prediction['failure_probability']);
    }

    public function test_recommend_refund_strategy_returns_structure(): void
    {
        $service = new AIAgentService();
        $payment = $this->makePayment(['status' => Payment::STATUS_FAILED]);

        $recommendation = $service->recommendRefundStrategy($payment);

        $this->assertArrayHasKey('should_refund', $recommendation);
        $this->assertArrayHasKey('refund_amount', $recommendation);
        $this->assertArrayHasKey('refund_reason', $recommendation);
        $this->assertArrayHasKey('processing_time', $recommendation);
        $this->assertIsBool($recommendation['should_refund']);
    }
}
