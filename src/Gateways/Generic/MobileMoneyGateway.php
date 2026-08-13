<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways\Generic;

/**
 * Generic mobile money gateway (wallet top-ups, airtime, USSD and mobile
 * banking integrations commonly found across Africa and Asia).
 */
class MobileMoneyGateway extends GenericGateway
{
    protected function gatewayType(): string
    {
        return 'mobile';
    }

    protected function buildInitializePayload(array $data): array
    {
        $payload = parent::buildInitializePayload($data);

        $payload['phone'] = $data['phone'] ?? $data['msisdn'] ?? $data['customer']['phone'] ?? null;
        $payload['msisdn'] = $payload['phone'];
        $payload['account'] = $data['account'] ?? $data['mobile_account'] ?? null;

        return $payload;
    }
}
