<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Database\Seeders\BlogSeeder;

it('seeds 6 published blog posts across 3 categories with tags', function () {
    $this->seed(BlogSeeder::class);

    expect(BlogPost::count())->toBe(6);
    expect(BlogPost::where('status', 'published')->count())->toBe(6);
    expect(BlogPost::whereNull('published_at')->count())->toBe(0);
    expect(BlogCategory::count())->toBe(3);

    BlogPost::with('tags', 'category', 'author')->get()->each(function (BlogPost $post) {
        expect($post->tags)->not->toBeEmpty();
        expect($post->category)->not->toBeNull();
        expect($post->author)->not->toBeNull();
        expect($post->slug)->not->toBe('');
        expect($post->content_html)->toContain('<h2>');
    });
});

it('is idempotent when seeded twice', function () {
    $this->seed(BlogSeeder::class);
    $this->seed(BlogSeeder::class);

    expect(BlogPost::count())->toBe(6);
    expect(BlogCategory::count())->toBe(3);
});
