<?php

namespace App\Models;

use App\Models\VariationType;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VariationTypeOption extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    public $timestamps = false;

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

    public function variationType(): BelongsTo
    {
        return $this->belongsTo(VariationType::class);
    }

}
