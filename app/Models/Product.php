<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->sharpen(10);

        $this->addMediaConversion('small')
            ->width(480)
            ->sharpen(10);

        $this->addMediaConversion('large')
            ->width(1200)
            ->sharpen(10);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variationTypes(): HasMany
    {
        return $this->hasMany(VariationType::class, 'product_id');
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    public function scopeForVendor(Builder $query): Builder
    {
        return $query->where('created_by', auth()->user()->id);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProductStatusEnum::PUBLISHED);
    }
}
