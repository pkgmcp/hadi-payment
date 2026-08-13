<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * Revolut virtual card issuing gateway.
 */
class RevolutVirtualCardGateway extends VirtualCardGateway
{
    protected function apiBaseUrls(): array
    {
        return ['sandbox' => 'https://sandbox-b2b.revolut.com/api/1.0', 'production' => 'https://b2b.revolut.com/api/1.0'];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key'];
    }

    protected function gatewaySlug(): string
    {
        return 'revolut';
    }
}
