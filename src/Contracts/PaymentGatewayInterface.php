<?php

declare(strict_types=1);

namespace Hadi\Payment\Contracts;

/**
 * Interface implemented by every payment gateway in the Hadi Payment package.
 *
 * Gateways provide a consistent four-step API:
 * initialize -> process -> verify -> refund.
 */
interface PaymentGatewayInterface
{
    /**
     * Initialize a payment transaction.
     *
     * @param array $data Transaction data (amount, orderId, callbackUrl, ...)
     * @return array Normalized gateway response.
     */
    public function initialize(array $data): array;

    /**
     * Process / execute a payment (typically a callback or direct execution).
     *
     * @param array $data Transaction data.
     * @return array Normalized gateway response.
     */
    public function process(array $data): array;

    /**
     * Verify a payment transaction.
     *
     * @param array $data Transaction data (paymentId / transactionId).
     * @return array Normalized gateway response.
     */
    public function verify(array $data): array;

    /**
     * Refund a payment transaction.
     *
     * @param array $data Refund data (paymentId, transactionId, amount, reason).
     * @return array Normalized gateway response.
     */
    public function refund(array $data): array;

    /**
     * Check if the gateway is properly configured.
     *
     * @return bool
     */
    public function isConfigured(): bool;
}
