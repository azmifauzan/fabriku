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

it('only links to posts and landing pages that exist', function () {
    $this->seed(BlogSeeder::class);

    $slugs = BlogPost::pluck('slug')->all();
    $landing = array_keys(config('landing_pages'));
    $broken = [];

    foreach (BlogPost::all() as $post) {
        preg_match_all('#\]\(/(blog|aplikasi)/([a-z0-9-]+)\)#', $post->content, $matches, PREG_SET_ORDER);

        foreach ($matches as [, $type, $slug]) {
            if (! in_array($slug, $type === 'blog' ? $slugs : $landing, true)) {
                $broken[] = "{$post->slug} -> /{$type}/{$slug}";
            }
        }
    }

    expect($broken)->toBe([]);
});

it('publishes the seasonal and category keyword batch', function () {
    $this->seed(BlogSeeder::class);

    $slugs = [
        'cara-membuat-bom-produksi-konveksi-per-ukuran-warna',
        'cara-mengatur-jadwal-teknisi-bengkel-servis-agar-tidak-bentrok',
        'cara-melacak-sparepart-bahan-habis-pakai-jasa-servis',
        'cara-kelola-stok-reseller-dropship-kosmetik',
        'cara-menyiapkan-stok-umkm-menjelang-ramadan-lebaran',
        'cara-kelola-stok-banyak-cabang-outlet-umkm',
        'cara-mencatat-produksi-banyak-produk-satu-dapur-rumahan',
    ];

    foreach ($slugs as $slug) {
        $post = BlogPost::where('slug', $slug)->first();

        expect($post)->not->toBeNull("missing post: {$slug}");
        expect(str_word_count(strip_tags($post->content)))->toBeGreaterThan(600);
        expect(strlen($post->meta_description))->toBeLessThanOrEqual(160);
        expect(strlen($post->meta_title))->toBeLessThanOrEqual(60);
    }
});

it('keeps the original publish date when re-seeding', function () {
    $this->seed(BlogSeeder::class);
    $post = BlogPost::where('slug', 'cara-menghitung-hpp-produk-umkm')->first();
    $post->update(['published_at' => now()->subYear()]);

    $this->seed(BlogSeeder::class);

    expect($post->fresh()->published_at->isSameDay(now()->subYear()))->toBeTrue();
});
