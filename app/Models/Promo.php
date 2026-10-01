<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'amount',
        'max_usages',
        'current_usages',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Check if promo is currently valid for use.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        if ($this->max_usages !== null && $this->current_usages >= $this->max_usages) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount based on subtotal.
     */
    public function calculateDiscount($subtotal): int
    {
        if ($this->discount_type === 'percentage') {
            return (int) round(($this->amount / 100) * $subtotal);
        }

        return (int) min($this->amount, $subtotal);
    }
}
