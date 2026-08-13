<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\InvalidConfigurationException;
use Hadi\Payment\Exceptions\RefundException;
use Hadi\Payment\Exceptions\VerificationException;
use Hadi\Payment\Gateways\Generic\MobileMoneyGateway;

class GenericGatewayTest extends TestCase
{
    public function test_validate_config_requires_endpoint_and_credential(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        new class ([]) extends MobileMoneyGateway {
        };
    }

    public function test_initialize_returns_normalized_response(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->initialize([
            'amount' => 250,
            'order_id' => 'ORD-123',
            'callback_url' => 'https://example.com/callback',
        ]);

        $this->assertSame('pending_user_action', $result['status']);
        $this->assertSame('TXN123', $result['gatewayReferenceId']);
        $this->assertSame('https://sandbox.test-gw.com/pay/TXN123', $result['paymentUrl']);
    }

    public function test_initialize_rejects_missing_amount(): void
    {
        $this->expectException(InitializationException::class);

        $this->makeGateway()->initialize(['order_id' => 'ORD-123']);
    }

    public function test_verify_returns_normalized_status(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->verify(['transaction_id' => 'TXN123']);

        $this->assertSame('pending', $result['status']);
        $this->assertSame('TXN123', $result['transactionId']);
    }

    public function test_verify_requires_transaction_id(): void
    {
        $this->expectException(VerificationException::class);

        $this->makeGateway()->verify([]);
    }

    public function test_refund_returns_normalized_response(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->refund(['transaction_id' => 'TXN123', 'amount' => 40]);

        $this->assertSame('success', $result['status']);
        $this->assertSame('REF123', $result['refundId']);
    }

    public function test_refund_requires_transaction_id(): void
    {
        $this->expectException(RefundException::class);

        $this->makeGateway()->refund([]);
    }

    public function test_process_delegates_to_verify(): void
    {
        $gateway = $this->makeGateway();

        $result = $gateway->process(['transaction_id' => 'TXN123']);

        $this->assertSame('pending', $result['status']);
    }

    public function test_is_configured_reflects_state(): void
    {
        $this->assertTrue($this->makeGateway()->isConfigured());
    }

    private function makeGateway(): MobileMoneyGateway
    {
        return new class ([
            'merchant_id' => 'm',
            'endpoint' => 'https://sandbox.test-gw.com/api/v1',
            'sandbox' => true,
        ]) extends MobileMoneyGateway {
            protected function httpClient(string $method, string $url, array $data = [], array $headers = []): array
            {
                if (str_contains($url, '/verify')) {
                    return [
                        'status_code' => 200,
                        'body' => ['data' => ['status' => 'PENDING', 'amount' => 250, 'currency' => 'TZS']],
                    ];
                }

                if (str_contains($url, '/refund')) {
                    return [
                        'status_code' => 200,
                        'body' => ['data' => ['status' => 'success', 'refund_id' => 'REF123', 'refunded_amount' => 40]],
                    ];
                }

                return [
                    'status_code' => 200,
                    'body' => [
                        'data' => [
                            'transaction_id' => 'TXN123',
                            'payment_url' => 'https://sandbox.test-gw.com/pay/TXN123',
                        ],
                    ],
                ];
            }
        };
    }
}
