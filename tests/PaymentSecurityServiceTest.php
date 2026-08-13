<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Models\Payment;
use Hadi\Payment\Services\PaymentSecurityService;
use Illuminate\Support\Facades\Crypt;

class PaymentSecurityServiceTest extends TestCase
{
    private PaymentSecurityService $service;

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

        $app['config']->set('hadi-payment.security', [
            'hash_secret' => 'test-secret',
            'webhook_secret' => 'test-webhook',
            'max_amount' => 10000,
            'min_amount' => 0.01,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PaymentSecurityService();
    }

    public function test_encrypt_and_decrypt_payment_data(): void
    {
        $data = [
            'order_id' => 'ORD-1',
            'card_number' => '4111111111111111',
            'cvv' => '123',
            'safe_field' => 'visible',
        ];

        $encrypted = $this->service->encryptPaymentData($data);

        $this->assertNotSame($data['card_number'], $encrypted['card_number']);
        $this->assertSame('visible', $encrypted['safe_field']);
        $this->assertSame('ORD-1', $encrypted['order_id']);

        $decrypted = $this->service->decryptPaymentData($encrypted);

        $this->assertSame('4111111111111111', $decrypted['card_number']);
        $this->assertSame('123', $decrypted['cvv']);
        $this->assertSame('visible', $decrypted['safe_field']);
    }

    public function test_payment_hash_is_stable_and_verifiable(): void
    {
        $data = ['amount' => 100, 'currency' => 'BDT', 'order_id' => 'ORD-1'];

        $hash = $this->service->generatePaymentHash($data, 'secret');

        $this->assertSame($hash, $this->service->generatePaymentHash($data, 'secret'));
        $this->assertTrue($this->service->verifyPaymentHash($data, $hash, 'secret'));
        $this->assertFalse($this->service->verifyPaymentHash(['amount' => 999, 'currency' => 'BDT', 'order_id' => 'ORD-1'], $hash, 'secret'));
    }

    public function test_hash_excludes_sensitive_fields(): void
    {
        $withSecret = $this->service->generatePaymentHash(['amount' => 10, 'card_number' => '1234'], 's');
        $withoutSecret = $this->service->generatePaymentHash(['amount' => 10, 'card_number' => '9999'], 's');

        $this->assertSame($withSecret, $withoutSecret);
    }

    public function test_secure_ids_generated(): void
    {
        $transactionId = $this->service->generateSecureTransactionId();
        $referenceId = $this->service->generateSecureReferenceId();

        $this->assertStringStartsWith('TXN_', $transactionId);
        $this->assertStringStartsWith('REF_', $referenceId);
        $this->assertNotSame($transactionId, $this->service->generateSecureTransactionId());
    }

    public function test_webhook_signature_round_trip(): void
    {
        $signature = $this->service->generateWebhookSignature('{"status":"completed"}', 'secret');

        $this->assertTrue($this->service->verifyWebhookSignature('{"status":"completed"}', $signature, 'secret'));
        $this->assertFalse($this->service->verifyWebhookSignature('{"status":"failed"}', $signature, 'secret'));
    }

    public function test_sanitize_for_logging_masks_sensitive_fields(): void
    {
        $sanitized = $this->service->sanitizeForLogging([
            'card_number' => '4111111111111111',
            'email' => 'user@example.com',
            'order_id' => 'ORD-1',
        ]);

        $this->assertSame('41**************', $sanitized['card_number']);
        $this->assertStringNotContainsString('@example.com', $sanitized['email']);
        $this->assertSame('ORD-1', $sanitized['order_id']);
    }

    public function test_rate_limit_blocks_after_max_attempts(): void
    {
        $this->assertTrue($this->service->checkRateLimit('user-1', 2, 15));
        $this->assertTrue($this->service->checkRateLimit('user-1', 2, 15));
        $this->assertFalse($this->service->checkRateLimit('user-1', 2, 15));
    }

    public function test_detect_fraudulent_activity(): void
    {
        $indicators = $this->service->detectFraudulentActivity('8.8.8.8', ['amount' => 100000]);

        $this->assertContains('unusual_amount', $indicators);
    }

    public function test_nonce_store_and_verify(): void
    {
        $nonce = $this->service->generateNonce();
        $this->assertSame(32, strlen($nonce));

        $this->assertFalse($this->service->verifyNonce($nonce));

        $this->service->storeNonce($nonce);
        $this->assertTrue($this->service->verifyNonce($nonce));
        $this->assertFalse($this->service->verifyNonce($nonce));
    }

    public function test_payment_integrity_validation(): void
    {
        $payment = Payment::create([
            'order_id' => 'ORD-1',
            'gateway' => 'bkash',
            'amount' => 100,
            'currency' => 'BDT',
            'status' => Payment::STATUS_PENDING,
        ]);

        $hash = $this->service->generatePaymentHash([
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'reference_id' => $payment->reference_id,
            'gateway' => $payment->gateway,
        ]);

        $this->assertTrue($this->service->validatePaymentIntegrity($payment, [
            'payment_hash' => $hash,
            'amount' => 100,
            'currency' => 'BDT',
        ]));

        $this->assertFalse($this->service->validatePaymentIntegrity($payment, [
            'amount' => 999,
            'currency' => 'BDT',
        ]));

        $this->assertFalse($this->service->validatePaymentIntegrity($payment, [
            'amount' => 100,
            'currency' => 'USD',
        ]));
    }
}
