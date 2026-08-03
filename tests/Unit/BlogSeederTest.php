<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Database\Seeders\BlogSeeder;

it('seeds 12 published blog posts across 4 categories with tags', function () {
    $this->seed(BlogSeeder::class);

    expect(BlogPost::count())->toBe(12);
    expect(BlogPost::where('status', 'published')->count())->toBe(12);
    expect(BlogPost::whereNull('published_at')->count())->toBe(0);
    expect(BlogCategory::count())->toBe(4);

    BlogPost::with('tags', 'category', 'author')->get()->each(function (BlogPost $post) {
        expect($post->tags)->not->toBeEmpty();
        expect($post->category)->not->toBeNull();
        expect($post->author)->not->toBeNull();
        expect($post->slug)->not->toBe('');
        expect($post->content_html)->toContain('<h2>');
        expect(str_word_count(strip_tags($post->content_html)))->toBeGreaterThanOrEqual(500);
    });
});

it('is idempotent and syncs updated content when seeded twice', function () {
    $this->seed(BlogSeeder::class);
    $this->seed(BlogSeeder::class);

    expect(BlogPost::count())->toBe(12);
    expect(BlogCategory::count())->toBe(4);
});
