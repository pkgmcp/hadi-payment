<?php

declare(strict_types=1);

namespace Hadi\Payment\Services;

use Hadi\Payment\Services\PaymentManager;
use Hadi\Payment\Services\PaymentLogger;
use Hadi\Payment\Services\PaymentValidator;
use Hadi\Payment\PaymentResponse;
use Hadi\Payment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    public function __construct(
        private readonly PaymentManager $paymentManager,
        private readonly PaymentLogger $logger,
        private readonly PaymentValidator $validator,
        private readonly ?PaymentRecordingService $recorder = null
    ) {
    }

    /**
     * Initialize a payment
     */
    public function initializePayment(string $gateway, array $paymentData): PaymentResponse
    {
        try {
            // Normalize common field aliases so either naming convention works
            $paymentData = $this->normalizePaymentData($paymentData);

            // Validate payment data
            $this->validator->validatePaymentData($paymentData);

            // Sanitize payment data
            $sanitizedData = $this->validator->sanitizePaymentData($paymentData);

            // Initialize payment
            $response = $this->paymentManager->initializePayment($gateway, $sanitizedData);

            // Log payment initialization
            $this->logger->logPaymentInitialization($gateway, $sanitizedData, $response);

            // Persist a Payment record and dispatch PaymentInitialized when enabled
            $this->recorder?->recordInitialized($gateway, $sanitizedData, $response);

            return $response;
        } catch (PaymentException $e) {
            Log::error('Payment initialization failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'data' => $paymentData
            ]);

            throw $e;
        }
    }

    /**
     * Verify a payment
     */
    public function verifyPayment(string $gateway, string $paymentId): PaymentResponse
    {
        try {
            // Validate payment ID
            $this->validator->validatePaymentId($paymentId);

            // Verify payment
            $response = $this->paymentManager->verifyPayment($gateway, $paymentId);

            // Log payment verification
            $this->logger->logPaymentVerification($gateway, $paymentId, $response);

            // Persist the verified status and dispatch the matching event when enabled
            $this->recorder?->recordVerified($gateway, $paymentId, $response);

            return $response;
        } catch (PaymentException $e) {
            Log::error('Payment verification failed', [
                'gateway' => $gateway,
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Refund a payment
     */
    public function refundPayment(string $gateway, string $paymentId, float $amount, string $reason = ''): PaymentResponse
    {
        try {
            // Validate refund data
            $this->validator->validateRefundData($paymentId, $amount, $reason);

            // Refund payment
            $response = $this->paymentManager->refundPayment($gateway, $paymentId, $amount, $reason);

            // Log payment refund
            $this->logger->logPaymentRefund($gateway, $paymentId, $amount, $reason, $response);

            // Persist the refunded status and dispatch PaymentRefunded when enabled
            $this->recorder?->recordRefunded($gateway, $paymentId, $amount, $reason, $response);

            return $response;
        } catch (PaymentException $e) {
            Log::error('Payment refund failed', [
                'gateway' => $gateway,
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $gateway, string $paymentId): PaymentResponse
    {
        try {
            // Validate payment ID
            $this->validator->validatePaymentId($paymentId);

            // Get payment status
            $response = $this->paymentManager->getPaymentStatus($gateway, $paymentId);

            // Log payment status check
            $this->logger->logPaymentStatus($gateway, $paymentId, $response);

            return $response;
        } catch (PaymentException $e) {
            Log::error('Payment status check failed', [
                'gateway' => $gateway,
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get supported gateways
     */
    public function getSupportedGateways(): array
    {
        return $this->paymentManager->getSupportedGateways();
    }

    /**
     * Check if gateway is supported
     */
    public function isGatewaySupported(string $gateway): bool
    {
        return $this->paymentManager->isGatewaySupported($gateway);
    }

    /**
     * Get gateway configuration
     */
    public function getGatewayConfig(string $gateway): array
    {
        return config("hadi-payment.gateways.{$gateway}", []);
    }

    /**
     * Log payment operation
     */
    public function logPayment(string $operation, string $gateway, array $data, ?PaymentResponse $response = null): void
    {
        $this->logger->log($operation, $gateway, $data, $response);
    }

    /**
     * Normalize common field aliases so either naming convention works.
     * Both spellings are kept because the validator reads snake_case while
     * gateway drivers read camelCase.
     */
    private function normalizePaymentData(array $data): array
    {
        if (isset($data['orderId']) && !isset($data['order_id'])) {
            $data['order_id'] = $data['orderId'];
        }

        if (isset($data['order_id']) && !isset($data['orderId'])) {
            $data['orderId'] = $data['order_id'];
        }

        if (isset($data['callbackUrl']) && !isset($data['callback_url'])) {
            $data['callback_url'] = $data['callbackUrl'];
        }

        if (isset($data['callback_url']) && !isset($data['callbackUrl'])) {
            $data['callbackUrl'] = $data['callback_url'];
        }

        return $data;
    }
}
