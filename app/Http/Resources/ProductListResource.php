<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
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
            'has_variations' => ($this->variation_types_count ?? 0) > 0,
            'image_url' => $this->getFirstMediaUrl('images', 'small') ?: null,
            'user' => $this->user ? [
                'id'         => $this->user->id,
                'name'       => $this->user->name,
                'store_slug' => $this->user->vendor?->store_slug,
            ] : null,
            'department' => $this->department ? [
                'id'   => $this->department->id,
                'name' => $this->department->name,
            ] : null,
            'category' => $this->category ? [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'avg_rating'   => round((float) ($this->reviews_avg_rating ?? 0), 1),
            'review_count' => (int) ($this->reviews_count ?? 0),
        ];
    }
}
