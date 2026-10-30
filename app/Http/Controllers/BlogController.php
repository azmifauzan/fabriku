<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
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
            'groups' => $isFiltered || $page > 1 ? [] : $this->groupsByBusiness(),
        ]);
    }

    /**
     * Per `/aplikasi/*` landing page, its newest posts (tag overlap with `blog_tags`) so `/blog`
     * links down to each money page. A post matching two landings shows under both. Landings
     * with no matching post are left out.
     *
     * @return list<array{slug: string, label: string, total: int, posts: list<array{slug: string, title: string}>}>
     */
    private function groupsByBusiness(): array
    {
        $posts = BlogPost::where('status', 'published')
            ->with('tags:id,slug')
            ->latest('published_at')
            ->get(['id', 'slug', 'title']);

        return collect(config('landing_pages'))
            ->map(function (array $page, string $slug) use ($posts): array {
                $matching = $posts->filter(fn (BlogPost $post): bool => $post->tags->pluck('slug')->intersect($page['blog_tags'])->isNotEmpty());

                return [
                    'slug' => $slug,
                    'label' => config("business.categories.{$page['category']}.label"),
                    'total' => $matching->count(),
                    'posts' => $matching->take(3)->map->only(['slug', 'title'])->values()->all(),
                ];
            })
            ->filter(fn (array $group): bool => $group['total'] > 0)
            ->values()
            ->all();
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
                'og_image_url' => $post->featured_image ? route('blog.og-image', $post) : $this->generatedOgImage($post),
                'published_at' => $post->published_at,
                'updated_at' => $post->updated_at,
                'meta_title' => $post->meta_title ?? $post->title,
                'meta_description' => $post->meta_description ?? $post->excerpt,
                'category' => $post->category?->only(['name', 'slug']),
                'tags' => $post->tags->map->only(['name', 'slug']),
                'author_name' => $post->author->name,
                'canonical' => url("/blog/{$post->slug}"),
            ],
            'landing' => $this->landingFor($post),
        ]);
    }

    /**
     * The `/aplikasi/*` landing page whose `blog_tags` overlap the post's tags the most, for the
     * CTA box under the article. Null on a tie or no overlap, so the box falls back to a plain
     * register link instead of pointing at a landing page that does not fit.
     *
     * @return array{slug: string, label: string}|null
     */
    private function landingFor(BlogPost $post): ?array
    {
        $tags = $post->tags->pluck('slug');

        $scores = collect(config('landing_pages'))
            ->map(fn (array $page): int => $tags->intersect($page['blog_tags'])->count())
            ->filter()
            ->sortDesc();

        if ($scores->isEmpty() || $scores->count() > 1 && $scores->values()[0] === $scores->values()[1]) {
            return null;
        }

        $slug = $scores->keys()->first();

        return [
            'slug' => $slug,
            'label' => config('business.categories.'.config("landing_pages.{$slug}.category").'.label'),
        ];
    }

    /**
     * Path of the title card generated by `og:generate-images`, for posts without a featured
     * image. Null when no file exists yet (e.g. a post created after the last generation).
     */
    private function generatedOgImage(BlogPost $post): ?string
    {
        if ($post->slug !== basename($post->slug)) {
            return null;
        }

        $path = "images/og/blog/{$post->slug}.png";

        return is_file(public_path($path)) ? "/{$path}" : null;
    }

    /**
     * Stable og:image URL for a post's featured image. The image itself is a signed S3
     * URL that expires in minutes, but Threads/FB cache og:image URLs for days, so the
     * meta tag points here and every fetch is redirected to a freshly signed URL.
     */
    public function ogImage(BlogPost $post): RedirectResponse
    {
        abort_unless($post->status === 'published' && $post->featured_image, 404);

        return redirect()->away($post->featured_image_url);
    }
}
