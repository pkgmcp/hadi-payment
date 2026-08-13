<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * UCash payment gateway for Bangladesh.
 */
class UCashGateway extends BangladeshGateway
{
    protected function apiBaseUrls(): array
    {
        return [
            'sandbox' => 'https://sandbox.ucash.com.bd/api/v1',
            'production' => 'https://api.ucash.com.bd/api/v1',
        ];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key', 'secret_key', 'merchant_id'];
    }

    protected function gatewaySlug(): string
    {
        return 'ucash';
    }
}
