<?php

declare(strict_types=1);

namespace Hadi\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $payment_id
 * @property string $gateway
 * @property string $operation
 * @property string $status
 * @property string|null $message
 * @property array|null $request_data
 * @property array|null $response_data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Payment $payment
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static where(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static static create(array $attributes = [])
 */
class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'gateway',
        'operation',
        'status',
        'message',
        'request_data',
        'response_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    /**
     * Get the payment that owns the log
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Scope a query to only include logs for a specific operation
     */
    public function scopeForOperation($query, string $operation)
    {
        return $query->where('operation', $operation);
    }

    /**
     * Scope a query to only include logs for a specific gateway
     */
    public function scopeForGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope a query to only include successful logs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed logs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
