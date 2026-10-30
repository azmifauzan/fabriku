<?php

use App\Models\AdminUser;
use App\Models\BlogPost;
use Database\Seeders\BlogSeeder;

beforeEach(function () {
    AdminUser::factory()->create(['email' => 'admin@fabriku.id']);
});

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

it('publishes the volume-researched keyword batch', function () {
    $this->seed(BlogSeeder::class);

    $slugs = [
        'kartu-stok-barang-contoh-cara-mengisi-template-excel',
        'cara-menghitung-hpp-makanan-per-porsi',
        'cara-menentukan-harga-jual-makanan-per-pcs',
        'contoh-laporan-penjualan-harian-toko-sembako',
    ];

    foreach ($slugs as $slug) {
        $post = BlogPost::where('slug', $slug)->first();

        expect($post)->not->toBeNull("missing post: {$slug}");
        expect(str_word_count(strip_tags($post->content)))->toBeGreaterThan(700);
        expect(strlen($post->meta_description))->toBeLessThanOrEqual(160);
        expect(strlen($post->meta_title))->toBeLessThanOrEqual(60);
    }
});

it('targets the measured search phrases in the revised posts', function () {
    $this->seed(BlogSeeder::class);

    $phrases = [
        'cara-menghitung-hpp-produk-umkm' => ['HPP dan Harga Jual', 'HPP per Unit', 'HPP Produksi'],
        'cara-membuat-laporan-penjualan-umkm-sederhana' => ['Contoh Laporan Penjualan Harian', 'laporan penjualan harian di Excel'],
        'cara-membuat-bom-produksi-konveksi-per-ukuran-warna' => ['Contoh Bill of Material', 'Template Bill of Material'],
        'cara-menghitung-harga-jasa-servis-kecil-agar-tidak-rugi' => ['Cara Menghitung HPP Jasa'],
    ];

    foreach ($phrases as $slug => $expected) {
        $content = BlogPost::where('slug', $slug)->value('content');

        foreach ($expected as $phrase) {
            expect($content)->toContain($phrase);
        }
    }
});

it('keeps the original publish date when re-seeding', function () {
    $this->seed(BlogSeeder::class);
    $post = BlogPost::where('slug', 'cara-menghitung-hpp-produk-umkm')->first();
    $post->update(['published_at' => now()->subYear()]);

    $this->seed(BlogSeeder::class);

    expect($post->fresh()->published_at->isSameDay(now()->subYear()))->toBeTrue();
});

it('links every seeded post from at least one other post', function () {
    $this->seed(BlogSeeder::class);

    $linked = BlogPost::all()
        ->flatMap(function (BlogPost $post) {
            preg_match_all('#\]\(/blog/([a-z0-9-]+)\)#', $post->content, $matches);

            return collect($matches[1])->reject(fn (string $slug) => $slug === $post->slug);
        })
        ->unique();

    $orphans = BlogPost::pluck('slug')->diff($linked)->values()->all();

    expect($orphans)->toBe([]);
});

it('keeps landing page titles within 60 characters', function () {
    $long = collect(config('landing_pages'))
        ->filter(fn (array $page) => strlen($page['meta_title']) > 60)
        ->keys()
        ->all();

    expect($long)->toBe([]);
});
