<?php

declare(strict_types=1);

namespace Hadi\Payment\Listeners;

use Hadi\Payment\Events\PaymentEvent;
use Hadi\Payment\Events\PaymentInitialized;
use Hadi\Payment\Events\PaymentCompleted;
use Hadi\Payment\Events\PaymentFailed;
use Hadi\Payment\Events\PaymentRefunded;
use Hadi\Payment\Services\PaymentLogger;
use Hadi\Payment\Services\AIAgentService;
use Hadi\Payment\Notifications\PaymentNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PaymentEventListener
{
    public function __construct(
        private readonly PaymentLogger $logger,
        private readonly AIAgentService $aiAgent
    ) {
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(\Illuminate\Contracts\Events\Dispatcher $events): void
    {
        $events->listen(PaymentInitialized::class, [$this, 'handlePaymentInitialized']);
        $events->listen(PaymentCompleted::class, [$this, 'handlePaymentCompleted']);
        $events->listen(PaymentFailed::class, [$this, 'handlePaymentFailed']);
        $events->listen(PaymentRefunded::class, [$this, 'handlePaymentRefunded']);
        $events->listen(PaymentEvent::class, [$this, 'handlePaymentEvent']);
    }

    /**
     * Handle payment initialized event.
     */
    public function handlePaymentInitialized(PaymentInitialized $event): void
    {
        $this->logger->log('payment_initialized', $event->getPayment()->transaction_id, [
            'gateway' => $event->getGateway(),
            'amount' => $event->getAmount(),
            'currency' => $event->getCurrency(),
        ]);

        // AI analysis for new payments
        if (config('hadi-payment.ai_agent.enabled', true)) {
            $this->aiAgent->analyzePaymentPatterns($event->getPayment());
        }

        Log::info('Payment initialized', [
            'payment_id' => $event->getPayment()->id,
            'gateway' => $event->getGateway(),
            'amount' => $event->getAmount(),
        ]);
    }

    /**
     * Handle payment completed event.
     */
    public function handlePaymentCompleted(PaymentCompleted $event): void
    {
        $this->logger->log('payment_completed', $event->getPayment()->transaction_id, [
            'gateway' => $event->getGateway(),
            'amount' => $event->getAmount(),
            'currency' => $event->getCurrency(),
        ]);

        // Generate notifications for successful payments
        if (config('hadi-payment.ai_agent.notifications_enabled', true)) {
            $analysis = $this->aiAgent->analyzePaymentPatterns($event->getPayment());
            $this->aiAgent->generateNotifications($event->getPayment(), $analysis);
        }

        $this->sendPaymentNotification($event->getPayment(), 'payment_completed');

        Log::info('Payment completed', [
            'payment_id' => $event->getPayment()->id,
            'gateway' => $event->getGateway(),
            'amount' => $event->getAmount(),
        ]);
    }

    /**
     * Handle payment failed event.
     */
    public function handlePaymentFailed(PaymentFailed $event): void
    {
        $this->logger->log('payment_failed', $event->getPayment()->transaction_id, [
            'gateway' => $event->getGateway(),
            'amount' => $event->getAmount(),
            'currency' => $event->getCurrency(),
            'reason' => $event->getData()['reason'] ?? 'Unknown',
        ]);

        // AI analysis for failed payments
        if (config('hadi-payment.ai_agent.enabled', true)) {
            $this->aiAgent->analyzePaymentPatterns($event->getPayment());
        }

        $this->sendPaymentNotification(
            $event->getPayment(),
            'payment_failed',
            ['reason' => $event->getData()['reason'] ?? 'Unknown']
        );

        Log::warning('Payment failed', [
            'payment_id' => $event->getPayment()->id,
            'gateway' => $event->getGateway(),
            'amount' => $event->getAmount(),
            'reason' => $event->getData()['reason'] ?? 'Unknown',
        ]);
    }

    /**
     * Handle payment refunded event.
     */
    public function handlePaymentRefunded(PaymentRefunded $event): void
    {
        $this->logger->log('payment_refunded', $event->getPayment()->transaction_id, [
            'gateway' => $event->getGateway(),
            'amount' => $event->getAmount(),
            'currency' => $event->getCurrency(),
            'refund_amount' => $event->getData()['refund_amount'] ?? $event->getAmount(),
        ]);

        $this->sendPaymentNotification(
            $event->getPayment(),
            'payment_refunded',
            ['refund_amount' => $event->getData()['refund_amount'] ?? $event->getAmount()]
        );

        Log::info('Payment refunded', [
            'payment_id' => $event->getPayment()->id,
            'gateway' => $event->getGateway(),
            'amount' => $event->getAmount(),
            'refund_amount' => $event->getData()['refund_amount'] ?? $event->getAmount(),
        ]);
    }

    /**
     * Handle any payment event.
     */
    public function handlePaymentEvent(PaymentEvent $event): void
    {
        $this->logger->log('payment_event', $event->getPayment()->transaction_id, [
            'event_type' => get_class($event),
            'gateway' => $event->getGateway(),
            'amount' => $event->getAmount(),
            'currency' => $event->getCurrency(),
            'status' => $event->getStatus(),
        ]);
    }

    /**
     * Send a payment notification to the configured recipient (if any).
     */
    private function sendPaymentNotification(
        \Hadi\Payment\Models\Payment $payment,
        string $type,
        array $data = []
    ): void {
        $recipient = config('hadi-payment.notifications.mail.recipient');

        if (!$recipient || !config('hadi-payment.notifications.mail.enabled', true)) {
            return;
        }

        Notification::route('mail', $recipient)
            ->notify(new PaymentNotification($payment, $type, $data));
    }
}
