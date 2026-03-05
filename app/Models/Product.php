<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Carbon\Carbon;
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

    public function scopeForWebsite(Builder $query): Builder
    {
        return $query->active();
    }

    /**
     * Whether the sale price is currently active.
     */
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

    /**
     * Returns the effective price (sale price if active, otherwise regular price).
     */
    public function getActivePriceAttribute(): float
    {
        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->price;
    }

    protected $fillable = [
        'title',
        'title_ar',
        'slug',
        'description',
        'description_ar',
        'price',
        'sale_price',
        'sale_start',
        'sale_end',
        'quantity',
        'status',
        'department_id',
        'category_id',
        'created_by',
    ];

    protected $casts = [
        'sale_start' => 'datetime',
        'sale_end'   => 'datetime',
    ];

    public function getPriceForOptions(array $optionIds)
    {
        $optionIds = array_values($optionIds);
        sort($optionIds);
        foreach ($this->variations as $variation) {
            $variationOptionIds = $variation->variation_type_option_ids ?? [];
            sort($variationOptionIds);
            if ($variationOptionIds === $optionIds) {
                return $variation->price !== null ? $variation->price : $this->price;
            }
        }

        return $this->price;
    }
}
