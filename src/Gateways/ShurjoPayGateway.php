<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

use Hadi\Payment\Exceptions\InitializationException;
use Hadi\Payment\Exceptions\InvalidConfigurationException;

/**
 * ShurjoPay payment gateway for Bangladesh.
 */
class ShurjoPayGateway extends BangladeshGateway
{
    protected function apiBaseUrls(): array
    {
        return [
            'sandbox' => 'https://sandbox.shurjopay.com/api/v1',
            'production' => 'https://api.shurjopay.com/api/v1',
        ];
    }

    protected function requiredConfigKeys(): array
    {
        return ['merchant_id', 'merchant_password', 'api_key'];
    }

    protected function gatewaySlug(): string
    {
        return 'shurjopay';
    }

    protected function getDefaultConfig(): array
    {
        return array_merge(parent::getDefaultConfig(), [
            'merchant_password' => '',
        ]);
    }

    protected function validateConfig(array $config): void
    {
        parent::validateConfig($config);

        if (empty($config['merchant_password'])) {
            throw new InvalidConfigurationException('ShurjoPay: merchant_password is required.');
        }
    }

    public function initialize(array $data): array
    {
        $sanitized = $this->sanitize($data);
        $this->validatePaymentData($sanitized);

        $orderId = $sanitized['orderId'] ?? $sanitized['reference_id'];

        $payload = [
            'merchant_id' => $this->config['merchant_id'],
            'merchant_password' => $this->config['merchant_password'],
            'order_id' => $orderId,
            'amount' => $sanitized['amount'],
            'currency' => $sanitized['currency'] ?? 'BDT',
            'customer_name' => $sanitized['customer']['name'] ?? 'Customer',
            'customer_mobile' => $sanitized['customer']['mobile'] ?? '',
            'customer_email' => $sanitized['customer']['email'] ?? '',
            'description' => $sanitized['description'] ?? 'Payment',
            'success_url' => $sanitized['callbackUrl'] ?? $sanitized['success_url'] ?? ($this->config['defaultCallbackUrl'] ?? ''),
            'cancel_url' => $sanitized['cancel_url'] ?? ($this->config['defaultCallbackUrl'] ?? ''),
        ];

        $response = $this->apiRequest('POST', '/payment/initialize', $payload);

        if ($response['status_code'] !== 200) {
            throw new InitializationException('ShurjoPay: Failed to initialize payment (HTTP ' . $response['status_code'] . ').');
        }

        $responseData = $response['body'] ?? [];

        if (($responseData['status'] ?? '') !== 'success') {
            throw new InitializationException('ShurjoPay: ' . ($responseData['message'] ?? 'Failed to initialize payment.'));
        }

        return $this->buildInitializeResponse($responseData);
    }
}
