<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways\Generic;

/**
 * Generic cryptocurrency payment gateway. Payments are settled through a
 * crypto wallet address at a fixed or floating exchange rate.
 */
class CryptoGateway extends GenericGateway
{
    protected function gatewayType(): string
    {
        return 'crypto';
    }

    protected function buildInitializePayload(array $data): array
    {
        $payload = parent::buildInitializePayload($data);

        $payload['wallet_address'] = $data['wallet_address'] ?? $data['address'] ?? null;
        $payload['coin'] = $data['coin'] ?? $data['currency_code'] ?? 'USDT';
        $payload['network'] = $data['network'] ?? null;

        return $payload;
    }
}
