<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    protected $casts = [
        'variation_type_option_ids' => 'json',
        'sale_start' => 'datetime',
        'sale_end'   => 'datetime',
    ];

    public function isOnSale(): bool
    {
        if ($this->sale_price === null) {
            return false;
        }

        $now = Carbon::now();

        if ($this->sale_start && $now->lt($this->sale_start)) {
            return false;
        }

        if ($this->sale_end && $now->gt($this->sale_end)) {
            return false;
        }

        return true;
    }

    public function getActivePriceAttribute(): float
    {
        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->price;
    }
}
