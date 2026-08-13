<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * Payoneer virtual card issuing gateway.
 */
class PayoneerVirtualCardGateway extends VirtualCardGateway
{
    protected function apiBaseUrls(): array
    {
        return ['sandbox' => 'https://api.sandbox.payoneer.com/v2', 'production' => 'https://api.payoneer.com/v2'];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key'];
    }

    protected function gatewaySlug(): string
    {
        return 'payoneer';
    }
}
