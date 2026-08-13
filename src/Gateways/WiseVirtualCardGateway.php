<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * Wise virtual card issuing gateway.
 */
class WiseVirtualCardGateway extends VirtualCardGateway
{
    protected function apiBaseUrls(): array
    {
        return ['sandbox' => 'https://api.sandbox.transferwise.tech', 'production' => 'https://api.transferwise.com'];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key'];
    }

    protected function gatewaySlug(): string
    {
        return 'wise';
    }

    protected function gatewayDisplayName(): string
    {
        return 'Wise Virtual Card';
    }
}
