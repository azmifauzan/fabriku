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

it('publishes the next keyword batch', function () {
    $this->seed(BlogSeeder::class);

    $slugs = [
        'cara-stok-opname-toko-retail-tanpa-tutup-toko',
        'cara-mengelola-kontraktor-maklun-konveksi-agar-tidak-telat',
        'kapan-usaha-rumahan-butuh-sistem-pencatatan',
        'aplikasi-pencatatan-umkm-vs-excel-kapan-pindah',
    ];

    foreach ($slugs as $slug) {
        $post = BlogPost::where('slug', $slug)->first();

        expect($post)->not->toBeNull("missing post: {$slug}");
        expect($post->status)->toBe('published');
        expect(str_word_count(strip_tags($post->content)))->toBeGreaterThan(500);
        expect($post->meta_description)->not->toBeNull();
        expect(strlen($post->meta_description))->toBeLessThanOrEqual(160);
    }
});
