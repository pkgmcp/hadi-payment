<?php

declare(strict_types=1);

namespace Hadi\Payment\Gateways;

/**
 * Stripe Issuing virtual card gateway.
 */
class StripeIssuingGateway extends VirtualCardGateway
{
    protected function apiBaseUrls(): array
    {
        return ['sandbox' => 'https://api.stripe.com/v1', 'production' => 'https://api.stripe.com/v1'];
    }

    protected function requiredConfigKeys(): array
    {
        return ['api_key'];
    }

    protected function gatewaySlug(): string
    {
        return 'stripe';
    }

    protected function gatewayDisplayName(): string
    {
        return 'Stripe Issuing';
    }

    protected function apiRequest(string $method, string $endpoint, array $payload = []): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . ($this->config['api_key'] ?? ''),
            'Accept' => 'application/json',
        ];

        if (!in_array(strtoupper($method), ['GET', 'HEAD'], true)) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        return $this->httpClient($method, $this->getApiBaseUrl() . $endpoint, $payload, $headers);
    }
}
