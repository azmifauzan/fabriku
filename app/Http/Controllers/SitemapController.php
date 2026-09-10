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
}
