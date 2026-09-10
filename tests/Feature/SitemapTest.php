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

    expect(substr_count($body, '<loc>'))->toBe(6);      // 4 static + 2 posts
    expect(substr_count($body, '<lastmod>'))->toBe(2);  // posts only
});
