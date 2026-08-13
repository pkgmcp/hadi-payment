<?php

declare(strict_types=1);

namespace Hadi\Payment\Contracts;

use Hadi\Payment\Contracts\PaymentGatewayInterface;
use Hadi\Payment\Services\CountryCatalog;

interface GatewayFactoryInterface
{
    /**
     * Create a payment gateway instance.
     */
    public function create(string $gateway, array $config): PaymentGatewayInterface;

    /**
     * Get all available gateways.
     */
    public function getAvailableGateways(): array;

    /**
     * Check if gateway is available.
     */
    public function isGatewayAvailable(string $gateway): bool;

    /**
     * Register a custom gateway.
     */
    public function registerGateway(string $name, string $class): void;

    /**
     * Get gateway configuration.
     */
    public function getGatewayConfig(string $gateway): array;

    /**
     * Get the human friendly display name for a gateway.
     */
    public function getGatewayName(string $gateway): string;

    /**
     * Get the CountryCatalog used by this factory.
     */
    public function getCatalog(): CountryCatalog;
}
