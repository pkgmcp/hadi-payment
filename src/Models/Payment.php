<?php

declare(strict_types=1);

namespace Hadi\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $order_id
 * @property string|null $reference_id
 * @property string $gateway
 * @property string|null $payment_id
 * @property string|null $transaction_id
 * @property string $amount
 * @property string $currency
 * @property string $status
 * @property array|null $gateway_response
 * @property array|null $callback_data
 * @property array|null $webhook_data
 * @property string $refunded_amount
 * @property string|null $refund_reason
 * @property string|null $product_name
 * @property array|null $customer_data
 * @property string|null $qr_code_path
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $refunded_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PaymentLog> $logs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PaymentRefund> $refunds
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PaymentHistory> $histories
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PaymentProblem> $problems
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static where(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static whereBetween(string $column, iterable $values, string $boolean = 'and', bool $not = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static whereYear(string $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static when(mixed $value, callable|null $callback = null, mixed $default = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static whereHas(string $relation, \Closure|null $callback = null, string $operator = '>=', int $count = 1)
 * @method static static|null find(mixed $id, array $columns = ['*'])
 * @method static static findOrFail(mixed $id, array $columns = ['*'])
 * @method static static create(array $attributes = [])
 * @method static int count(string $columns = '*')
 * @method static mixed avg(string $column)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static selectRaw(string $expression, array $bindings = [])
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'reference_id',
        'gateway',
        'payment_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'gateway_response',
        'callback_data',
        'webhook_data',
        'refunded_amount',
        'refund_reason',
        'processed_at',
        'completed_at',
        'failed_at',
        'refunded_at',
        'product_name',
        'customer_data',
        'qr_code_path',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'callback_data' => 'array',
        'webhook_data' => 'array',
        'customer_data' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Payment statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    /**
     * Get the user that owns the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    /**
     * Get the payment logs for the payment
     */
    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    /**
     * Get the payment refunds for the payment
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    /**
     * Get the payment history entries for the payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<PaymentHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }

    /**
     * Get the payment problems reported for the payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<PaymentProblem, $this>
     */
    public function problems(): HasMany
    {
        return $this->hasMany(PaymentProblem::class);
    }

    /**
     * Scope a query to only include pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope a query to only include refunded payments
     */
    public function scopeRefunded($query)
    {
        return $query->whereIn('status', [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Scope a query to only include payments for a specific gateway
     */
    public function scopeForGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope a query to only include payments for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Check if payment is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get the total refunded amount
     */
    public function getTotalRefundedAmount(): float
    {
        return $this->refunds()->sum('amount');
    }

    /**
     * Get the remaining refundable amount
     */
    public function getRemainingRefundableAmount(): float
    {
        return (float) $this->amount - (float) $this->getTotalRefundedAmount();
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->isCompleted() && $this->getRemainingRefundableAmount() > 0;
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(array $gatewayResponse = []): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'gateway_response' => $gatewayResponse,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(array $gatewayResponse = []): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'gateway_response' => $gatewayResponse,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark payment as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'processed_at' => now(),
        ]);
    }

    /**
     * Update refunded amount
     */
    public function updateRefundedAmount(): void
    {
        $totalRefunded = $this->getTotalRefundedAmount();

        if ($totalRefunded >= $this->amount) {
            $this->update([
                'status' => self::STATUS_REFUNDED,
                'refunded_amount' => $totalRefunded,
            ]);
        } elseif ($totalRefunded > 0) {
            $this->update([
                'status' => self::STATUS_PARTIALLY_REFUNDED,
                'refunded_amount' => $totalRefunded,
            ]);
        }
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmount(): string
    {
        return number_format((float) $this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get payment status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_PROCESSING => 'badge-info',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_FAILED => 'badge-danger',
            self::STATUS_CANCELLED => 'badge-secondary',
            self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}
