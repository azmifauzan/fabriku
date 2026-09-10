<?php

use App\Models\BlogPost;
use Database\Seeders\BlogSeeder;

it('gives every seeded post at least one link to another post', function () {
    $this->seed(BlogSeeder::class);

    $missing = BlogPost::where('status', 'published')
        ->get()
        ->filter(fn (BlogPost $post) => ! str_contains($post->content, ']('.'/blog/'))
        ->pluck('slug')
        ->all();

    expect($missing)->toBe([]);
});
