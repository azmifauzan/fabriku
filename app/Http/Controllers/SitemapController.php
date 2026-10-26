<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            // No lastmod for static pages on purpose: emitting now() on every request
            // trains Google to ignore lastmod for this domain, including the accurate
            // updated_at values on blog posts below.
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('blog.index'), 'priority' => '0.8'],
            ['loc' => route('legal.privacy'), 'priority' => '0.3'],
            ['loc' => route('legal.terms'), 'priority' => '0.3'],
        ])->concat(
            collect(array_keys(config('landing_pages')))
                ->map(fn (string $slug) => ['loc' => route('landing.category', $slug), 'priority' => '0.9'])
        )->concat(
            // Only canonical post URLs — category/tag filter views are excluded on purpose,
            // their canonical points back to /blog so they must not appear here too.
            BlogPost::where('status', 'published')
                ->orderByDesc('published_at')
                ->get(['slug', 'updated_at'])
                ->map(fn (BlogPost $post) => [
                    'loc' => route('blog.show', $post->slug),
                    'lastmod' => $post->updated_at,
                    'priority' => '0.6',
                ])
        );

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Served by the app instead of a static public/robots.txt: Apache serves existing
     * files before Laravel, so a static file would shadow every storefront's own
     * /robots.txt (and its sitemap) on tenant subdomains and custom domains.
     */
    public function robots(): Response
    {
        return response("User-agent: *\nDisallow: /auth/\nDisallow: /login\nDisallow: /register\n\nSitemap: ".route('sitemap')."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
