<?php

declare(strict_types=1);

namespace Hadi\Payment;

use Hadi\Payment\Services\PaymentManager;
use Hadi\Payment\Contracts\PaymentGatewayInterface;
use Hadi\Payment\Exceptions\ConfigurationException;
use Hadi\Payment\Factories\GatewayFactory;

/**
 * Hadi Payment - the primary entry point of the package.
 *
 * Provides both a low-level array based API (initialize / process / verify /
 * refund) and a high-level PaymentResponse based API (initializePayment /
 * verifyPayment / refundPayment / getPaymentStatus).
 */
class HadiPayment
{
    public function __construct(
        private readonly GatewayFactory $factory,
        private readonly PaymentManager $manager
    ) {
    }

    /**
     * Resolve a gateway instance.
     */
    public function gateway(string $name): PaymentGatewayInterface
    {
        $name = strtolower($name);

        if (!$this->factory->isGatewayAvailable($name)) {
            throw new ConfigurationException("Gateway '{$name}' is not available");
        }

        $config = $this->factory->getGatewayConfig($name);

        return $this->factory->create($name, $config);
    }

    /**
     * Initialize a payment transaction.
     *
     * The low-level API accepts both camelCase (orderId, callbackUrl) and
     * snake_case (order_id, callback_url) field names.
     */
    public function initialize(string $gateway, array $data): array
    {
        return $this->gateway($gateway)->initialize($this->normalizePaymentData($data));
    }

    /**
     * Normalize common field aliases so either naming convention works.
     */
    private function normalizePaymentData(array $data): array
    {
        if (isset($data['orderId']) && !isset($data['order_id'])) {
            $data['order_id'] = $data['orderId'];
        }

        if (isset($data['callbackUrl']) && !isset($data['callback_url'])) {
            $data['callback_url'] = $data['callbackUrl'];
        }

        return $data;
    }

    /**
     * Process / execute a payment (callback or direct execution).
     */
    public function process(string $gateway, array $data): array
    {
        return $this->gateway($gateway)->process($data);
    }

    /**
     * Verify a payment transaction.
     */
    public function verify(string $gateway, array $data): array
    {
        return $this->gateway($gateway)->verify($data);
    }

    /**
     * Refund a payment transaction.
     */
    public function refund(string $gateway, array $data): array
    {
        return $this->gateway($gateway)->refund($data);
    }

    /**
     * Initialize a payment and return a PaymentResponse.
     */
    public function initializePayment(string $gateway, array $paymentData): PaymentResponse
    {
        return $this->manager->initializePayment($gateway, $paymentData);
    }

    /**
     * Verify a payment and return a PaymentResponse.
     */
    public function verifyPayment(string $gateway, string $paymentId): PaymentResponse
    {
        return $this->manager->verifyPayment($gateway, $paymentId);
    }

    /**
     * Refund a payment and return a PaymentResponse.
     */
    public function refundPayment(string $gateway, string $paymentId, float $amount, string $reason = ''): PaymentResponse
    {
        return $this->manager->refundPayment($gateway, $paymentId, $amount, $reason);
    }

    /**
     * Get the status of a payment.
     */
    public function getPaymentStatus(string $gateway, string $paymentId): PaymentResponse
    {
        return $this->manager->getPaymentStatus($gateway, $paymentId);
    }

    /**
     * Get all supported gateways.
     */
    public function getSupportedGateways(): array
    {
        return $this->manager->getSupportedGateways();
    }

    /**
     * Check if a gateway is supported.
     */
    public function isGatewaySupported(string $gateway): bool
    {
        return $this->manager->isGatewaySupported($gateway);
    }

    /**
     * Register a custom gateway.
     */
    public function registerGateway(string $name, string $class): self
    {
        $this->factory->registerGateway($name, $class);

        return $this;
    }

    /**
     * Get the underlying factory.
     */
    public function factory(): GatewayFactory
    {
        return $this->factory;
    }

    /**
     * Get the underlying payment manager.
     */
    public function manager(): PaymentManager
    {
        return $this->manager;
    }
}
