<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'title',
        'slug',
        'description',
        'banner_image',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function ticketTiers(): HasMany
    {
        return $this->hasMany(TicketTier::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the lowest active ticket tier price for display.
     */
    public function getLowestPriceAttribute(): ?string
    {
        $min = $this->ticketTiers->where('is_active', true)->min('price');
        return $min !== null ? number_format($min, 0, ',', '.') : null;
    }
}
