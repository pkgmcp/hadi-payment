<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Models\Payment;
use Hadi\Payment\Models\PaymentHistory;
use Hadi\Payment\Models\PaymentProblem;
use Hadi\Payment\Services\PaymentHistoryService;

class PaymentHistoryServiceTest extends TestCase
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

    private function makePayment(): Payment
    {
        return Payment::create([
            'order_id' => 'ORD-H-1',
            'reference_id' => 'REF-H-1',
            'gateway' => 'bkash',
            'transaction_id' => 'TXN-H-1',
            'amount' => 200,
            'currency' => 'BDT',
            'status' => Payment::STATUS_PENDING,
        ]);
    }

    public function test_log_action_creates_history_entry(): void
    {
        $payment = $this->makePayment();
        $service = new PaymentHistoryService();

        $history = $service->logAction($payment, PaymentHistory::ACTION_CREATED, null, 'pending');

        $this->assertInstanceOf(PaymentHistory::class, $history);
        $this->assertSame($payment->id, $history->payment_id);
        $this->assertSame(PaymentHistory::ACTION_CREATED, $history->action);
        $this->assertSame('200.00', $history->amount);
        $this->assertSame('BDT', $history->currency);
    }

    public function test_convenience_logging_methods(): void
    {
        $payment = $this->makePayment();
        $service = new PaymentHistoryService();

        $service->logPaymentCreated($payment);
        $service->logPaymentInitialized($payment, ['status' => '0010001']);
        $service->logPaymentCompleted($payment);
        $service->logPaymentFailed($payment, [], 'Gateway timeout');
        $service->logPaymentRefunded($payment, 100, 'Partial');
        $service->logWebhookReceived($payment, ['event' => 'charge.success']);
        $service->logCallbackReceived($payment, ['status' => 'success']);
        $service->logAdminUpdate($payment, 'Manually verified');

        $this->assertSame(8, $payment->histories()->count());
        $this->assertSame(
            Payment::STATUS_COMPLETED,
            $payment->histories()->where('action', PaymentHistory::ACTION_COMPLETED)->first()->status_to
        );
        $this->assertSame(
            Payment::STATUS_FAILED,
            $payment->histories()->where('action', PaymentHistory::ACTION_FAILED)->first()->status_to
        );
    }

    public function test_report_and_resolve_problem(): void
    {
        $payment = $this->makePayment();
        $service = new PaymentHistoryService();

        $problem = $service->reportProblem(
            $payment,
            'payment_stuck',
            'Payment stuck in pending',
            'Payment has been pending for over an hour'
        );

        $this->assertInstanceOf(PaymentProblem::class, $problem);
        $this->assertSame(PaymentProblem::STATUS_OPEN, $problem->status);
        $this->assertSame($payment->id, $problem->payment_id);
        $this->assertSame(PaymentHistory::ACTION_PROBLEM_REPORTED, $payment->histories()->latest('id')->first()->action);

        $service->resolveProblem($problem, 'Gateway confirmed success');

        $this->assertSame(PaymentProblem::STATUS_RESOLVED, $problem->fresh()->status);
        $this->assertSame(PaymentHistory::ACTION_PROBLEM_RESOLVED, $payment->histories()->latest('id')->first()->action);
    }

    public function test_history_and_problem_queries(): void
    {
        $payment = $this->makePayment();
        $service = new PaymentHistoryService();

        $service->logPaymentCreated($payment);
        $service->reportProblem($payment, 'chargeback', 'Chargeback filed', 'Bank dispute received');

        $history = $service->getPaymentHistory($payment);
        $problems = $service->getPaymentProblems($payment);

        $this->assertCount(2, $history);
        $this->assertCount(1, $problems);
    }

    public function test_payment_statistics(): void
    {
        $payment = $this->makePayment();
        Payment::create([
            'order_id' => 'ORD-H-2',
            'gateway' => 'nagad',
            'transaction_id' => 'TXN-H-2',
            'amount' => 50,
            'currency' => 'BDT',
            'status' => Payment::STATUS_COMPLETED,
        ]);
        Payment::create([
            'order_id' => 'ORD-H-3',
            'gateway' => 'rocket',
            'transaction_id' => 'TXN-H-3',
            'amount' => 75,
            'currency' => 'BDT',
            'status' => Payment::STATUS_FAILED,
        ]);

        $service = new PaymentHistoryService();
        $stats = $service->getPaymentStatistics(['gateway' => 'nagad']);

        $this->assertSame(1, $stats['total_payments']);
        $this->assertSame(1, $stats['completed_payments']);

        $all = $service->getPaymentStatistics();
        $this->assertSame(3, $all['total_payments']);
        $this->assertSame(1, $all['failed_payments']);
        $this->assertSame(1, $all['pending_payments']);
    }

    public function test_problem_statistics(): void
    {
        $payment = $this->makePayment();
        $service = new PaymentHistoryService();

        $service->reportProblem($payment, 'payment_stuck', 'Stuck', 'desc', PaymentProblem::SEVERITY_CRITICAL, PaymentProblem::PRIORITY_URGENT);
        $service->reportProblem($payment, 'duplicate', 'Duplicate', 'desc', PaymentProblem::SEVERITY_LOW);

        $stats = $service->getProblemStatistics();

        $this->assertSame(2, $stats['total_problems']);
        $this->assertSame(2, $stats['open_problems']);
        $this->assertSame(1, $stats['critical_problems']);
        $this->assertSame(1, $stats['urgent_problems']);
    }
}
