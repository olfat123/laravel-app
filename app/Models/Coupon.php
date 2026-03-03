<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value'            => 'float',
        'min_order_amount' => 'float',
        'max_uses'         => 'integer',
        'used_count'       => 'integer',
        'expires_at'       => 'datetime',
        'is_active'        => 'boolean',
    ];

    /**
     * Validate the coupon against an order total and return an error string or null.
     */
    public function validate(float $orderTotal): ?string
    {
        if (!$this->is_active) {
            return 'This coupon is inactive.';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'This coupon has expired.';
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return 'This coupon has reached its usage limit.';
        }

        if ($this->min_order_amount !== null && $orderTotal < $this->min_order_amount) {
            return "This coupon requires a minimum order of " . number_format($this->min_order_amount, 2) . ".";
        }

        return null;
    }

    /**
     * Calculate the discount amount for a given order total.
     */
    public function calculateDiscount(float $orderTotal): float
    {
        if ($this->type === 'percentage') {
            return round($orderTotal * $this->value / 100, 4);
        }

        return min($this->value, $orderTotal);
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
