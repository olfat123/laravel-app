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
use App\Http\Resources\PostListResource;
use App\Models\Post;
use App\Models\ProductView;

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
            ->where('is_featured', true)
            ->withCount('variationTypes')
            ->with(['department', 'category', 'user.vendor'])
            ->latest()
            ->take(8)
            ->get();

        $mostSellingProducts = Product::query()
            ->forWebsite()
            ->withCount('variationTypes')
            ->with(['department', 'category', 'user.vendor'])
            ->withSum('orderItems', 'quantity')
            ->orderByDesc('order_items_sum_quantity')
            ->take(8)
            ->get();

        $latestViewedProducts = collect();
        if (auth()->check()) {
            $viewedProductIds = ProductView::where('user_id', auth()->id())
                ->latest('viewed_at')
                ->take(8)
                ->pluck('product_id');

            if ($viewedProductIds->isNotEmpty()) {
                $latestViewedProducts = Product::query()
                    ->forWebsite()
                    ->withCount('variationTypes')
                    ->with(['department', 'category', 'user.vendor'])
                    ->whereIn('id', $viewedProductIds)
                    ->orderByRaw('FIELD(id, ' . $viewedProductIds->implode(',') . ')')
                    ->get();
            }
        } else {
            $sessionViewedIds = session('viewed_product_ids', []);
            if (!empty($sessionViewedIds)) {
                $latestViewedProducts = Product::query()
                    ->forWebsite()
                    ->withCount('variationTypes')
                    ->with(['department', 'category', 'user.vendor'])
                    ->whereIn('id', $sessionViewedIds)
                    ->take(8)
                    ->get();
            }
        }

        return Inertia::render('Home', [
            'departments'           => $departments,
            'featuredProducts'      => ProductListResource::collection($featuredProducts),
            'mostSellingProducts'   => ProductListResource::collection($mostSellingProducts),
            'latestViewedProducts'  => ProductListResource::collection($latestViewedProducts),
            'latestPosts'           => PostListResource::collection(
                Post::published()->with(['author', 'category'])->latest('published_at')->take(3)->get()
            ),
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
        // Track the view
        if (auth()->check()) {
            ProductView::updateOrCreate(
                ['product_id' => $product->id, 'user_id' => auth()->id(), 'session_id' => null],
                ['viewed_at' => now()]
            );
        } else {
            $sessionKey = 'viewed_product_ids';
            $viewedIds = session($sessionKey, []);
            $viewedIds = array_values(array_diff($viewedIds, [$product->id]));
            array_unshift($viewedIds, $product->id);
            session([$sessionKey => array_slice($viewedIds, 0, 20)]);
        }

        $relatedProducts = Product::query()
            ->forWebsite()
            ->withCount('variationTypes')
            ->with(['department', 'category', 'user.vendor'])
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('Product/Show', [
            'product'          => new ProductResource($product),
            'variationOptions' => request('options', []),
            'relatedProducts'  => ProductListResource::collection($relatedProducts),
        ]);
    }
}
