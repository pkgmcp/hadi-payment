<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Exceptions\ValidationException;
use Hadi\Payment\Services\PaymentValidator;

class PaymentValidatorTest extends TestCase
{
    private PaymentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new PaymentValidator();
    }

    public function test_valid_payment_data_passes(): void
    {
        $this->assertTrue($this->validator->validatePaymentData([
            'order_id' => 'ORD-123',
            'amount' => 150.50,
            'currency' => 'BDT',
            'callback_url' => 'https://example.com/callback',
        ]));
    }

    public function test_missing_order_id_throws(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Required field 'order_id' is missing or empty");

        $this->validator->validatePaymentData([
            'amount' => 100,
        ]);
    }

    public function test_non_positive_amount_throws(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Amount must be a positive number');

        $this->validator->validatePaymentData([
            'order_id' => 'ORD-1',
            'amount' => -5,
        ]);
    }

    public function test_overlong_order_id_throws(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Order ID must be 50 characters or less');

        $this->validator->validatePaymentData([
            'order_id' => str_repeat('x', 51),
            'amount' => 10,
        ]);
    }

    public function test_invalid_order_id_chars_throws(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Order ID contains invalid characters');

        $this->validator->validatePaymentData([
            'order_id' => 'ORD 123!',
            'amount' => 10,
        ]);
    }

    public function test_unsupported_currency_throws(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Currency must be one of');

        $this->validator->validatePaymentData([
            'order_id' => 'ORD-1',
            'amount' => 10,
            'currency' => 'XYZ',
        ]);
    }

    public function test_invalid_callback_url_throws(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid callback URL format');

        $this->validator->validatePaymentData([
            'order_id' => 'ORD-1',
            'amount' => 10,
            'callback_url' => 'not-a-url',
        ]);
    }

    public function test_payment_id_validation(): void
    {
        $this->assertTrue($this->validator->validatePaymentId('PAY-123'));

        $this->expectException(ValidationException::class);
        $this->validator->validatePaymentId('');
    }

    public function test_refund_data_validation(): void
    {
        $this->assertTrue($this->validator->validateRefundData('PAY-1', 25.00));

        $this->expectException(ValidationException::class);
        $this->validator->validateRefundData('PAY-1', 0);
    }

    public function test_sanitize_payment_data(): void
    {
        $sanitized = $this->validator->sanitizePaymentData([
            'order_id' => '  ORD-123  ',
            'amount' => '99.99',
            'currency' => 'usd',
            'callback_url' => ' https://example.com/cb ',
            'not_allowed' => 'dropped',
        ]);

        $this->assertSame('ORD-123', $sanitized['order_id']);
        $this->assertSame(99.99, $sanitized['amount']);
        $this->assertSame('USD', $sanitized['currency']);
        $this->assertSame('https://example.com/cb', $sanitized['callback_url']);
        $this->assertArrayNotHasKey('not_allowed', $sanitized);
    }
}
