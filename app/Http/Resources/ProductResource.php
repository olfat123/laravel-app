<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'slug' => $this->slug,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'sale_start' => $this->sale_start?->toISOString(),
            'sale_end' => $this->sale_end?->toISOString(),
            'active_price' => $this->active_price,
            'is_on_sale' => $this->isOnSale(),
            'quantity' => $this->quantity,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'image' => $this->getFirstMediaUrl('images', 'small') ?: null,
            'images' => $this->getMedia('images')->map(fn ($media) => [
                'id'           => $media->id,
                'small'        => $media->getUrl('small'),
                'large'        => $media->getUrl('large'),
                'thumb'        => $media->getUrl('thumb'),
                'original_url' => $media->getUrl(),
            ]),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'department' => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            'variationTypes' => $this->variationTypes->map(fn ($variationType) => [
                'id'      => $variationType->id,
                'name'    => $variationType->name,
                'name_ar' => $variationType->name_ar,
                'type'    => $variationType->type,
                'options' => $variationType->options->map(fn ($option) => [
                    'id'      => $option->id,
                    'name'    => $option->name,
                    'name_ar' => $option->name_ar,
                    'images'  => $option->getMedia('images')->map(fn ($media) => [
                        'id'           => $media->id,
                        'small'        => $media->getUrl('small'),
                        'large'        => $media->getUrl('large'),
                        'thumb'        => $media->getUrl('thumb'),
                        'original_url' => $media->getUrl(),
                    ]),
                ]),
            ]),
            'variations' => $this->variations->map(fn ($variation) => [
                'id'                        => $variation->id,
                'variation_type_option_ids' => $variation->variation_type_option_ids,
                'price'                     => $variation->price,
                'sale_price'                => $variation->sale_price,
                'sale_start'                => $variation->sale_start?->toISOString(),
                'sale_end'                  => $variation->sale_end?->toISOString(),
                'is_on_sale'                => $variation->isOnSale(),
                'active_price'              => $variation->active_price,
                'quantity'                  => $variation->quantity,
            ]),
        ];    
    }
}
