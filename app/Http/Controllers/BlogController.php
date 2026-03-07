<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Setting;
use App\Http\Resources\PostListResource;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::published()
            ->with(['author', 'category'])
            ->latest('published_at');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        $posts = $query->paginate(9)->withQueryString();

        $categories = PostCategory::orderBy('name')->get()->map(fn ($c) => [
            'id'      => $c->id,
            'name'    => $c->name,
            'name_ar' => $c->name_ar,
            'slug'    => $c->slug,
        ]);

        $blogBgPath = Setting::get('blog_banner_image_url', '');
        $banner = [
            'title'     => Setting::get('blog_banner_title', 'Stories, Tips & Style Guides'),
            'subtitle'  => Setting::get('blog_banner_subtitle', ''),
            'image_url' => $blogBgPath ? Storage::url($blogBgPath) : '',
        ];

        return Inertia::render('Blog/Index', [
            'posts'          => PostListResource::collection($posts),
            'categories'     => $categories,
            'activeCategory' => $request->category,
            'banner'         => $banner,
        ]);
    }

    public function show(Post $post)
    {
        abort_if($post->status !== 'published', 404);

        $post->load(['author', 'category']);

        $relatedPosts = Post::published()
            ->with(['author', 'category'])
            ->where('id', '!=', $post->id)
            ->when($post->post_category_id, fn ($q) =>
                $q->where('post_category_id', $post->post_category_id)
            )
            ->latest('published_at')
            ->take(3)
            ->get();

        return Inertia::render('Blog/Show', [
            'post'         => (new PostResource($post))->resolve(),
            'relatedPosts' => PostListResource::collection($relatedPosts)->resolve(),
        ]);
    }
}
