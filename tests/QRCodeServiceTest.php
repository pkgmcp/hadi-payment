<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Models\Payment;
use Hadi\Payment\Services\QRCodeService;

class QRCodeServiceTest extends TestCase
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

        $app['config']->set('hadi-payment.qr_code', [
            'storage_path' => 'qr-codes-test',
            'size' => 200,
            'format' => 'svg',
        ]);
    }

    private function makePayment(): Payment
    {
        return Payment::create([
            'order_id' => 'ORD-QR-' . uniqid(),
            'reference_id' => 'REF-QR-' . uniqid(),
            'gateway' => 'bkash',
            'transaction_id' => 'TXN-QR-' . uniqid(),
            'amount' => 250,
            'currency' => 'BDT',
            'status' => Payment::STATUS_PENDING,
            'customer_data' => ['name' => 'Test User', 'email' => 'test@example.com'],
        ]);
    }

    public function test_generate_payment_qr_returns_svg(): void
    {
        $service = new QRCodeService();
        $payment = $this->makePayment();

        $qr = $service->generatePaymentQRCode($payment);

        $this->assertStringContainsString('<svg', $qr);
    }

    public function test_generate_payment_url_qr_returns_svg(): void
    {
        $service = new QRCodeService();

        $qr = $service->generatePaymentURLQRCode('https://example.com/pay/123');

        $this->assertStringContainsString('<svg', $qr);
    }

    public function test_generate_invoice_and_refund_qr(): void
    {
        $service = new QRCodeService();
        $payment = $this->makePayment();

        $invoiceQr = $service->generateInvoiceQRCode($payment);
        $this->assertStringContainsString('<svg', $invoiceQr);

        $refundQr = $service->generateRefundQRCode($payment, 50, 'Partial refund');
        $this->assertStringContainsString('<svg', $refundQr);
    }

    public function test_generate_custom_and_styled_qr(): void
    {
        $service = new QRCodeService();

        $custom = $service->generateCustomQRCode('custom-data');
        $this->assertStringContainsString('<svg', $custom);

        $styled = $service->generateStyledQRCode('data', ['color' => [255, 0, 0]]);
        $this->assertStringContainsString('<svg', $styled);
    }

    public function test_validate_qr_code_data(): void
    {
        $service = new QRCodeService();

        $this->assertFalse($service->validateQRCodeData(''));
        $this->assertTrue($service->validateQRCodeData('{"payment_id": 1}'));
        $this->assertTrue($service->validateQRCodeData('https://example.com'));
        $this->assertTrue($service->validateQRCodeData('anything'));
    }

    public function test_generate_batch_qr_codes(): void
    {
        $service = new QRCodeService();
        $payment = $this->makePayment();

        $codes = $service->generateBatchQRCodes([$payment]);

        $this->assertArrayHasKey($payment->id, $codes);
        $this->assertStringContainsString('<svg', $codes[$payment->id]);
    }

    public function test_get_qr_code_stats(): void
    {
        $service = new QRCodeService();

        $stats = $service->getQRCodeStats();

        $this->assertArrayHasKey('total_qr_codes', $stats);
        $this->assertArrayHasKey('storage_path', $stats);
        $this->assertArrayHasKey('storage_size', $stats);
    }
}
