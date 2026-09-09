<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::where('status', 'published')
            ->with('category', 'tags')
            ->when($request->category, fn ($q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)))
            ->when($request->tag, fn ($q, $slug) => $q->whereHas('tags', fn ($q) => $q->where('slug', $slug)))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $isFiltered = $request->filled('category') || $request->filled('tag');
        $page = (int) $request->query('page', 1);

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'categories' => BlogCategory::orderBy('name')->get(['name', 'slug']),
            'activeCategory' => $request->category,
            'activeTag' => $request->tag,
            // Filter views collapse onto the bare listing; real pagination pages own
            // their canonical so page 2+ never claims to be page 1.
            'canonical' => $isFiltered || $page <= 1 ? url('/blog') : url('/blog?page='.$page),
            // Canonical alone does not stop Google from spending crawl budget on the
            // unbounded tag x page combinations — 13 of 17 unindexed URLs were these.
            'noindex' => $isFiltered,
        ]);
    }

    public function show(BlogPost $post)
    {
        abort_unless($post->status === 'published', 404);

        $post->load('category', 'tags', 'author');

        return Inertia::render('Blog/Show', [
            'post' => [
                'title' => $post->title,
                'content_html' => $post->content_html,
                'excerpt' => $post->excerpt,
                'featured_image_url' => $post->featured_image_url,
                'published_at' => $post->published_at,
                'updated_at' => $post->updated_at,
                'meta_title' => $post->meta_title ?? $post->title,
                'meta_description' => $post->meta_description ?? $post->excerpt,
                'category' => $post->category?->only(['name', 'slug']),
                'tags' => $post->tags->map->only(['name', 'slug']),
                'author_name' => $post->author->name,
                'canonical' => url("/blog/{$post->slug}"),
            ],
        ]);
    }
}
