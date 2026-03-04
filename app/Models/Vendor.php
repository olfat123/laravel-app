<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Vendor extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id',
        'status',
        'store_name',
        'store_slug',
        'store_description',
        'store_address',
        'cover_image',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_swift_code',
        'bank_iban',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(150)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(400)
            ->nonQueued();
    }

    public function getRouteKeyName(): string
    {
        return 'store_slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'vendor_user_id', 'user_id');
    }
}
