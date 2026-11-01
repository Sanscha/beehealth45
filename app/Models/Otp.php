<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'otp',
        'type',
        'expires_at',
        'is_verified',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Generate and store a 6-digit OTP for given email.
     */
    public static function generate(string $email, string $type = 'registration'): self
    {
        // Invalidate old OTPs for this email and type
        static::where('email', $email)
            ->where('type', $type)
            ->where('is_verified', false)
            ->delete();

        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        return static::create([
            'email' => $email,
            'otp' => $code,
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(15),
            'is_verified' => false,
        ]);
    }

    /**
     * Validate an OTP.
     */
    public static function verify(string $email, string $otp, string $type = 'registration'): bool
    {
        $record = static::where('email', $email)
            ->where('otp', trim($otp))
            ->where('type', $type)
            ->where('is_verified', false)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$record) {
            return false;
        }

        $record->is_verified = true;
        $record->save();

        return true;
    }
}
