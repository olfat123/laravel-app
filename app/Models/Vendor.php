<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id',
        'status',
        'store_name',
        'store_address',
        'cover_image',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_swift_code',
        'bank_iban',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'vendor_user_id', 'user_id');
    }
}
