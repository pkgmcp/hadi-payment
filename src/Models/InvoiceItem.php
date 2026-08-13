<?php

declare(strict_types=1);

namespace Hadi\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invoice_id
 * @property string $description
 * @property string $quantity
 * @property string $unit_price
 * @property string $tax_rate
 * @property string $discount_rate
 * @property string $line_total
 * @property string $tax_amount
 * @property string $discount_amount
 * @property string $final_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Invoice $invoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|static where(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static static create(array $attributes = [])
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if (! array_key_exists('line_total', $item->attributes)) {
                $item->attributes['line_total'] = $item->calculateLineTotal();
            }
            if (! array_key_exists('final_amount', $item->attributes)) {
                $item->attributes['final_amount'] = $item->calculateFinalAmount();
            }
        });
    }

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'discount_rate',
        'line_total',
        'tax_amount',
        'discount_amount',
        'final_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the item
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Calculate line total
     */
    public function calculateLineTotal(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }

    /**
     * Calculate tax amount
     */
    public function calculateTaxAmount(): float
    {
        return $this->calculateLineTotal() * ((float) $this->tax_rate / 100);
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscountAmount(): float
    {
        return $this->calculateLineTotal() * ((float) $this->discount_rate / 100);
    }

    /**
     * Calculate final amount
     */
    public function calculateFinalAmount(): float
    {
        return $this->calculateLineTotal() + $this->calculateTaxAmount() - $this->calculateDiscountAmount();
    }

    /**
     * Update calculated amounts
     */
    public function updateCalculatedAmounts(): void
    {
        $this->update([
            'line_total' => $this->calculateLineTotal(),
            'tax_amount' => $this->calculateTaxAmount(),
            'discount_amount' => $this->calculateDiscountAmount(),
            'final_amount' => $this->calculateFinalAmount(),
        ]);
    }
}
