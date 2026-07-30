<?php

use App\Models\AdminUser;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function blogActingAsAdmin(): AdminUser
{
    $admin = AdminUser::factory()->create();
    test()->actingAs($admin, 'admin');

    return $admin;
}

beforeEach(function () {
    Storage::fake('fabriku_s3');
});

it('lists posts in the admin panel', function () {
    blogActingAsAdmin();
    BlogPost::factory()->count(2)->create();

    $response = $this->get(route('admin.blog.index'));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/Blog/Index')
        ->has('posts.data', 2)
    );
});

it('creates a post with image, category, and tags', function () {
    $admin = blogActingAsAdmin();
    $category = BlogCategory::factory()->create();

    $response = $this->post(route('admin.blog.store'), [
        'title' => 'Cara Mengelola Stok Toko',
        'excerpt' => 'Ringkasan singkat',
        'content' => "# Halo\n\nIsi post.",
        'status' => 'draft',
        'blog_category_id' => $category->id,
        'tags' => 'retail, tips, stok',
        'featured_image' => UploadedFile::fake()->image('cover.jpg'),
    ]);

    $response->assertRedirect(route('admin.blog.index'))->assertSessionHas('success');

    $post = BlogPost::where('title', 'Cara Mengelola Stok Toko')->firstOrFail();
    expect($post->slug)->toBe('cara-mengelola-stok-toko');
    expect($post->admin_user_id)->toBe($admin->id);
    expect($post->tags)->toHaveCount(3);
    expect($post->tags->pluck('name'))->toContain('retail');
    expect($post->featured_image)->not->toBeNull();
    Storage::disk('fabriku_s3')->assertExists($post->featured_image);
});

it('auto-suffixes the slug on a duplicate title', function () {
    blogActingAsAdmin();
    BlogPost::factory()->create(['title' => 'Judul Sama', 'slug' => 'judul-sama']);

    $this->post(route('admin.blog.store'), [
        'title' => 'Judul Sama',
        'content' => 'Isi',
        'status' => 'draft',
    ]);

    expect(BlogPost::where('title', 'Judul Sama')->count())->toBe(2);
    expect(BlogPost::where('slug', 'judul-sama-2')->exists())->toBeTrue();
});

it('sets published_at once when status flips to published, and never overwrites it', function () {
    blogActingAsAdmin();
    $post = BlogPost::factory()->create(['status' => 'draft', 'published_at' => null]);

    $this->put(route('admin.blog.update', $post), [
        'title' => $post->title,
        'content' => $post->content,
        'status' => 'published',
    ]);
    $firstPublishedAt = $post->fresh()->published_at;
    expect($firstPublishedAt)->not->toBeNull();

    $this->travel(1)->hours();
    $this->put(route('admin.blog.update', $post), [
        'title' => 'Judul diedit',
        'content' => $post->content,
        'status' => 'published',
    ]);

    expect($post->fresh()->published_at->equalTo($firstPublishedAt))->toBeTrue();
});

it('replaces the featured image on update and deletes the old file', function () {
    blogActingAsAdmin();
    $post = BlogPost::factory()->create(['featured_image' => 'blog/old.jpg']);
    Storage::disk('fabriku_s3')->put('blog/old.jpg', 'x');

    $this->put(route('admin.blog.update', $post), [
        'title' => $post->title,
        'content' => $post->content,
        'status' => 'draft',
        'featured_image' => UploadedFile::fake()->image('new.jpg'),
    ]);

    Storage::disk('fabriku_s3')->assertMissing('blog/old.jpg');
    expect($post->fresh()->featured_image)->not->toBe('blog/old.jpg');
});

it('deletes a post and its featured image', function () {
    blogActingAsAdmin();
    $post = BlogPost::factory()->create(['featured_image' => 'blog/to-delete.jpg']);
    Storage::disk('fabriku_s3')->put('blog/to-delete.jpg', 'x');

    $response = $this->delete(route('admin.blog.destroy', $post));

    $response->assertRedirect(route('admin.blog.index'));
    $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    Storage::disk('fabriku_s3')->assertMissing('blog/to-delete.jpg');
});

it('rejects a blog_category_id that does not exist', function () {
    blogActingAsAdmin();

    $response = $this->post(route('admin.blog.store'), [
        'title' => 'Judul',
        'content' => 'Isi',
        'status' => 'draft',
        'blog_category_id' => 99999,
    ]);

    $response->assertSessionHasErrors('blog_category_id');
});

it('returns rendered html from the preview endpoint', function () {
    blogActingAsAdmin();

    $response = $this->post(route('admin.blog-preview'), ['content' => '# Judul']);

    $response->assertOk()->assertJson(['html' => "<h1>Judul</h1>\n"]);
});

it('redirects guests to admin login instead of crashing', function () {
    $response = $this->get(route('admin.blog.index'));

    $response->assertRedirect(route('login'));
});
