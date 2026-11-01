<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'library_id',
        'booking_id',
        'plan_id',
        'collected_by',
        'amount',
        'type',
        'currency',
        'payment_method',
        'transaction_id',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the user who paid.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the library associated with this payment (for library owner registration).
     */
    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    /**
     * Get the booking linked to this payment.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the plan linked to this payment.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get staff who collected payment at counter.
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Helper to generate transaction reference ID.
     */
    public static function generateTransactionId(string $prefix = 'PAY'): string
    {
        return $prefix . '-' . date('YmdHis') . '-' . strtoupper(Str::random(4));
    }

    /**
     * Scope for library registration payments.
     */
    public function scopeLibraryRegistrations(Builder $query): Builder
    {
        return $query->where('type', 'library_registration');
    }
}
