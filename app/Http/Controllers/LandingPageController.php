<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = config("landing_pages.{$slug}");

        abort_unless(is_array($page), 404);

        $relatedPosts = BlogPost::query()
            ->where('status', 'published')
            ->whereHas('tags', fn ($q) => $q->whereIn('slug', $page['blog_tags']))
            ->latest('published_at')
            ->limit(3)
            ->get(['slug', 'title', 'excerpt']);

        return Inertia::render('Landing/Category', [
            'page' => [
                ...collect($page)->except('blog_tags')->all(),
                'category_label' => config("business.categories.{$page['category']}.label"),
            ],
            'relatedPosts' => $relatedPosts,
            'otherPages' => collect(config('landing_pages'))
                ->except($slug)
                ->map(fn (array $other, string $otherSlug) => [
                    'slug' => $otherSlug,
                    'label' => config("business.categories.{$other['category']}.label"),
                ])
                ->values(),
            'canonical' => route('landing.category', $slug),
        ]);
    }
}
