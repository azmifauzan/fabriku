<?php

use App\Models\BlogPost;

it('includes published posts and static pages but excludes drafts', function () {
    $published = BlogPost::factory()->published()->create(['slug' => 'post-terbit']);
    BlogPost::factory()->create(['slug' => 'post-draft', 'status' => 'draft']);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');

    $body = $response->getContent();
    expect($body)->toContain(route('home'));
    expect($body)->toContain(route('blog.index'));
    expect($body)->toContain(route('blog.show', $published->slug));
    expect($body)->not->toContain('post-draft');
});

it('emits lastmod only for blog posts, never for static pages', function () {
    BlogPost::factory()->published()->count(2)->create();

    $body = $this->get('/sitemap.xml')->getContent();

    expect(substr_count($body, '<loc>'))->toBe(13);     // 4 static + 7 landing + 2 posts
    expect(substr_count($body, '<lastmod>'))->toBe(2);  // posts only
});

it('serves the main robots.txt from the app so storefront hosts keep their own', function () {
    // A static public/robots.txt is served by Apache before Laravel on every host,
    // hiding each storefront's robots.txt and sitemap.
    expect(file_exists(public_path('robots.txt')))->toBeFalse();

    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /login')
        ->assertSee('Sitemap: '.route('sitemap'));
});
