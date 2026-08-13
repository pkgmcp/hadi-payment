<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways\Generic;

/**
 * Generic bank transfer / open banking gateway. Payments are made through a
 * bank account, transfer, or banking redirect flow.
 */
class BankTransferGateway extends GenericGateway
{
    protected function gatewayType(): string
    {
        return 'bank';
    }

    protected function buildInitializePayload(array $data): array
    {
        $payload = parent::buildInitializePayload($data);

        $payload['bank_code'] = $data['bank_code'] ?? $data['bankCode'] ?? null;
        $payload['account_number'] = $data['account_number'] ?? $data['accountNumber'] ?? $data['customer']['account'] ?? null;
        $payload['account_holder'] = $data['account_holder'] ?? $data['customer']['name'] ?? null;

        return $payload;
    }
}
