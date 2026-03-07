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
use App\Models\Category;
use App\Models\ProductView;
use App\Models\ProductReview;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Enums\OrderStatusEnum;
use Illuminate\Support\Facades\Storage;

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
            ->featured()
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

        $heroBgPath = Setting::get('hero_bg_image_url', '');
        $hero = [
            'badge'            => Setting::get('hero_badge', 'New Collection'),
            'heading'          => Setting::get('hero_heading', 'Discover Your'),
            'heading2'         => Setting::get('hero_heading2', 'Perfect Style'),
            'subtext'          => Setting::get('hero_subtext', ''),
            'cta_shop_label'   => Setting::get('hero_cta_shop_label', 'Shop Now'),
            'cta_browse_label' => Setting::get('hero_cta_browse_label', 'Browse Departments'),
            'bg_image_url'     => $heroBgPath ? Storage::url($heroBgPath) : '',
        ];

        $sections = [
            'departments'       => Setting::get('show_departments', '1') === '1',
            'featured_products' => Setting::get('show_featured_products', '1') === '1',
            'best_sellers'      => Setting::get('show_best_sellers', '1') === '1',
            'recently_viewed'   => Setting::get('show_recently_viewed', '1') === '1',
            'blog_posts'        => Setting::get('show_blog_posts', '1') === '1',
        ];

        $featuredCategories = Category::where('active', true)
            ->whereNotNull('image')
            ->with('department:id,name')
            ->orderBy('name')
            ->take(12)
            ->get()
            ->map(fn ($c) => [
                'id'         => $c->id,
                'name'       => $c->name,
                'image_url'  => Storage::url($c->image),
                'department' => $c->department?->name,
            ]);

        return Inertia::render('Home', [
            'departments'          => $departments,
            'featuredCategories'   => $featuredCategories,
            'featuredProducts'     => ProductListResource::collection($featuredProducts),
            'mostSellingProducts'  => ProductListResource::collection($mostSellingProducts),
            'latestViewedProducts' => ProductListResource::collection($this->getRecentlyViewedProducts()),
            'latestPosts'          => PostListResource::collection(
                Post::published()->with(['author', 'category'])->latest('published_at')->take(3)->get()
            ),
            'hero'     => $hero,
            'sections' => $sections,
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
        $this->trackProductView($product);

        $relatedProducts = Product::query()
            ->forWebsite()
            ->withCount('variationTypes')
            ->with(['department', 'category', 'user.vendor'])
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->latest()
            ->take(4)
            ->get();

        $reviews = $product->reviews()
            ->where('is_approved', true)
            ->with('user:id,name')
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'body'       => $r->body,
                'created_at' => $r->created_at->toISOString(),
                'user'       => ['id' => $r->user->id, 'name' => $r->user->name],
            ]);

        return Inertia::render('Product/Show', [
            'product'          => new ProductResource($product),
            'variationOptions' => request('options', []),
            'relatedProducts'  => ProductListResource::collection($relatedProducts),
            'reviews'          => $reviews,
            'canReview'        => $this->userCanReview($product->id),
            'userReview'       => auth()->check()
                ? ProductReview::where('product_id', $product->id)
                    ->where('user_id', auth()->id())
                    ->first(['id', 'rating', 'body', 'is_approved', 'created_at'])
                : null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────────

    /**
     * Record a product view for the current visitor (auth user or guest session).
     */
    private function trackProductView(Product $product): void
    {
        if (auth()->check()) {
            ProductView::updateOrCreate(
                ['product_id' => $product->id, 'user_id' => auth()->id()],
                ['viewed_at' => now()]
            );
            return;
        }

        $viewedIds = session('viewed_product_ids', []);
        $viewedIds = array_values(array_diff($viewedIds, [$product->id]));
        array_unshift($viewedIds, $product->id);
        session(['viewed_product_ids' => array_slice($viewedIds, 0, 20)]);
    }

    /**
     * Return recently-viewed products for the current visitor.
     */
    private function getRecentlyViewedProducts()
    {
        $base = Product::query()
            ->forWebsite()
            ->withCount('variationTypes')
            ->with(['department', 'category', 'user.vendor']);

        if (auth()->check()) {
            $ids = ProductView::where('user_id', auth()->id())
                ->latest('viewed_at')
                ->take(8)
                ->pluck('product_id');

            if ($ids->isEmpty()) {
                return collect();
            }

            return $base->whereIn('id', $ids)
                ->orderByRaw('FIELD(id, ' . $ids->implode(',') . ')')
                ->get();
        }

        $ids = session('viewed_product_ids', []);
        if (empty($ids)) {
            return collect();
        }

        return $base->whereIn('id', $ids)->take(8)->get();
    }

    /**
     * Whether the authenticated user is eligible to leave a review for a product.
     */
    private function userCanReview(int $productId): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $userId = auth()->id();

        $hasPurchased = OrderItem::where('product_id', $productId)
            ->whereHas('order', fn ($q) => $q
                ->where('user_id', $userId)
                ->whereIn('status', [
                    OrderStatusEnum::Completed->value,
                    OrderStatusEnum::Delivered->value,
                ])
            )
            ->exists();

        if (! $hasPurchased) {
            return false;
        }

        return ! ProductReview::where('product_id', $productId)
            ->where('user_id', $userId)
            ->exists();
    }
}
