<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'name', 'price', 'quota', 'sold_count',
        'color', 'zone_label', 'description', 'wristband_color',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quota' => 'integer',
        'sold_count' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function getAvailableQuotaAttribute(): int
    {
        return max(0, $this->quota - $this->sold_count);
    }

    public function isSoldOut(): bool
    {
        return $this->available_quota <= 0;
    }
}
