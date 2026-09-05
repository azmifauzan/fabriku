<?php

use App\Models\AdminUser;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;

it('lists only published posts', function () {
    BlogPost::factory()->published()->create(['title' => 'Post Terbit']);
    BlogPost::factory()->create(['title' => 'Post Draft', 'status' => 'draft']);

    $response = $this->get(route('blog.index'));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Blog/Index')
        ->has('posts.data', 1)
        ->where('posts.data.0.title', 'Post Terbit')
    );
});

it('shows a published post with rendered html content', function () {
    $post = BlogPost::factory()->published()->create([
        'slug' => 'contoh-post',
        'content' => '# Halo Dunia',
    ]);

    $response = $this->get(route('blog.show', $post->slug));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Blog/Show')
        ->where('post.content_html', "<h1>Halo Dunia</h1>\n")
        ->where('post.canonical', url('/blog/contoh-post'))
    );
});

it('returns 404 for a draft post', function () {
    $post = BlogPost::factory()->create(['slug' => 'masih-draft', 'status' => 'draft']);

    $response = $this->get(route('blog.show', $post->slug));

    $response->assertNotFound();
});

it('returns 404 for a nonexistent slug', function () {
    $response = $this->get(route('blog.show', 'tidak-ada'));

    $response->assertNotFound();
});

it('filters the index by category', function () {
    $category = BlogCategory::factory()->create(['slug' => 'tips-umkm']);
    $other = BlogCategory::factory()->create(['slug' => 'lainnya']);
    BlogPost::factory()->published()->create(['blog_category_id' => $category->id, 'title' => 'A']);
    BlogPost::factory()->published()->create(['blog_category_id' => $other->id, 'title' => 'B']);

    $response = $this->get(route('blog.index', ['category' => 'tips-umkm']));

    $response->assertInertia(fn ($page) => $page
        ->has('posts.data', 1)
        ->where('posts.data.0.title', 'A')
        ->where('canonical', url('/blog'))
    );
});

it('filters the index by tag', function () {
    $tag = BlogTag::factory()->create(['slug' => 'retail']);
    $matching = BlogPost::factory()->published()->create(['title' => 'A']);
    $matching->tags()->attach($tag->id);
    BlogPost::factory()->published()->create(['title' => 'B']);

    $response = $this->get(route('blog.index', ['tag' => 'retail']));

    $response->assertInertia(fn ($page) => $page
        ->has('posts.data', 1)
        ->where('posts.data.0.title', 'A')
        ->where('canonical', url('/blog'))
    );
});

it('does not overwrite published_at when an unrelated field is edited later', function () {
    $post = BlogPost::factory()->create(['status' => 'draft', 'published_at' => null]);
    $admin = AdminUser::factory()->create();
    $this->actingAs($admin, 'admin');

    $this->put(route('admin.blog.update', $post), [
        'title' => $post->title,
        'content' => $post->content,
        'status' => 'published',
    ]);
    $publishedAt = $post->fresh()->published_at;

    $this->put(route('admin.blog.update', $post), [
        'title' => 'Judul Baru',
        'content' => $post->content,
        'status' => 'published',
    ]);

    expect($post->fresh()->published_at->equalTo($publishedAt))->toBeTrue();
    expect($this->get(route('blog.show', $post->fresh()->slug))->status())->toBe(200);
});
