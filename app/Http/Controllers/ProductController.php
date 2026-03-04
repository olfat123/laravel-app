<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Department;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Filters\ProductFilter;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductListResource;

class ProductController extends Controller
{
    public function home()
    {
        $departments = Department::withCount('categories')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $featuredProducts = Product::query()
            ->forWebsite()
            ->withCount('variationTypes')
            ->with(['department', 'category', 'user.vendor'])
            ->latest()
            ->take(8)
            ->get();

        return Inertia::render('Home', [
            'departments'      => $departments,
            'featuredProducts' => ProductListResource::collection($featuredProducts),
        ]);
    }

    public function shop(Request $request)
    {
        $query = Product::query()
            ->forWebsite()
            ->withCount('variationTypes')
            ->with(['department', 'category', 'user.vendor']);

        (new ProductFilter($request))->apply($query);

        $products = $query->paginate(12)->withQueryString();

        // Departments with nested categories for the filter sidebar
        $departments = Department::where('active', true)
            ->orderBy('name')
            ->with(['categories' => fn($q) => $q->orderBy('name')])
            ->get()
            ->map(fn($d) => [
                'id'         => $d->id,
                'name'       => $d->name,
                'categories' => $d->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
            ]);

        // Active stores (vendors that have at least one published product)
        $stores = Vendor::orderBy('store_name')
            ->whereIn('user_id', Product::forWebsite()->select('created_by'))
            ->get()
            ->map(fn($v) => ['id' => $v->user_id, 'name' => $v->store_name]);

        return Inertia::render('Shop', [
            'products'    => ProductListResource::collection($products),
            'departments' => $departments,
            'stores'      => $stores,
            'filters'     => (object) $request->only(['search', 'department_id', 'category_id', 'store_id', 'min_price', 'max_price', 'sort']),
        ]);
    }

    public function show(Product $product)
    {
        return Inertia::render('Product/Show', [
            'product' => new ProductResource($product),
            'variationOptions' => request('options', []),
        ]);
       
    }
}
