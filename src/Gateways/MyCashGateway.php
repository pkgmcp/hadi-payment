<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * MyCash payment gateway for Bangladesh.
 */
class MyCashGateway extends BangladeshGateway
{
    protected function apiBaseUrls(): array
    {
        return [
            'sandbox' => 'https://sandbox.mycash.com.bd/api/v1',
            'production' => 'https://api.mycash.com.bd/api/v1',
        ];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key', 'secret_key', 'merchant_id'];
    }

    protected function gatewaySlug(): string
    {
        return 'mycash';
    }
}
