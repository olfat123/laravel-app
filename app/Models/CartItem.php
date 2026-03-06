<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'variation_type_option_ids',
        'quantity',
        'price',
    ];

    protected $casts = [
        'variation_type_option_ids' => 'array',
    ];

}
