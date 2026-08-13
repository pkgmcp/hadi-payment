<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways\Generic;

/**
 * Generic hosted / redirect payment gateway. The customer is redirected to a
 * payment page hosted by the provider, and the provider reports back through
 * the callback URL.
 */
class HostedRedirectGateway extends GenericGateway
{
    protected function gatewayType(): string
    {
        return 'redirect';
    }
}
