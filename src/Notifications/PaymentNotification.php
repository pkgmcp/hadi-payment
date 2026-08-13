<?php

declare(strict_types=1);

namespace Hadi\Payment\Notifications;

use Hadi\Payment\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Payment $payment,
        public readonly string $type,
        public readonly array $data = []
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database'];

        if (config('hadi-payment.notifications.mail.enabled', true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $message = match ($this->type) {
            'payment_completed' => $this->getPaymentCompletedMailMessage(),
            'payment_failed' => $this->getPaymentFailedMailMessage(),
            'payment_refunded' => $this->getPaymentRefundedMailMessage(),
            default => $this->getDefaultMailMessage(),
        };

        return $message;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'type' => $this->type,
            'gateway' => $this->payment->gateway,
            'amount' => $this->payment->amount,
            'currency' => $this->payment->currency,
            'status' => $this->payment->status,
            'data' => $this->data,
        ];
    }

    /**
     * Get payment completed mail message.
     */
    private function getPaymentCompletedMailMessage(): MailMessage
    {
        return (new MailMessage())
            ->subject('Payment Completed Successfully')
            ->greeting('Payment Completed!')
            ->line("Your payment of {$this->payment->currency} {$this->payment->amount} has been completed successfully.")
            ->line("Gateway: {$this->payment->gateway}")
            ->line("Transaction ID: {$this->payment->transaction_id}")
            ->action('View Payment Details', route('payment.show', $this->payment->id))
            ->line('Thank you for your business!');
    }

    /**
     * Get payment failed mail message.
     */
    private function getPaymentFailedMailMessage(): MailMessage
    {
        return (new MailMessage())
            ->subject('Payment Failed')
            ->greeting('Payment Failed')
            ->line("Your payment of {$this->payment->currency} {$this->payment->amount} has failed.")
            ->line("Gateway: {$this->payment->gateway}")
            ->line("Transaction ID: {$this->payment->transaction_id}")
            ->line('Reason: ' . ($this->data['reason'] ?? 'Unknown'))
            ->action('Retry Payment', route('payment.show', $this->payment->id))
            ->line('Please try again or contact support if the issue persists.');
    }

    /**
     * Get payment refunded mail message.
     */
    private function getPaymentRefundedMailMessage(): MailMessage
    {
        return (new MailMessage())
            ->subject('Payment Refunded')
            ->greeting('Payment Refunded')
            ->line("Your payment of {$this->payment->currency} {$this->payment->amount} has been refunded.")
            ->line("Gateway: {$this->payment->gateway}")
            ->line("Transaction ID: {$this->payment->transaction_id}")
            ->line('Refund Amount: ' . ($this->data['refund_amount'] ?? $this->payment->amount))
            ->line('Reason: ' . ($this->data['reason'] ?? 'Customer request'))
            ->action('View Refund Details', route('payment.show', $this->payment->id))
            ->line('The refund will be processed within 3-5 business days.');
    }

    /**
     * Get default mail message.
     */
    private function getDefaultMailMessage(): MailMessage
    {
        return (new MailMessage())
            ->subject('Payment Update')
            ->greeting('Payment Update')
            ->line("Your payment status has been updated.")
            ->line("Gateway: {$this->payment->gateway}")
            ->line("Amount: {$this->payment->currency} {$this->payment->amount}")
            ->line("Status: {$this->payment->status}")
            ->action('View Payment Details', route('payment.show', $this->payment->id));
    }
}
