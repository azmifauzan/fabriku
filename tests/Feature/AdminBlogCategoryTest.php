<?php

use App\Models\AdminUser;
use App\Models\BlogCategory;
use App\Models\BlogPost;

function actingAsAdmin(): AdminUser
{
    $admin = AdminUser::factory()->create();
    test()->actingAs($admin, 'admin');

    return $admin;
}

it('lists blog categories in the admin panel', function () {
    actingAsAdmin();
    BlogCategory::factory()->create(['name' => 'Tips UMKM']);

    $response = $this->get(route('admin.blog-categories.index'));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/BlogCategories/Index')
        ->has('categories', 1)
    );
});

it('creates a blog category', function () {
    actingAsAdmin();

    $response = $this->post(route('admin.blog-categories.store'), ['name' => 'Tips UMKM']);

    $response->assertRedirect(route('admin.blog-categories.index'))->assertSessionHas('success');
    $this->assertDatabaseHas('blog_categories', ['name' => 'Tips UMKM', 'slug' => 'tips-umkm']);
});

it('updates a blog category', function () {
    actingAsAdmin();
    $category = BlogCategory::factory()->create(['name' => 'Lama']);

    $response = $this->put(route('admin.blog-categories.update', $category), ['name' => 'Baru']);

    $response->assertRedirect(route('admin.blog-categories.index'));
    expect($category->fresh()->name)->toBe('Baru');
});

it('rejects deleting a category that still has posts', function () {
    actingAsAdmin();
    $category = BlogCategory::factory()->create();
    BlogPost::factory()->create(['blog_category_id' => $category->id]);

    $response = $this->delete(route('admin.blog-categories.destroy', $category));

    $response->assertRedirect()->assertSessionHas('error');
    $this->assertDatabaseHas('blog_categories', ['id' => $category->id]);
});

it('deletes an unused blog category', function () {
    actingAsAdmin();
    $category = BlogCategory::factory()->create();

    $response = $this->delete(route('admin.blog-categories.destroy', $category));

    $response->assertRedirect(route('admin.blog-categories.index'));
    $this->assertDatabaseMissing('blog_categories', ['id' => $category->id]);
});

it('redirects guests to admin login instead of crashing', function () {
    $response = $this->get(route('admin.blog-categories.index'));

    $response->assertRedirect(route('login'));
});
