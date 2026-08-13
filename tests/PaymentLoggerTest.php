<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\PaymentResponse;
use Hadi\Payment\Services\PaymentLogger;

class PaymentLoggerTest extends TestCase
{
    public function test_log_returns_success_entry(): void
    {
        $logger = new PaymentLogger();
        $response = new PaymentResponse(
            success: true,
            message: 'Payment initialized',
            paymentId: 'PAY-1',
            transactionId: 'TXN-1',
            status: 'pending_user_action',
            httpCode: 200
        );

        $logger->log('initialize_payment', 'bkash', ['amount' => 100], $response);

        $logs = $logger->getLogs();
        $this->assertCount(1, $logs);
        $this->assertSame('initialize_payment', $logs[0]['operation']);
        $this->assertSame('bkash', $logs[0]['gateway']);
        $this->assertTrue($logs[0]['success']);
        $this->assertSame('PAY-1', $logs[0]['payment_id']);
    }

    public function test_log_redacts_sensitive_data(): void
    {
        $logger = new PaymentLogger();
        $response = new PaymentResponse(success: true, message: 'ok');

        $logger->log('initialize_payment', 'stripe', [
            'secret_key' => 'sk_test_12345',
            'token' => 'tok_secret',
            'safe_field' => 'visible',
        ], $response);

        $logs = $logger->getLogs();
        $this->assertSame('***REDACTED***', $logs[0]['data']['secret_key']);
        $this->assertSame('***REDACTED***', $logs[0]['data']['token']);
        $this->assertSame('visible', $logs[0]['data']['safe_field']);
    }

    public function test_disabled_logger_stores_nothing(): void
    {
        $logger = new PaymentLogger(false);
        $response = new PaymentResponse(success: true, message: 'ok');

        $logger->log('initialize_payment', 'bkash', [], $response);

        $this->assertSame([], $logger->getLogs());
        $this->assertFalse($logger->isEnabled());

        $logger->enable();
        $this->assertTrue($logger->isEnabled());
    }

    public function test_log_filtering_by_gateway_and_operation(): void
    {
        $logger = new PaymentLogger();
        $response = new PaymentResponse(success: true, message: 'ok');

        $logger->logPaymentInitialization('bkash', ['amount' => 10], $response);
        $logger->logPaymentVerification('stripe', 'PAY-9', new PaymentResponse(success: true, message: 'ok', paymentId: 'PAY-9'));

        $this->assertCount(1, $logger->getLogsByGateway('bkash'));
        $this->assertCount(1, $logger->getLogsByGateway('stripe'));
        $this->assertCount(1, $logger->getLogsByOperation('initialize_payment'));
        $this->assertCount(1, $logger->getLogsByOperation('verify_payment'));
        $this->assertCount(1, $logger->getLogsByPaymentId('PAY-9'));
    }

    public function test_logs_export_and_clear(): void
    {
        $logger = new PaymentLogger();
        $logger->logPaymentInitialization('bkash', [], new PaymentResponse(success: true, message: 'ok'));

        $json = $logger->getLogsAsJson();
        $this->assertJson($json);

        $path = sys_get_temp_dir() . '/payment-logs-' . uniqid() . '.json';
        $this->assertTrue($logger->exportToFile($path));
        $this->assertFileExists($path);

        $logger->clearLogs();
        $this->assertSame([], $logger->getLogs());
    }
}
