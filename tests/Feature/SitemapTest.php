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
