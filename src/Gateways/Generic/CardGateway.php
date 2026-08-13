<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways\Generic;

/**
 * Generic card processing gateway (direct card payments, tokenization and
 * 3-D Secure flows).
 */
class CardGateway extends GenericGateway
{
    protected function gatewayType(): string
    {
        return 'card';
    }

    protected function buildInitializePayload(array $data): array
    {
        $payload = parent::buildInitializePayload($data);

        $payload['card'] = $data['card'] ?? [
            'number' => $data['card_number'] ?? null,
            'exp_month' => $data['card_exp_month'] ?? null,
            'exp_year' => $data['card_exp_year'] ?? null,
            'cvc' => $data['card_cvc'] ?? null,
        ];
        $payload['card_token'] = $data['card_token'] ?? $data['token'] ?? null;

        return $payload;
    }
}
