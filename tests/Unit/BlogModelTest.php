<?php

use App\Models\AdminUser;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;

it('creates a blog post with category, tags, and author', function () {
    $admin = AdminUser::factory()->create();
    $category = BlogCategory::factory()->create(['name' => 'Tips UMKM', 'slug' => 'tips-umkm']);
    $tag = BlogTag::factory()->create(['name' => 'Retail', 'slug' => 'retail']);

    $post = BlogPost::factory()->create([
        'admin_user_id' => $admin->id,
        'blog_category_id' => $category->id,
        'title' => 'Cara Mengelola Stok',
        'slug' => 'cara-mengelola-stok',
        'status' => 'draft',
    ]);
    $post->tags()->attach($tag->id);

    expect($post->author->id)->toBe($admin->id);
    expect($post->category->slug)->toBe('tips-umkm');
    expect($post->tags)->toHaveCount(1);
    expect($post->tags->first()->slug)->toBe('retail');
});

it('renders content_html from markdown content', function () {
    $post = BlogPost::factory()->create([
        'content' => "# Judul\n\nIni **tebal**.",
    ]);

    expect($post->content_html)->toContain('<h1>Judul</h1>');
    expect($post->content_html)->toContain('<strong>tebal</strong>');
});

it('returns null featured_image_url when no featured_image is set', function () {
    $post = BlogPost::factory()->create(['featured_image' => null]);

    expect($post->featured_image_url)->toBeNull();
});

it('deletes pivot rows when a post is deleted, and nulls category_id when a category is deleted', function () {
    $category = BlogCategory::factory()->create();
    $tag = BlogTag::factory()->create();
    $post = BlogPost::factory()->create(['blog_category_id' => $category->id]);
    $post->tags()->attach($tag->id);

    $post->delete();
    expect(DB::table('blog_post_tag')->where('blog_post_id', $post->id)->count())->toBe(0);

    $post2 = BlogPost::factory()->create(['blog_category_id' => $category->id]);
    $category->delete();
    expect($post2->fresh()->blog_category_id)->toBeNull();
});
