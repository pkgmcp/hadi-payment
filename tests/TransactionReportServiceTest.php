<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Services\TransactionReportService;
use Hadi\Payment\Support\SimplePdfWriter;

class TransactionReportServiceTest extends TestCase
{
    private function makeService(): TransactionReportService
    {
        return $this->app->make(TransactionReportService::class);
    }

    private function sampleReport(): array
    {
        return [
            'summary' => [
                'total_payments' => 10,
                'total_amount' => 1250.50,
                'success_rate' => 80.5,
            ],
            'gateway_breakdown' => [
                'bkash' => ['count' => 6, 'amount' => 750.00],
                'nagad' => ['count' => 4, 'amount' => 500.50],
            ],
        ];
    }

    public function test_exports_json(): void
    {
        $service = $this->makeService();
        $exported = $service->exportReport($this->sampleReport(), 'json');

        $this->assertJson($exported);
        $decoded = json_decode($exported, true);
        $this->assertEquals(10, $decoded['summary']['total_payments']);
    }

    public function test_exports_csv(): void
    {
        $service = $this->makeService();
        $exported = $service->exportReport($this->sampleReport(), 'csv');

        $this->assertStringContainsString('Key,Value', $exported);
        $this->assertStringContainsString('summary.total_payments,10', $exported);
        $this->assertStringContainsString('gateway_breakdown.bkash.count,6', $exported);
    }

    public function test_exports_excel(): void
    {
        $service = $this->makeService();
        $exported = $service->exportReport($this->sampleReport(), 'excel');

        $this->assertStringContainsString('Workbook', $exported);
        $this->assertStringContainsString('summary.total_payments', $exported);
        $this->assertStringContainsString('10', $exported);
    }

    public function test_exports_pdf(): void
    {
        $service = $this->makeService();
        $exported = $service->exportReport($this->sampleReport(), 'pdf');

        $this->assertStringStartsWith('%PDF-1.4', $exported);
        $this->assertStringEndsWith('%%EOF', trim($exported));
        $this->assertStringContainsString('summary.total_payments: 10', $exported);
    }

    public function test_export_throws_for_unsupported_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->makeService()->exportReport($this->sampleReport(), 'xml');
    }

    public function test_pdf_writer_generates_valid_pdf_header(): void
    {
        $pdf = (new SimplePdfWriter())
            ->title('Test Report')
            ->addLine('Line one')
            ->addLine('Line (two)')
            ->output();

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('/Type /Catalog', $pdf);
        $this->assertStringContainsString('/BaseFont /Helvetica', $pdf);
        $this->assertStringContainsString('startxref', $pdf);
        $this->assertStringEndsWith('%%EOF', trim($pdf));
    }
}
