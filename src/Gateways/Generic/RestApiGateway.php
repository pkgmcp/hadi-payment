<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways\Generic;

/**
 * Generic REST / JSON API payment gateway. All operations are performed
 * against a JSON endpoint configured by the merchant.
 */
class RestApiGateway extends GenericGateway
{
    protected function gatewayType(): string
    {
        return 'api';
    }
}
