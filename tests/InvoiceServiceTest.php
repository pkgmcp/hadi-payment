<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Models\Invoice;
use Hadi\Payment\Models\InvoiceItem;
use Hadi\Payment\Models\Payment;
use Hadi\Payment\Services\InvoiceService;

class InvoiceServiceTest extends TestCase
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

    private function makeService(): InvoiceService
    {
        return $this->app->make(InvoiceService::class);
    }

    private function makeInvoice(): Invoice
    {
        $payment = Payment::create([
            'transaction_id' => 'TXN-INV-001',
            'user_id' => 1,
            'order_id' => 'ORD-001',
            'gateway' => 'bkash',
            'amount' => 150.00,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        return $this->makeService()->generateInvoice($payment, [
            'tax_amount' => 15.00,
            'notes' => 'Thank you for your purchase',
        ]);
    }

    public function test_generate_invoice_pdf_returns_valid_pdf(): void
    {
        $invoice = $this->makeInvoice();

        $pdf = $this->makeService()->generateInvoicePdf($invoice);

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringEndsWith('%%EOF', trim($pdf));
        $this->assertStringContainsString('INVOICE ' . $invoice->invoice_number, $pdf);
        $this->assertStringContainsString('Thank you for your purchase', $pdf);
    }

    public function test_invoice_pdf_includes_item_and_totals(): void
    {
        $invoice = $this->makeInvoice();

        $this->makeService()->addInvoiceItem($invoice, [
            'description' => 'Test item',
            'quantity' => 2,
            'unit_price' => 50.00,
            'tax_rate' => 10,
            'discount_rate' => 0,
        ]);

        $this->makeService()->updateInvoiceTotals($invoice);

        $pdf = $this->makeService()->generateInvoicePdf($invoice);

        $this->assertStringContainsString('Test item', $pdf);
        $this->assertStringContainsString('Subtotal:', $pdf);
        $this->assertStringContainsString('Total:', $pdf);
        $this->assertStringContainsString('BDT', $pdf);
    }

    public function test_invoice_pdf_works_without_items(): void
    {
        $payment = Payment::create([
            'transaction_id' => 'TXN-INV-003',
            'user_id' => 1,
            'order_id' => 'ORD-003',
            'gateway' => 'sslcommerz',
            'amount' => 0.00,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $invoice = $this->makeService()->generateInvoice($payment);

        $pdf = $this->makeService()->generateInvoicePdf($invoice);

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('USD', $pdf);
    }

    public function test_invoice_items_persist_line_totals(): void
    {
        $invoice = $this->makeInvoice();

        $item = $this->makeService()->addInvoiceItem($invoice, [
            'description' => 'Line item',
            'quantity' => 3,
            'unit_price' => 10.00,
            'tax_rate' => 5,
            'discount_rate' => 0,
        ]);

        $this->assertInstanceOf(InvoiceItem::class, $item);
        $this->assertSame(30.00, (float) $item->line_total);
        $this->assertSame(1.50, (float) $item->tax_amount);
    }
}
