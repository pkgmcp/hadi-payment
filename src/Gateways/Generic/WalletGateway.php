<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways\Generic;

/**
 * Generic digital wallet gateway (e-wallets, QR payments and wallet-to-wallet
 * transfers).
 */
class WalletGateway extends GenericGateway
{
    protected function gatewayType(): string
    {
        return 'wallet';
    }

    protected function buildInitializePayload(array $data): array
    {
        $payload = parent::buildInitializePayload($data);

        $payload['wallet'] = $data['wallet'] ?? $data['wallet_id'] ?? null;
        $payload['phone'] = $data['phone'] ?? $data['customer']['phone'] ?? null;

        return $payload;
    }
}
