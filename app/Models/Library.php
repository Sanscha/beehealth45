<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Library extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'image',
        'address',
        'city',
        'state',
        'pincode',
        'latitude',
        'longitude',
        'phone',
        'email',
        'description',
        'amenities',
        'total_seats',
        'registration_fee',
        'payment_status',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'total_seats' => 'integer',
            'registration_fee' => 'decimal:2',
        ];
    }

    /**
     * Get the owner/manager user for this library.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all seats belonging to this library.
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Get available/empty seats.
     */
    public function availableSeats(): HasMany
    {
        return $this->hasMany(Seat::class)->where('status', 'available');
    }

    /**
     * Get occupied seats.
     */
    public function occupiedSeats(): HasMany
    {
        return $this->hasMany(Seat::class)->where('status', 'occupied');
    }

    /**
     * Get all payments associated with this library.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Helper to generate a unique slug.
     */
    public static function createUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Scope to find nearby libraries given latitude and longitude.
     */
    public function scopeNearby(Builder $query, ?float $lat, ?float $lng, float $radiusKm = 50): Builder
    {
        if ($lat === null || $lng === null) {
            return $query;
        }

        // Haversine formula calculation for distance in kilometers
        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))";

        return $query->selectRaw("libraries.*, {$haversine} AS distance_km", [$lat, $lng, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km', 'asc');
    }
}
