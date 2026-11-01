<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'seat_id',
        'pass_code',
        'shift',
        'booking_date',
        'status',
    ];

    /**
     * Get the user that owns the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the seat associated with the booking.
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * Generate a unique pass code for entry/QR code.
     */
    public static function generatePassCode(): string
    {
        do {
            $code = 'LIB-'.date('Ymd').'-'.strtoupper(Str::random(5));
        } while (static::where('pass_code', $code)->exists());

        return $code;
    }
}
