<?php

declare(strict_types=1);

namespace Hadi\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $payment_problem_id
 * @property int|null $user_id
 * @property string $comment
 * @property bool $is_internal
 * @property array|null $attachments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PaymentProblemComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_problem_id',
        'user_id',
        'comment',
        'is_internal',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    /**
     * Get the payment problem that owns the comment
     */
    public function paymentProblem(): BelongsTo
    {
        return $this->belongsTo(PaymentProblem::class);
    }

    /**
     * Get the user that owns the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    /**
     * Scope a query to only include internal comments
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope a query to only include public comments
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }
}
