<?php

declare(strict_types=1);

namespace Hadi\Payment\Services;

use Hadi\Payment\Models\Payment;
use Hadi\Payment\PaymentResponse;
use Hadi\Payment\Events\PaymentInitialized;
use Hadi\Payment\Events\PaymentCompleted;
use Hadi\Payment\Events\PaymentFailed;
use Hadi\Payment\Events\PaymentRefunded;

/**
 * Bridges the stateless sync flow to the async event/job/notification chain.
 *
 * When `hadi-payment.events.enabled` is true, each operation in the sync flow
 * persists a Payment model and dispatches the matching event, which the
 * PaymentEventListener turns into logging, AI analysis and notifications.
 * When disabled (default), the sync flow stays completely stateless and this
 * service is a no-op.
 */
class PaymentRecordingService
{
    /**
     * Whether event recording is enabled.
     */
    public function enabled(): bool
    {
        return (bool) config('hadi-payment.events.enabled', false);
    }

    /**
     * Record an initialized payment and dispatch PaymentInitialized.
     */
    public function recordInitialized(string $gateway, array $paymentData, PaymentResponse $response): ?Payment
    {
        if (!$this->enabled()) {
            return null;
        }

        $payment = Payment::create([
            'order_id' => $paymentData['order_id'] ?? $paymentData['orderId'] ?? null,
            'reference_id' => $response->paymentId,
            'gateway' => $gateway,
            'transaction_id' => $response->transactionId ?? $response->paymentId,
            'payment_id' => $response->paymentId,
            'amount' => $response->amount ?? ($paymentData['amount'] ?? 0),
            'currency' => $response->currency ?? ($paymentData['currency'] ?? 'BDT'),
            'status' => Payment::STATUS_PENDING,
            'gateway_response' => $response->data,
        ]);

        PaymentInitialized::dispatch($payment, $paymentData);

        return $payment;
    }

    /**
     * Record a verified payment, dispatching PaymentCompleted or PaymentFailed.
     */
    public function recordVerified(
        string $gateway,
        string $paymentId,
        PaymentResponse $response
    ): ?Payment {
        if (!$this->enabled()) {
            return null;
        }

        $payment = $this->findPayment($gateway, $paymentId);

        if ($payment === null) {
            return null;
        }

        if ($response->success) {
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'completed_at' => now(),
                'gateway_response' => $response->data,
            ]);

            PaymentCompleted::dispatch($payment, $response->data);
        } else {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'failed_at' => now(),
                'gateway_response' => $response->data,
            ]);

            PaymentFailed::dispatch(
                $payment,
                array_merge($response->data, ['reason' => $response->message])
            );
        }

        return $payment;
    }

    /**
     * Record a refunded payment and dispatch PaymentRefunded.
     */
    public function recordRefunded(
        string $gateway,
        string $paymentId,
        float $amount,
        string $reason,
        PaymentResponse $response
    ): ?Payment {
        if (!$this->enabled()) {
            return null;
        }

        $payment = $this->findPayment($gateway, $paymentId);

        if ($payment === null) {
            return null;
        }

        $payment->update([
            'status' => Payment::STATUS_REFUNDED,
            'refunded_amount' => $amount,
            'refund_reason' => $reason,
            'refunded_at' => now(),
            'gateway_response' => $response->data,
        ]);

        PaymentRefunded::dispatch(
            $payment,
            ['refund_amount' => $amount, 'reason' => $reason]
        );

        return $payment;
    }

    /**
     * Find a previously recorded payment for the given gateway and id.
     */
    private function findPayment(string $gateway, string $paymentId): ?Payment
    {
        return Payment::query()
            ->where('gateway', $gateway)
            ->where(function ($query) use ($paymentId) {
                $query->where('transaction_id', $paymentId)
                    ->orWhere('reference_id', $paymentId)
                    ->orWhere('payment_id', $paymentId);
            })
            ->latest('id')
            ->first();
    }
}
