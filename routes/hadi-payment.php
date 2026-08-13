<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Hadi\Payment\Http\Controllers\PaymentController;
use Hadi\Payment\Http\Controllers\QRCodeController;
use Hadi\Payment\Http\Controllers\ReportController;
use Hadi\Payment\Http\Controllers\Admin\PaymentAdminController;

/*
|--------------------------------------------------------------------------
| Payment Gateway Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the payment gateway package. These routes handle
| payment initialization, verification, refunds, callbacks, and webhooks.
|
*/

Route::prefix('payment')->name('payment.')->group(function () {

    // Public routes
    Route::get('/form', [PaymentController::class, 'showPaymentForm'])->name('form');
    Route::post('/initialize', [PaymentController::class, 'initializePayment'])->name('initialize');

    // Gateway-specific routes
    Route::prefix('{gateway}')->whereAlphaNumeric('gateway')->middleware(['payment.gateway'])->group(function () {
        Route::post('/verify', [PaymentController::class, 'verifyPayment'])->name('verify');
        Route::post('/refund', [PaymentController::class, 'refundPayment'])->name('refund');
        Route::get('/status', [PaymentController::class, 'getPaymentStatus'])->name('status');
        Route::get('/callback', [PaymentController::class, 'handleCallback'])->name('callback');
        Route::post('/webhook', [PaymentController::class, 'handleWebhook'])
            ->middleware(['payment.webhook-signature', 'payment.rate-limit:60,1'])
            ->name('webhook');
    });

    // Result pages
    Route::get('/success', [PaymentController::class, 'showSuccess'])->name('success');
    Route::get('/failed', [PaymentController::class, 'showFailed'])->name('failed');
    Route::get('/{payment}', [PaymentController::class, 'showPayment'])->name('show');
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for programmatic access to payment gateway functionality.
| These routes are protected by the 'api' middleware group.
|
*/

Route::prefix('api/payment')->name('api.payment.')->middleware(['api'])->group(function () {

    Route::post('/initialize', [PaymentController::class, 'initializePayment'])->name('initialize');

    // Gateway-specific API routes
    Route::prefix('{gateway}')->whereAlphaNumeric('gateway')->middleware(['payment.gateway'])->group(function () {
        Route::post('/verify', [PaymentController::class, 'verifyPayment'])->name('verify');
        Route::post('/refund', [PaymentController::class, 'refundPayment'])->name('refund');
        Route::get('/status', [PaymentController::class, 'getPaymentStatus'])->name('status');
        Route::post('/webhook', [PaymentController::class, 'handleWebhook'])
            ->middleware(['payment.webhook-signature', 'payment.rate-limit:60,1'])
            ->name('webhook');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Admin routes for managing payments, logs, problems, invoices, and exports.
| These routes are protected by the 'admin' middleware group.
|
*/

Route::prefix('admin/payment')->name('admin.payment.')->middleware(['web', 'auth', 'admin'])->group(function () {

    // Payment management
    Route::get('/', [PaymentAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/payments', [PaymentAdminController::class, 'index'])->name('index');
    Route::get('/payments/{payment}', [PaymentAdminController::class, 'show'])->name('show');
    Route::post('/payments/{payment}/status', [PaymentAdminController::class, 'updateStatus'])->name('update-status');

    // Payment problems
    Route::get('/problems', [PaymentAdminController::class, 'problems'])->name('problems');
    Route::get('/problems/{problem}', [PaymentAdminController::class, 'showProblem'])->name('problems.show');
    Route::post('/problems/{problem}/assign', [PaymentAdminController::class, 'assignProblem'])->name('problems.assign');
    Route::post('/problems/{problem}/resolve', [PaymentAdminController::class, 'resolveProblem'])->name('problems.resolve');

    // Invoices
    Route::get('/invoices', [PaymentAdminController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}', [PaymentAdminController::class, 'showInvoice'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [PaymentAdminController::class, 'downloadInvoice'])->name('invoices.download');
    Route::post('/invoices/{invoice}/send', [PaymentAdminController::class, 'sendInvoice'])->name('invoices.send');

    // Exports
    Route::get('/export', [PaymentAdminController::class, 'exportPayments'])->name('export');
});

/*
|--------------------------------------------------------------------------
| QR Code Routes
|--------------------------------------------------------------------------
|
| QR Code generation routes for payments, invoices, receipts, and refunds.
| These routes handle QR code generation with various customization options.
|
*/

Route::prefix('qr-code')->name('qr-code.')->group(function () {
    Route::post('/payment', [QRCodeController::class, 'generatePaymentQRCode'])->name('payment');
    Route::post('/payment-url', [QRCodeController::class, 'generatePaymentURLQRCode'])->name('payment-url');
    Route::post('/payment-data', [QRCodeController::class, 'generatePaymentDataQRCode'])->name('payment-data');
    Route::post('/invoice', [QRCodeController::class, 'generateInvoiceQRCode'])->name('invoice');
    Route::post('/refund', [QRCodeController::class, 'generateRefundQRCode'])->name('refund');
    Route::post('/receipt', [QRCodeController::class, 'generateReceiptQRCode'])->name('receipt');
    Route::post('/custom', [QRCodeController::class, 'generateCustomQRCode'])->name('custom');
    Route::post('/with-logo', [QRCodeController::class, 'generateQRCodeWithLogo'])->name('with-logo');
    Route::post('/styled', [QRCodeController::class, 'generateStyledQRCode'])->name('styled');
    Route::post('/batch', [QRCodeController::class, 'generateBatchQRCodes'])->name('batch');
    Route::post('/validate', [QRCodeController::class, 'validateQRCodeData'])->name('validate');
    Route::get('/stats', [QRCodeController::class, 'getQRCodeStats'])->name('stats');
    Route::post('/cleanup', [QRCodeController::class, 'cleanupOldQRCodes'])->name('cleanup');
});

/*
|--------------------------------------------------------------------------
| Report Routes
|--------------------------------------------------------------------------
|
| Comprehensive reporting routes for transaction analysis, financial reports,
| fraud analysis, and customer behavior insights.
|
*/

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/transaction', [ReportController::class, 'getTransactionReport'])->name('transaction');
    Route::get('/gateway-performance', [ReportController::class, 'getGatewayPerformanceReport'])->name('gateway-performance');
    Route::get('/financial', [ReportController::class, 'getFinancialReport'])->name('financial');
    Route::get('/fraud-analysis', [ReportController::class, 'getFraudAnalysisReport'])->name('fraud-analysis');
    Route::get('/customer-behavior', [ReportController::class, 'getCustomerBehaviorReport'])->name('customer-behavior');
    Route::get('/export', [ReportController::class, 'exportReport'])->name('export');
    Route::get('/dashboard', [ReportController::class, 'getDashboardData'])->name('dashboard');
});
