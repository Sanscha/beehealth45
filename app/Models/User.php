<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'phone',
        'password',
        'role',
        'status',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the library owned by this user (if role is manager).
     */
    public function library(): HasOne
    {
        return $this->hasOne(Library::class, 'user_id');
    }

    /**
     * Get bookings associated with the user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get payments made by this user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get payments collected by this staff member.
     */
    public function collectedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'collected_by');
    }

    /**
     * Check if user is staff or admin.
     */
    public function isStaff(): bool
    {
        return in_array($this->role, ['staff', 'admin'], true);
    }

    /**
     * Check if user is a library manager/owner.
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a regular member / student.
     */
    public function isMember(): bool
    {
        return in_array($this->role, ['member', 'user'], true);
    }

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    /**
     * Check if user's email has been verified via OTP.
     */
    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }
}
