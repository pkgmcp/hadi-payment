<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * Mercury virtual card issuing gateway.
 */
class MercuryGateway extends VirtualCardGateway
{
    protected function apiBaseUrls(): array
    {
        return ['sandbox' => 'https://sandbox.mercury.com/api/v1', 'production' => 'https://api.mercury.com/api/v1'];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key'];
    }

    protected function gatewaySlug(): string
    {
        return 'mercury';
    }
}
