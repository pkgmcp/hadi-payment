<?php

declare(strict_types=1);

namespace Hadi\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $payment_id
 * @property int|null $user_id
 * @property string $invoice_number
 * @property \Illuminate\Support\Carbon $invoice_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property string $status
 * @property string $subtotal
 * @property string $tax_amount
 * @property string $discount_amount
 * @property string $total_amount
 * @property string $currency
 * @property array|null $billing_address
 * @property array|null $shipping_address
 * @property string|null $notes
 * @property string|null $terms_conditions
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItem> $items
 * @property-read Payment|null $payment
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
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'user_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'billing_address',
        'shipping_address',
        'notes',
        'terms_conditions',
        'sent_at',
        'paid_at',
        'cancelled_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Invoice statuses
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    /**
     * Get the payment that owns the invoice
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the user that owns the invoice
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    /**
     * Get invoice items
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope a query to only include specific status
     */
    public function scopeForStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope a query to only include overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE);
    }

    /**
     * Scope a query to only include sent invoices
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE ||
               ($this->status === self::STATUS_SENT && $this->due_date && $this->due_date->isPast());
    }

    /**
     * Check if invoice is sent
     */
    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * Check if invoice is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark as overdue
     */
    public function markAsOverdue(): void
    {
        $this->update([
            'status' => self::STATUS_OVERDUE,
        ]);
    }

    /**
     * Cancel invoice
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmount(): string
    {
        return number_format((float) $this->total_amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'badge-secondary',
            self::STATUS_SENT => 'badge-primary',
            self::STATUS_PAID => 'badge-success',
            self::STATUS_OVERDUE => 'badge-danger',
            self::STATUS_CANCELLED => 'badge-secondary',
            self::STATUS_REFUNDED => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    /**
     * Generate invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = config('hadi-payment.invoice.prefix', 'INV');
        $year = date('Y');
        $month = date('m');

        $lastInvoice = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return $prefix . $year . $month . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
