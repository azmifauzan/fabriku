<?php

use App\Models\AdminUser;
use App\Models\BlogPost;
use App\Models\BusinessSite;
use App\Models\InventoryItem;
use App\Models\SiteProduct;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('ships a 1200x630 og image for the home page and every category landing page', function () {
    $paths = [public_path('images/og-default.png')];

    foreach (array_keys(config('landing_pages')) as $slug) {
        $paths[] = public_path("images/og/{$slug}.png");
    }

    foreach ($paths as $path) {
        expect(is_file($path))->toBeTrue("Missing {$path}, run php artisan og:generate-images");
        expect(array_slice(getimagesize($path), 0, 2))->toBe([1200, 630]);
    }
});

it('passes the category og image to the landing page', function () {
    $this->get(route('landing.category', 'konveksi'))
        ->assertInertia(fn ($page) => $page->where('page.og_image', '/images/og/konveksi.png'));
});

it('points a blog post og image at a stable redirect instead of the expiring signed url', function () {
    Storage::fake('fabriku_s3');
    $post = BlogPost::factory()->published()->create(['slug' => 'ada-gambar', 'featured_image' => 'blog/cover.jpg']);

    $this->get(route('blog.show', $post->slug))
        ->assertInertia(fn ($page) => $page->where('post.og_image_url', route('blog.og-image', $post)));

    $this->get(route('blog.og-image', $post))->assertRedirect();
});

it('has no blog og image route for posts without a featured image or drafts', function () {
    $noImage = BlogPost::factory()->published()->create(['featured_image' => null]);
    $draft = BlogPost::factory()->create(['status' => 'draft', 'featured_image' => 'blog/x.jpg']);

    $this->get(route('blog.show', $noImage->slug))
        ->assertInertia(fn ($page) => $page->where('post.og_image_url', null));
    $this->get(route('blog.og-image', $noImage))->assertNotFound();
    $this->get(route('blog.og-image', $draft))->assertNotFound();
});

it('rejects a blog featured image too small for social previews', function () {
    Storage::fake('fabriku_s3');
    $this->actingAs(AdminUser::factory()->create(), 'admin');

    $this->post(route('admin.blog.store'), [
        'title' => 'Gambar Kecil',
        'content' => 'Isi',
        'status' => 'draft',
        'featured_image' => UploadedFile::fake()->image('kecil.jpg', 400, 200),
    ])->assertSessionHasErrors('featured_image');
});

it('renders og image and url tags on a storefront product page', function () {
    $tenant = Tenant::factory()->create();
    $site = BusinessSite::factory()->create([
        'tenant_id' => $tenant->id,
        'slug' => 'toko-og',
        'mode' => 'produk',
        'status' => 'published',
        'profile' => ['name' => 'Toko OG'],
    ]);
    $item = InventoryItem::factory()->create(['tenant_id' => $tenant->id]);
    SiteProduct::create([
        'tenant_id' => $tenant->id,
        'inventory_item_id' => $item->id,
        'slug' => 'kaos-polos',
        'title' => 'Kaos Polos',
        'image_path' => 'https://cdn.example.com/kaos.jpg',
    ]);

    $this->get('http://toko-og.fabriku.biz.id/produk/kaos-polos')
        ->assertOk()
        ->assertSee('<meta property="og:image" content="https://cdn.example.com/kaos.jpg">', false)
        ->assertSee('<meta property="og:url" content="'.$site->getStorefrontUrl().'/produk/kaos-polos">', false)
        ->assertSee('<meta property="og:site_name" content="Toko OG">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
});

it('falls back to a text card when a storefront has no image at all', function () {
    $tenant = Tenant::factory()->create();
    BusinessSite::factory()->create([
        'tenant_id' => $tenant->id,
        'slug' => 'toko-polos',
        'mode' => 'produk',
        'status' => 'published',
        'profile' => ['name' => 'Toko Polos'],
    ]);

    $this->get('http://toko-polos.fabriku.biz.id/')
        ->assertOk()
        ->assertDontSee('og:image', false)
        ->assertSee('<meta name="twitter:card" content="summary">', false);
});
