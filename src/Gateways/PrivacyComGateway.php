<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * Privacy.com virtual card issuing gateway.
 */
class PrivacyComGateway extends VirtualCardGateway
{
    protected function apiBaseUrls(): array
    {
        return ['sandbox' => 'https://api.sandbox.privacy.com/v1', 'production' => 'https://api.privacy.com/v1'];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key'];
    }

    protected function gatewaySlug(): string
    {
        return 'privacy';
    }
}
