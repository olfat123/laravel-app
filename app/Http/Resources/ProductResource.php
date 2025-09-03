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
            'slug' => $this->slug,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'description' => $this->description,
            'image' => $this->getFirstMediaUrl('images', 'small') ?: null,
            'images' => $this->getMedia('images')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'small' => $media->getUrl('small'),
                    'large' => $media->getUrl('large'),
                    'thumb' => $media->getUrl('thumb'),
                    'original_url' => $media->getUrl(),
                ];
            }),
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
            'variationTypes' => $this->variationTypes->map(function ($variationType) {
                return [
                    'id' => $variationType->id,
                    'name' => $variationType->name,
                    'type' => $variationType->type,
                    'options' => $variationType->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'name' => $option->name,
                            'images' => $option->getMedia('images')->map(function ($media) {
                                return [
                                    'id' => $media->id,
                                    'small' => $media->getUrl('small'),
                                    'large' => $media->getUrl('large'),
                                    'thumb' => $media->getUrl('thumb'),
                                    'original_url' => $media->getUrl(),
                                ];
                            }),
                        ];
                    }),
                ];
            }),
            'variations' => $this->variations->map(function ($variation) {
                return [
                    'id' => $variation->id,
                    'variation_type_option_ids' => $variation->variation_type_option_ids,
                    'price' => $variation->price,
                    'quantity' => $variation->quantity,
                    // 'is_default' => $variation->is_default,
                ];
            }),
        ];    
    }
}
