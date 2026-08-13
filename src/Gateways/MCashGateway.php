<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * MCash payment gateway for Bangladesh.
 */
class MCashGateway extends BangladeshGateway
{
    protected function apiBaseUrls(): array
    {
        return [
            'sandbox' => 'https://sandbox.mcash.com.bd/api/v1',
            'production' => 'https://api.mcash.com.bd/api/v1',
        ];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key', 'secret_key', 'merchant_id'];
    }

    protected function gatewaySlug(): string
    {
        return 'mcash';
    }
}
