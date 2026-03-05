<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'title_ar'     => $this->title_ar,
            'excerpt'      => $this->excerpt,
            'excerpt_ar'   => $this->excerpt_ar,
            'content'      => $this->content,
            'content_ar'   => $this->content_ar,
            'slug'         => $this->slug,
            'status'       => $this->status,
            'published_at' => $this->published_at?->toDateString(),
            'cover_url'    => $this->cover_url,
            'cover_thumb'  => $this->cover_thumb,
            'author'       => $this->author?->name,
            'category'     => $this->category ? [
                'id'      => $this->category->id,
                'name'    => $this->category->name,
                'name_ar' => $this->category->name_ar,
                'slug'    => $this->category->slug,
            ] : null,
        ];
    }
}
