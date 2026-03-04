<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Product;
use App\Models\Department;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Filters\ProductFilter;
use App\Http\Resources\ProductListResource;

class StoreController extends Controller
{
    public function show(Request $request, Vendor $vendor)
    {
        $query = Product::query()
            ->forWebsite()
            ->withCount('variationTypes')
            ->with(['department', 'category', 'user.vendor'])
            ->where('created_by', $vendor->user_id);

        // Apply all filters except store (already scoped to this vendor)
        (new ProductFilter($request))->apply($query);

        $products = $query->paginate(12)->withQueryString();

        // Only departments that this vendor actually has products in
        $vendorDeptIds = Product::forWebsite()
            ->where('created_by', $vendor->user_id)
            ->distinct()
            ->pluck('department_id');

        $departments = Department::whereIn('id', $vendorDeptIds)
            ->orderBy('name')
            ->with(['categories' => fn($q) => $q->orderBy('name')])
            ->get()
            ->map(fn($d) => [
                'id'         => $d->id,
                'name'       => $d->name,
                'categories' => $d->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
            ]);

        return Inertia::render('Store/Show', [
            'vendor'      => [
                'store_name'        => $vendor->store_name,
                'store_slug'        => $vendor->store_slug,
                'store_address'     => $vendor->store_address,
                'store_description' => $vendor->store_description,
                'banner_url'        => $vendor->getFirstMediaUrl('banner', 'large') ?: $vendor->getFirstMediaUrl('banner') ?: null,
                'cover_image'       => $vendor->cover_image,
            ],
            'products'    => ProductListResource::collection($products),
            'departments' => $departments,
            'filters'     => (object) $request->only(['search', 'department_id', 'category_id', 'min_price', 'max_price', 'sort']),
        ]);
    }
}
