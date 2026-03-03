<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Department;
use App\Models\Product;
use Illuminate\Http\Request;
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
        $products = Product::query()
            ->forWebsite()
            ->withCount('variationTypes')
            ->paginate(12);

        return Inertia::render('Shop', [
            'products' => ProductListResource::collection($products),
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
