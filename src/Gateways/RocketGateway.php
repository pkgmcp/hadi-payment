<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * Rocket (DBBL) payment gateway for Bangladesh.
 */
class RocketGateway extends BangladeshGateway
{
    protected function apiBaseUrls(): array
    {
        return [
            'sandbox' => 'https://sandbox.rocket.com.bd/api/v1',
            'production' => 'https://api.rocket.com.bd/api/v1',
        ];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key', 'secret_key', 'merchant_id'];
    }

    protected function gatewaySlug(): string
    {
        return 'rocket';
    }
}
