<?php

declare(strict_types=1);

namespace Hadi\Payment\Services;

use Hadi\Payment\Contracts\PaymentGatewayInterface;
use Hadi\Payment\Exceptions\ConfigurationException;
use Hadi\Payment\Factories\GatewayFactory;
use Hadi\Payment\PaymentResponse;

class PaymentManager
{
    public function __construct(
        private readonly GatewayFactory $factory
    ) {
    }

    /**
     * Resolve a gateway instance using its configuration.
     */
    public function getGateway(string $name): PaymentGatewayInterface
    {
        $name = strtolower($name);

        if (!$this->factory->isGatewayAvailable($name)) {
            throw new ConfigurationException("Gateway '{$name}' not found");
        }

        $config = $this->factory->getGatewayConfig($name);

        return $this->factory->create($name, $config);
    }

    /**
     * Initialize payment with the given gateway.
     */
    public function initializePayment(string $gatewayName, array $paymentData): PaymentResponse
    {
        $result = $this->getGateway($gatewayName)->initialize($paymentData);

        return $this->toResponse($result);
    }

    /**
     * Verify payment with the given gateway.
     */
    public function verifyPayment(string $gatewayName, string $paymentId): PaymentResponse
    {
        $result = $this->getGateway($gatewayName)->verify(['paymentID' => $paymentId, 'transactionId' => $paymentId]);

        return $this->toResponse($result);
    }

    /**
     * Refund payment with the given gateway.
     */
    public function refundPayment(
        string $gatewayName,
        string $paymentId,
        float $amount,
        string $reason = ''
    ): PaymentResponse {
        $result = $this->getGateway($gatewayName)->refund([
            'paymentID' => $paymentId,
            'transactionId' => $paymentId,
            'amount' => $amount,
            'reason' => $reason,
        ]);

        return $this->toResponse($result);
    }

    /**
     * Get payment status with the given gateway.
     */
    public function getPaymentStatus(string $gatewayName, string $paymentId): PaymentResponse
    {
        return $this->verifyPayment($gatewayName, $paymentId);
    }

    /**
     * Get all available gateways.
     */
    public function getGateways(): array
    {
        return $this->factory->getAvailableGateways();
    }

    /**
     * Get supported gateways (gateway slug => human name).
     */
    public function getSupportedGateways(): array
    {
        $gateways = [];

        foreach ($this->factory->getAvailableGateways() as $key => $class) {
            $gateways[$key] = $this->factory->getGatewayName($key);
        }

        return $gateways;
    }

    /**
     * Check if a gateway is supported.
     */
    public function isGatewaySupported(string $gateway): bool
    {
        return $this->factory->isGatewayAvailable($gateway);
    }

    /**
     * Get configured gateway names.
     */
    public function getGatewayNames(): array
    {
        return array_keys(config('hadi-payment.gateways', []));
    }

    /**
     * Check if a gateway exists in the factory.
     */
    public function hasGateway(string $name): bool
    {
        return $this->factory->isGatewayAvailable($name);
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
     * Wrap a normalized gateway response into a PaymentResponse.
     */
    private function toResponse(array $result): PaymentResponse
    {
        $status = $result['status'] ?? 'unknown';
        $success = in_array($status, ['success', 'completed', 'pending_user_action', 'pending'], true);

        return PaymentResponse::success(
            message: $result['message'] ?? 'Payment operation completed.',
            data: $result,
            paymentId: isset($result['gatewayReferenceId']) ? (string) $result['gatewayReferenceId'] : null,
            redirectUrl: isset($result['paymentUrl']) ? (string) $result['paymentUrl'] : null,
            transactionId: isset($result['transactionId']) ? (string) $result['transactionId'] : null,
            amount: isset($result['amount']) ? (float) $result['amount'] : null,
            currency: $result['currency'] ?? null,
            status: $status
        );
    }
}
