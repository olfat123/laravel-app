<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductFilter
{
    public function __construct(protected Request $request) {}

    /**
     * Apply all active filters and sorting to the given query.
     */
    public function apply(Builder $query): Builder
    {
        // Always load the rating aggregates so ProductListResource can expose them
        $query->withAvg(['reviews' => fn($q) => $q->where('is_approved', true)], 'rating')
              ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)]);

        $this->search($query)
             ->byDepartment($query)
             ->byCategory($query)
             ->byStore($query)
             ->byPriceRange($query)
             ->sort($query);

        return $query;
    }

    protected function search(Builder $query): static
    {
        if ($search = $this->request->input('search')) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        return $this;
    }

    protected function byDepartment(Builder $query): static
    {
        if ($departmentId = $this->request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }

        return $this;
    }

    protected function byCategory(Builder $query): static
    {
        if ($categoryId = $this->request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        return $this;
    }

    protected function byStore(Builder $query): static
    {
        if ($storeId = $this->request->input('store_id')) {
            $query->where('created_by', $storeId);
        }

        return $this;
    }

    protected function byPriceRange(Builder $query): static
    {
        if ($min = $this->request->input('min_price')) {
            $query->where('price', '>=', (float) $min);
        }

        if ($max = $this->request->input('max_price')) {
            $query->where('price', '<=', (float) $max);
        }

        return $this;
    }

    protected function sort(Builder $query): static
    {
        match ($this->request->input('sort', 'newest')) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc'   => $query->orderBy('title', 'asc'),
            'name_desc'  => $query->orderBy('title', 'desc'),
            'top_rated'  => $query->orderByRaw(
                '(SELECT COALESCE(AVG(rating), 0) FROM product_reviews
                  WHERE product_reviews.product_id = products.id
                    AND product_reviews.is_approved = 1) DESC'
            ),
            default      => $query->latest(),
        };

        return $this;
    }
}
