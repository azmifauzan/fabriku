<?php

use App\Models\BlogPost;
use App\Models\BlogTag;

it('renders every configured category landing page', function (string $slug) {
    $this->get(route('landing.category', $slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Landing/Category')
            ->where('canonical', url("/aplikasi/{$slug}"))
            ->has('page.faqs', 3)
            ->has('otherPages', count(config('landing_pages')) - 1)
        );
})->with(array_keys(require __DIR__.'/../../config/landing_pages.php'));

it('maps every landing page to an enabled business category', function () {
    foreach (config('landing_pages') as $page) {
        expect(config('business.enabled_categories'))->toContain($page['category']);
    }
});

it('returns 404 for an unknown landing slug', function () {
    $this->get('/aplikasi/tidak-ada')->assertNotFound();
});

it('shows related published posts by tag', function () {
    $tag = BlogTag::create(['name' => 'Konveksi', 'slug' => 'konveksi']);
    $published = BlogPost::factory()->published()->create(['title' => 'Post Konveksi']);
    $draft = BlogPost::factory()->create(['status' => 'draft']);
    $published->tags()->attach($tag);
    $draft->tags()->attach($tag);

    $this->get(route('landing.category', 'konveksi'))
        ->assertInertia(fn ($page) => $page
            ->has('relatedPosts', 1)
            ->where('relatedPosts.0.title', 'Post Konveksi')
        );
});

it('lists landing pages in the sitemap', function () {
    $xml = $this->get(route('sitemap'))->assertOk()->getContent();

    foreach (array_keys(config('landing_pages')) as $slug) {
        expect($xml)->toContain(url("/aplikasi/{$slug}"));
    }
});

it('links every landing page from the public footer', function () {
    $layout = file_get_contents(resource_path('js/layouts/PublicLayout.vue'));

    foreach (array_keys(config('landing_pages')) as $slug) {
        expect($layout)->toContain("/aplikasi/{$slug}'");
    }
});
