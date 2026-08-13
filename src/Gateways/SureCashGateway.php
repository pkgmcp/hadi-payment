<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * SureCash payment gateway for Bangladesh.
 */
class SureCashGateway extends BangladeshGateway
{
    protected function apiBaseUrls(): array
    {
        return [
            'sandbox' => 'https://sandbox.surecash.com.bd/api/v1',
            'production' => 'https://api.surecash.com.bd/api/v1',
        ];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key', 'secret_key', 'merchant_id'];
    }

    protected function gatewaySlug(): string
    {
        return 'surecash';
    }
}
