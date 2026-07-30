# Public Blog Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a platform-level blog — authored via the super-admin panel (`/admin/blog`), readable by anonymous visitors (`/blog`, `/blog/{slug}`).

**Architecture:** Four new tables (`blog_categories`, `blog_tags`, `blog_posts`, `blog_post_tag`) with no `tenant_id`/`TenantScope` (platform content, not tenant-owned). Three new controllers: `Admin\AdminBlogCategoryController` and `Admin\AdminBlogController` (both behind the existing `auth:admin` + `AdminMiddleware` stack) and a public `BlogController` (no auth). Content is authored as Markdown and rendered to HTML server-side via `league/commonmark` through a small `App\Support\Markdown` helper, used both by an admin live-preview endpoint and the public show page.

**Tech Stack:** Laravel 12, Inertia.js v2, Vue 3.5 `<script setup>`, Pest 4, `league/commonmark` (already vendored transitively — confirmed present in `composer.lock`, no new Composer dependency to add).

## Global Constraints

- No `tenant_id` / `TenantScope` on any of the four new tables — this is platform content, not tenant-owned (per spec §Data Model).
- Featured images go through `config('filesystems.uploads_disk', 'fabriku_s3')`, same disk-resolution convention as `InventoryItemController` (per spec §Backend).
- `blog_posts.slug` and `blog_categories.slug`/`blog_tags.slug` are **globally** unique (no tenant scoping applies), so collision-suffix logic (`-2`, `-3`, ...) checks the whole table.
- `published_at` is set automatically the first time `status` becomes `published`, and is never overwritten afterward (per spec §Backend, §Edge Cases).
- Draft posts return 404 on the public `show` route (per spec §Edge Cases).
- Markdown → HTML conversion happens in exactly one place (`App\Support\Markdown::toHtml()`), reused by the admin preview endpoint and the public show page — do not instantiate `CommonMarkConverter` in more than one file.
- All new Form Requests: `authorize()` returns `$this->user() !== null` (project convention — the real gate is the route middleware stack), per `CLAUDE.md` and matches `StoreRoleRequest`/`UpdateRoleRequest`.
- Bahasa Indonesia for all user-facing UI strings, flash messages, and validation messages (project-wide convention).
- Every quantity/stock-style number isn't relevant here (no numeric displays in this feature), but reuse existing `useSweetAlert` confirm pattern for destructive actions (delete post/category) in the Vue admin pages, matching `Staff/Index.vue`.

---

## File Structure

**Backend — new files:**
- `database/migrations/2026_07_30_000001_create_blog_tables.php` — all 4 tables in one migration (mirrors `2026_01_07_000001_create_sales_tables.php` convention of grouping related tables).
- `app/Models/BlogCategory.php`
- `app/Models/BlogTag.php`
- `app/Models/BlogPost.php`
- `app/Support/Markdown.php`
- `app/Http/Requests/StoreBlogCategoryRequest.php`
- `app/Http/Requests/UpdateBlogCategoryRequest.php`
- `app/Http/Requests/StoreBlogPostRequest.php`
- `app/Http/Requests/UpdateBlogPostRequest.php`
- `app/Http/Controllers/Admin/AdminBlogCategoryController.php`
- `app/Http/Controllers/Admin/AdminBlogController.php`
- `app/Http/Controllers/BlogController.php` (public, `App\Http\Controllers` namespace — same as `MaterialController` etc., not `Admin`)
- `database/factories/BlogCategoryFactory.php`
- `database/factories/BlogTagFactory.php`
- `database/factories/BlogPostFactory.php`

**Backend — modified files:**
- `database/factories/AdminUserFactory.php` — currently an empty stub (`definition()` returns `[]`); no existing test uses `AdminUser::factory()`, so this plan fills it in (needed to write feature tests for the two new Admin controllers).
- `routes/web.php` — add blog routes inside the existing `admin` prefix group, plus two public routes near the existing `/privasi`, `/syarat-ketentuan` routes.

**Frontend — new files:**
- `resources/js/pages/Admin/BlogCategories/Index.vue` — list + inline create/rename (no separate Form page, mirrors the "simple lookup table" pattern).
- `resources/js/pages/Admin/Blog/Index.vue`
- `resources/js/pages/Admin/Blog/Form.vue` — shared create/edit form.
- `resources/js/layouts/PublicLayout.vue` — lightweight public chrome (header/footer matching `Welcome.vue`), reused by both public blog pages.
- `resources/js/pages/Blog/Index.vue`
- `resources/js/pages/Blog/Show.vue`

**Frontend — modified files:**
- `resources/js/layouts/AdminLayout.vue` — add a `Blog` entry to the `navigation` array (`resources/js/layouts/AdminLayout.vue:12-21`).

**Tests — new files:**
- `tests/Feature/AdminBlogCategoryTest.php`
- `tests/Feature/AdminBlogTest.php`
- `tests/Feature/PublicBlogTest.php`

---

## Task 1: Database schema, models, and factories

**Files:**
- Create: `database/migrations/2026_07_30_000001_create_blog_tables.php`
- Create: `app/Models/BlogCategory.php`
- Create: `app/Models/BlogTag.php`
- Create: `app/Models/BlogPost.php`
- Create: `database/factories/BlogCategoryFactory.php`
- Create: `database/factories/BlogTagFactory.php`
- Create: `database/factories/BlogPostFactory.php`
- Test: `tests/Unit/BlogModelTest.php`

**Interfaces:**
- Produces: `App\Models\BlogCategory` (fields: `id`, `name`, `slug`; relation `posts(): HasMany`), `App\Models\BlogTag` (fields: `id`, `name`, `slug`; relation `posts(): BelongsToMany`), `App\Models\BlogPost` (fields: `id`, `admin_user_id`, `blog_category_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `status`, `published_at`, `meta_title`, `meta_description`; relations `author(): BelongsTo` (→ `AdminUser`), `category(): BelongsTo`, `tags(): BelongsToMany`; accessors `featured_image_url` (string|null), `content_html` (string)).
- Later tasks (2-5) depend on these exact class/method/column names.

- [ ] **Step 1: Write the failing model/migration test**

```php
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
    expect(\DB::table('blog_post_tag')->where('blog_post_id', $post->id)->count())->toBe(0);

    $post2 = BlogPost::factory()->create(['blog_category_id' => $category->id]);
    $category->delete();
    expect($post2->fresh()->blog_category_id)->toBeNull();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Unit/BlogModelTest.php`
Expected: FAIL — classes `App\Models\BlogPost` etc. don't exist yet.

- [ ] **Step 3: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->restrictOnDelete();
            $table->foreignId('blog_category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        Schema::create('blog_post_tag', function (Blueprint $table) {
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained('blog_tags')->cascadeOnDelete();
            $table->primary(['blog_post_id', 'blog_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_tag');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
    }
};
```

- [ ] **Step 4: Write the models**

`app/Models/BlogCategory.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
```

`app/Models/BlogTag.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlogTag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class);
    }
}
```

`app/Models/BlogPost.php`:
```php
<?php

namespace App\Models;

use App\Support\Markdown;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_user_id',
        'blog_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class);
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (! $this->featured_image) {
            return null;
        }

        $ttl = config('filesystems.url_ttl_minutes', 25);

        return Cache::remember(
            'blog_img_url_'.md5($this->featured_image),
            ($ttl - 1) * 60,
            fn () => Storage::disk(config('filesystems.uploads_disk', 'fabriku_s3'))->temporaryUrl(
                $this->featured_image,
                now()->addMinutes($ttl)
            )
        );
    }

    public function getContentHtmlAttribute(): string
    {
        return Markdown::toHtml($this->content ?? '');
    }
}
```

- [ ] **Step 5: Write the factories**

`database/factories/BlogCategoryFactory.php`:
```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}
```

`database/factories/BlogTagFactory.php`:
```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogTagFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}
```

`database/factories/BlogPostFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogPostFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'admin_user_id' => AdminUser::factory(),
            'blog_category_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => $this->faker->sentence(),
            'content' => "# {$title}\n\n".$this->faker->paragraph(),
            'featured_image' => null,
            'status' => 'draft',
            'published_at' => null,
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
```

Add `use HasFactory;` factory resolution: since these models live in the default `App\Models` namespace and `database/factories` follows Laravel's auto-discovery convention (`{Model}Factory` in `Database\Factories`), no explicit `newFactory()` override is needed — matches how `InventoryItem::factory()` already resolves in this codebase.

- [ ] **Step 6: Run migration and test to verify it passes**

Run: `php artisan migrate` then `php artisan test --compact tests/Unit/BlogModelTest.php`
Expected: PASS (all 4 tests) — note: `content_html` test will still fail until Task 2's `Markdown` helper exists; if running Task 1 in isolation, temporarily comment out that one test or proceed straight to Task 2 before running the full suite. Run the other 3 tests individually first: `php artisan test --compact --filter="creates a blog post|featured_image_url|deletes pivot rows"`.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_07_30_000001_create_blog_tables.php app/Models/BlogCategory.php app/Models/BlogTag.php app/Models/BlogPost.php database/factories/BlogCategoryFactory.php database/factories/BlogTagFactory.php database/factories/BlogPostFactory.php tests/Unit/BlogModelTest.php
git commit -m "feat(blog): add blog_posts/categories/tags schema and models"
```

---

## Task 2: Markdown rendering helper

**Files:**
- Create: `app/Support/Markdown.php`
- Test: `tests/Unit/MarkdownTest.php`

**Interfaces:**
- Consumes: nothing (wraps `League\CommonMark\CommonMarkConverter`, already vendored).
- Produces: `App\Support\Markdown::toHtml(string $markdown): string` — used by `BlogPost::getContentHtmlAttribute()` (Task 1, already written above) and by `AdminBlogController::preview()` (Task 4).

- [ ] **Step 1: Write the failing test**

```php
<?php

use App\Support\Markdown;

it('converts markdown to html', function () {
    $html = Markdown::toHtml("# Judul\n\nParagraf **tebal** dan _miring_.");

    expect($html)->toContain('<h1>Judul</h1>');
    expect($html)->toContain('<strong>tebal</strong>');
    expect($html)->toContain('<em>miring</em>');
});

it('returns empty string for empty input', function () {
    expect(Markdown::toHtml(''))->toBe('');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Unit/MarkdownTest.php`
Expected: FAIL — `App\Support\Markdown` not defined.

- [ ] **Step 3: Write the helper**

```php
<?php

namespace App\Support;

use League\CommonMark\CommonMarkConverter;

class Markdown
{
    public static function toHtml(string $markdown): string
    {
        if (trim($markdown) === '') {
            return '';
        }

        static $converter;
        $converter ??= new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return (string) $converter->convert($markdown);
    }
}
```

`html_input: strip` and `allow_unsafe_links: false` matter here because post content, while authored only by trusted `AdminUser`s, is still rendered with `v-html` on the public show page (Task 5) — stripping raw HTML input and disallowing unsafe link schemes (e.g. `javascript:`) is a defense-in-depth floor, not a trust assumption.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact tests/Unit/MarkdownTest.php`
Expected: PASS

Also now run Task 1's full suite: `php artisan test --compact tests/Unit/BlogModelTest.php`
Expected: PASS (all 4 tests, including `content_html`)

- [ ] **Step 5: Commit**

```bash
git add app/Support/Markdown.php tests/Unit/MarkdownTest.php
git commit -m "feat(blog): add Markdown::toHtml rendering helper"
```

---

## Task 3: Admin blog category CRUD

**Files:**
- Create: `app/Http/Requests/StoreBlogCategoryRequest.php`
- Create: `app/Http/Requests/UpdateBlogCategoryRequest.php`
- Create: `app/Http/Controllers/Admin/AdminBlogCategoryController.php`
- Create: `resources/js/pages/Admin/BlogCategories/Index.vue`
- Modify: `database/factories/AdminUserFactory.php`
- Modify: `routes/web.php:94` (insert after the existing `Route::resource('roles', AdminRoleController::class);` line)
- Test: `tests/Feature/AdminBlogCategoryTest.php`

**Interfaces:**
- Consumes: `App\Models\BlogCategory` (Task 1).
- Produces: routes `admin.blog-categories.index/store/update/destroy`; used by Task 4's blog post form (category select) and Task 5's public controller (category filter).

- [ ] **Step 1: Fill in the empty `AdminUserFactory`**

`database/factories/AdminUserFactory.php` (replace the empty `definition()`):
```php
<?php

namespace Database\Factories;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<AdminUser>
 */
class AdminUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => $this->faker->phoneNumber(),
            'is_active' => true,
            'last_login_at' => null,
        ];
    }
}
```

- [ ] **Step 2: Write the failing feature test**

```php
<?php

use App\Models\AdminUser;
use App\Models\BlogCategory;

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
    \App\Models\BlogPost::factory()->create(['blog_category_id' => $category->id]);

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

    $response->assertRedirect(route('admin.login'));
});
```

- [ ] **Step 3: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/AdminBlogCategoryTest.php`
Expected: FAIL — route `admin.blog-categories.index` not defined.

- [ ] **Step 4: Write the Form Requests**

`app/Http/Requests/StoreBlogCategoryRequest.php`:
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
        ];
    }
}
```

`app/Http/Requests/UpdateBlogCategoryRequest.php` — identical rules (kept as a separate class to match the project's Store/Update pair convention, e.g. `StoreRoleRequest`/`UpdateRoleRequest`):
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
        ];
    }
}
```

- [ ] **Step 5: Write the controller**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogCategoryRequest;
use App\Http\Requests\UpdateBlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminBlogCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/BlogCategories/Index', [
            'categories' => BlogCategory::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreBlogCategoryRequest $request)
    {
        $validated = $request->validated();
        $validated['slug'] = $this->uniqueSlug($validated['name']);

        BlogCategory::create($validated);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Kategori berhasil dibuat.');
    }

    public function update(UpdateBlogCategoryRequest $request, BlogCategory $blogCategory)
    {
        $blogCategory->update($request->validated());

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(BlogCategory $blogCategory)
    {
        if ($blogCategory->posts()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih dipakai post.');
        }

        $blogCategory->delete();

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (BlogCategory::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
```

Note: `update()` intentionally does not regenerate the slug — categories only get a slug at creation time, matching the "slug stays immutable after creation" convention already used by `RoleController::update()`.

- [ ] **Step 6: Wire the routes**

Modify `routes/web.php` — add `use App\Http\Controllers\Admin\AdminBlogCategoryController;` and `use App\Http\Controllers\Admin\AdminBlogController;` to the `use` block at the top (alongside the other `Admin\*` imports), then insert after line 94 (`Route::resource('roles', AdminRoleController::class);`):

```php
        // Blog
        Route::resource('blog-categories', AdminBlogCategoryController::class)->except(['show', 'create', 'edit']);
```

(`create`/`edit`/`show` excluded — `Index.vue` handles create/rename inline, no separate pages, per spec.)

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/AdminBlogCategoryTest.php`
Expected: PASS (all 6 tests)

- [ ] **Step 8: Build the Vue admin page**

`resources/js/pages/Admin/BlogCategories/Index.vue`:
```vue
<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    categories: Array<{ id: number; name: string; slug: string; posts_count: number }>;
}>();

const { confirmDelete } = useSweetAlert();

const showCreateForm = ref(false);
const createForm = useForm({ name: '' });
const editingId = ref<number | null>(null);
const editForm = useForm({ name: '' });

function submitCreate() {
    createForm.post(route('admin.blog-categories.store'), {
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
        },
    });
}

function startEdit(category: { id: number; name: string }) {
    editingId.value = category.id;
    editForm.name = category.name;
}

function submitEdit(id: number) {
    editForm.put(route('admin.blog-categories.update', id), {
        onSuccess: () => {
            editingId.value = null;
        },
    });
}

async function destroy(id: number) {
    const confirmed = await confirmDelete('Hapus kategori ini?');
    if (confirmed) {
        useForm({}).delete(route('admin.blog-categories.destroy', id));
    }
}
</script>

<template>
    <Head title="Kategori Blog" />
    <AdminLayout>
        <div class="mx-auto max-w-3xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Kategori Blog</h1>
                <button
                    class="flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="showCreateForm = !showCreateForm"
                >
                    <Plus class="h-4 w-4" /> Tambah Kategori
                </button>
            </div>

            <form v-if="showCreateForm" class="mb-4 flex gap-2" @submit.prevent="submitCreate">
                <input
                    v-model="createForm.name"
                    type="text"
                    placeholder="Nama kategori"
                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                />
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm text-white">Simpan</button>
            </form>

            <ul class="divide-y divide-gray-200 rounded-md border border-gray-200 dark:divide-gray-700 dark:border-gray-700">
                <li v-for="category in categories" :key="category.id" class="flex items-center justify-between p-3">
                    <form v-if="editingId === category.id" class="flex flex-1 gap-2" @submit.prevent="submitEdit(category.id)">
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        />
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1 text-sm text-white">Simpan</button>
                    </form>
                    <template v-else>
                        <span class="text-gray-900 dark:text-white" @click="startEdit(category)">
                            {{ category.name }} <span class="text-sm text-gray-400">({{ category.posts_count }} post)</span>
                        </span>
                        <button class="text-red-600 hover:text-red-800" @click="destroy(category.id)">
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </template>
                </li>
            </ul>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 9: Commit**

```bash
git add app/Http/Requests/StoreBlogCategoryRequest.php app/Http/Requests/UpdateBlogCategoryRequest.php app/Http/Controllers/Admin/AdminBlogCategoryController.php database/factories/AdminUserFactory.php routes/web.php resources/js/pages/Admin/BlogCategories/Index.vue tests/Feature/AdminBlogCategoryTest.php
git commit -m "feat(blog): add admin blog category CRUD"
```

---

## Task 4: Admin blog post CRUD (create/edit/list/delete, image upload, tags, preview)

**Files:**
- Create: `app/Http/Requests/StoreBlogPostRequest.php`
- Create: `app/Http/Requests/UpdateBlogPostRequest.php`
- Create: `app/Http/Controllers/Admin/AdminBlogController.php`
- Create: `resources/js/pages/Admin/Blog/Index.vue`
- Create: `resources/js/pages/Admin/Blog/Form.vue`
- Modify: `resources/js/layouts/AdminLayout.vue:12-21`
- Modify: `routes/web.php` (insert after the `blog-categories` line added in Task 3)
- Test: `tests/Feature/AdminBlogTest.php`

**Interfaces:**
- Consumes: `App\Models\BlogPost`, `BlogCategory`, `BlogTag` (Task 1); `Route::resource('blog-categories', ...)` (Task 3, for the category select).
- Produces: routes `admin.blog.index/create/store/edit/update/destroy`, `admin.blog-preview` (POST); used by Task 5's public controller only indirectly (same `BlogPost` rows).

- [ ] **Step 1: Write the failing feature test**

```php
<?php

use App\Models\AdminUser;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function actingAsAdmin(): AdminUser
{
    $admin = AdminUser::factory()->create();
    test()->actingAs($admin, 'admin');

    return $admin;
}

beforeEach(function () {
    Storage::fake('fabriku_s3');
});

it('lists posts in the admin panel', function () {
    actingAsAdmin();
    BlogPost::factory()->count(2)->create();

    $response = $this->get(route('admin.blog.index'));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/Blog/Index')
        ->has('posts.data', 2)
    );
});

it('creates a post with image, category, and tags', function () {
    $admin = actingAsAdmin();
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
    actingAsAdmin();
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
    actingAsAdmin();
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
    actingAsAdmin();
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
    actingAsAdmin();
    $post = BlogPost::factory()->create(['featured_image' => 'blog/to-delete.jpg']);
    Storage::disk('fabriku_s3')->put('blog/to-delete.jpg', 'x');

    $response = $this->delete(route('admin.blog.destroy', $post));

    $response->assertRedirect(route('admin.blog.index'));
    $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    Storage::disk('fabriku_s3')->assertMissing('blog/to-delete.jpg');
});

it('rejects a blog_category_id that does not exist', function () {
    actingAsAdmin();

    $response = $this->post(route('admin.blog.store'), [
        'title' => 'Judul',
        'content' => 'Isi',
        'status' => 'draft',
        'blog_category_id' => 99999,
    ]);

    $response->assertSessionHasErrors('blog_category_id');
});

it('returns rendered html from the preview endpoint', function () {
    actingAsAdmin();

    $response = $this->post(route('admin.blog-preview'), ['content' => '# Judul']);

    $response->assertOk()->assertJson(['html' => "<h1>Judul</h1>\n"]);
});

it('redirects guests to admin login instead of crashing', function () {
    $response = $this->get(route('admin.blog.index'));

    $response->assertRedirect(route('admin.login'));
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/AdminBlogTest.php`
Expected: FAIL — route `admin.blog.index` not defined.

- [ ] **Step 3: Write the Form Requests**

`app/Http/Requests/StoreBlogPostRequest.php`:
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'blog_category_id' => ['nullable', Rule::exists('blog_categories', 'id')],
            'tags' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
```

`app/Http/Requests/UpdateBlogPostRequest.php` — identical rules:
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'blog_category_id' => ['nullable', Rule::exists('blog_categories', 'id')],
            'tags' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
```

- [ ] **Step 4: Write the controller**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogPostRequest;
use App\Http\Requests\UpdateBlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Support\Markdown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminBlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::with('category')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Blog/Index', ['posts' => $posts]);
    }

    public function create()
    {
        return Inertia::render('Admin/Blog/Form', [
            'categories' => BlogCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreBlogPostRequest $request)
    {
        $validated = $request->validated();
        $validated['slug'] = $this->uniqueSlug($validated['title']);
        $validated['admin_user_id'] = auth('admin')->id();
        $validated = $this->applyPublishedAt($validated, null);

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->storePublicly(
                'blog',
                config('filesystems.uploads_disk', 'fabriku_s3')
            );
        }

        $tagNames = $this->parseTags($request->input('tags', ''));
        unset($validated['tags']);

        $post = BlogPost::create($validated);
        $post->tags()->sync($this->tagIdsFor($tagNames));

        return redirect()->route('admin.blog.index')->with('success', 'Post berhasil dibuat.');
    }

    public function edit(BlogPost $blog)
    {
        $blog->load('tags');

        return Inertia::render('Admin/Blog/Form', [
            'post' => $blog->only([
                'id', 'title', 'excerpt', 'content', 'status', 'blog_category_id',
                'meta_title', 'meta_description', 'featured_image_url',
            ]) + ['tags' => $blog->tags->pluck('name')->join(', ')],
            'categories' => BlogCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blog)
    {
        $validated = $request->validated();
        $validated = $this->applyPublishedAt($validated, $blog);

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image) {
                Storage::disk(config('filesystems.uploads_disk', 'fabriku_s3'))->delete($blog->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->storePublicly(
                'blog',
                config('filesystems.uploads_disk', 'fabriku_s3')
            );
        } else {
            unset($validated['featured_image']);
        }

        $tagNames = $this->parseTags($request->input('tags', ''));
        unset($validated['tags']);

        $blog->update($validated);
        $blog->tags()->sync($this->tagIdsFor($tagNames));

        return redirect()->route('admin.blog.index')->with('success', 'Post berhasil diperbarui.');
    }

    public function destroy(BlogPost $blog)
    {
        if ($blog->featured_image) {
            Storage::disk(config('filesystems.uploads_disk', 'fabriku_s3'))->delete($blog->featured_image);
        }

        $blog->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Post berhasil dihapus.');
    }

    public function preview(Request $request)
    {
        return response()->json(['html' => Markdown::toHtml($request->input('content', ''))]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function applyPublishedAt(array $validated, ?BlogPost $existing): array
    {
        $wasPublished = $existing?->published_at !== null;

        if ($validated['status'] === 'published' && ! $wasPublished) {
            $validated['published_at'] = now();
        }

        return $validated;
    }

    private function parseTags(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function tagIdsFor(array $tagNames): array
    {
        return collect($tagNames)->map(function (string $name) {
            $tag = BlogTag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );

            return $tag->id;
        })->all();
    }
}
```

- [ ] **Step 5: Wire the routes**

Modify `routes/web.php` — insert after the `blog-categories` resource line added in Task 3:

```php
        Route::resource('blog', AdminBlogController::class);
        Route::post('blog-preview', [AdminBlogController::class, 'preview'])->name('blog-preview');
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/AdminBlogTest.php`
Expected: PASS (all 9 tests)

- [ ] **Step 7: Add the admin nav entry**

Modify `resources/js/layouts/AdminLayout.vue`:
```diff
-import { Activity, Building2, ChevronDown, CreditCard, FileText, LayoutDashboard, LogOut, Menu, Settings, Shield, Users, X } from 'lucide-vue-next';
+import { Activity, Building2, ChevronDown, CreditCard, FileText, LayoutDashboard, LogOut, Menu, Newspaper, Settings, Shield, Users, X } from 'lucide-vue-next';
```
```diff
     { name: 'Roles', href: '/admin/roles', icon: Shield },
+    { name: 'Blog', href: '/admin/blog', icon: Newspaper },
     { name: 'Settings', href: '/admin/settings', icon: Settings },
```

- [ ] **Step 8: Build the Vue admin pages**

`resources/js/pages/Admin/Blog/Index.vue`:
```vue
<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineProps<{
    posts: {
        data: Array<{ id: number; title: string; status: string; published_at: string | null; category: { name: string } | null }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

const { confirmDelete } = useSweetAlert();

async function destroy(id: number) {
    const confirmed = await confirmDelete('Hapus post ini?');
    if (confirmed) {
        useForm({}).delete(route('admin.blog.destroy', id));
    }
}
</script>

<template>
    <Head title="Blog" />
    <AdminLayout>
        <div class="mx-auto max-w-5xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Blog</h1>
                <Link
                    :href="route('admin.blog.create')"
                    class="flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    <Plus class="h-4 w-4" /> Tulis Post
                </Link>
            </div>

            <table class="w-full divide-y divide-gray-200 rounded-md border border-gray-200 text-left text-sm dark:divide-gray-700 dark:border-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2">Judul</th>
                        <th class="px-4 py-2">Kategori</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Tanggal Publish</th>
                        <th class="px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="post in posts.data" :key="post.id">
                        <td class="px-4 py-2 text-gray-900 dark:text-white">{{ post.title }}</td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ post.category?.name ?? '-' }}</td>
                        <td class="px-4 py-2">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs"
                                :class="post.status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                            >
                                {{ post.status === 'published' ? 'Terbit' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ post.published_at ?? '-' }}</td>
                        <td class="px-4 py-2">
                            <div class="flex gap-2">
                                <Link :href="route('admin.blog.edit', post.id)" class="text-indigo-600 hover:text-indigo-800">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button class="text-red-600 hover:text-red-800" @click="destroy(post.id)">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
```

`resources/js/pages/Admin/Blog/Form.vue`:
```vue
<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps<{
    post?: {
        id: number;
        title: string;
        excerpt: string | null;
        content: string;
        status: string;
        blog_category_id: number | null;
        meta_title: string | null;
        meta_description: string | null;
        featured_image_url: string | null;
        tags: string;
    };
    categories: Array<{ id: number; name: string }>;
}>();

const isEdit = !!props.post;

const form = useForm({
    title: props.post?.title ?? '',
    excerpt: props.post?.excerpt ?? '',
    content: props.post?.content ?? '',
    status: props.post?.status ?? 'draft',
    blog_category_id: props.post?.blog_category_id ?? null,
    tags: props.post?.tags ?? '',
    meta_title: props.post?.meta_title ?? '',
    meta_description: props.post?.meta_description ?? '',
    featured_image: null as File | null,
});

const previewHtml = ref('');
const showPreview = ref(false);

async function loadPreview() {
    const response = await axios.post(route('admin.blog-preview'), { content: form.content });
    previewHtml.value = response.data.html;
    showPreview.value = true;
}

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    form.featured_image = target.files?.[0] ?? null;
}

function submit() {
    const url = isEdit ? route('admin.blog.update', props.post!.id) : route('admin.blog.store');
    form.post(url, {
        forceFormData: true,
        onSuccess: () => form.reset('featured_image'),
    });
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Post' : 'Tulis Post'" />
    <AdminLayout>
        <form class="mx-auto max-w-3xl space-y-4 p-6" @submit.prevent="submit">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ isEdit ? 'Edit Post' : 'Tulis Post' }}</h1>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul</label>
                <input v-model="form.title" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ringkasan (Excerpt)</label>
                <textarea v-model="form.excerpt" rows="2" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konten (Markdown)</label>
                    <button type="button" class="text-sm text-indigo-600" @click="loadPreview">Lihat Preview</button>
                </div>
                <textarea v-model="form.content" rows="12" class="mt-1 w-full rounded-md border-gray-300 font-mono dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                <div v-if="showPreview" class="prose mt-2 max-w-none rounded-md border border-gray-200 p-4 dark:border-gray-700 dark:prose-invert" v-html="previewHtml" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gambar Utama</label>
                <input type="file" accept="image/*" class="mt-1" @change="handleFileChange" />
                <img v-if="post?.featured_image_url" :src="post.featured_image_url" class="mt-2 h-32 w-32 rounded-md object-cover" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                    <select v-model="form.blog_category_id" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option :value="null">- Tanpa kategori -</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select v-model="form.status" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="draft">Draft</option>
                        <option value="published">Terbit</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag (pisahkan dengan koma)</label>
                <input v-model="form.tags" type="text" placeholder="retail, tips, stok" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Title (SEO)</label>
                <input v-model="form.meta_title" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Description (SEO)</label>
                <textarea v-model="form.meta_description" rows="2" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                Simpan
            </button>
        </form>
    </AdminLayout>
</template>
```

- [ ] **Step 9: Commit**

```bash
git add app/Http/Requests/StoreBlogPostRequest.php app/Http/Requests/UpdateBlogPostRequest.php app/Http/Controllers/Admin/AdminBlogController.php resources/js/pages/Admin/Blog resources/js/layouts/AdminLayout.vue routes/web.php tests/Feature/AdminBlogTest.php
git commit -m "feat(blog): add admin blog post CRUD with image upload, tags, and preview"
```

---

## Task 5: Public blog routes and pages

**Files:**
- Create: `app/Http/Controllers/BlogController.php`
- Create: `resources/js/layouts/PublicLayout.vue`
- Create: `resources/js/pages/Blog/Index.vue`
- Create: `resources/js/pages/Blog/Show.vue`
- Modify: `routes/web.php:47-48` (insert after the existing `/syarat-ketentuan` route)
- Test: `tests/Feature/PublicBlogTest.php`

**Interfaces:**
- Consumes: `App\Models\BlogPost`/`BlogCategory`/`BlogTag` (Task 1), `BlogPost::content_html`/`featured_image_url` accessors (Task 1).
- Produces: routes `blog.index`, `blog.show` — terminal task, nothing downstream depends on this.

- [ ] **Step 1: Write the failing feature test**

```php
<?php

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

    $response->assertInertia(fn ($page) => $page->has('posts.data', 1)->where('posts.data.0.title', 'A'));
});

it('filters the index by tag', function () {
    $tag = BlogTag::factory()->create(['slug' => 'retail']);
    $matching = BlogPost::factory()->published()->create(['title' => 'A']);
    $matching->tags()->attach($tag->id);
    BlogPost::factory()->published()->create(['title' => 'B']);

    $response = $this->get(route('blog.index', ['tag' => 'retail']));

    $response->assertInertia(fn ($page) => $page->has('posts.data', 1)->where('posts.data.0.title', 'A'));
});

it('does not overwrite published_at when an unrelated field is edited later', function () {
    $post = BlogPost::factory()->create(['status' => 'draft', 'published_at' => null]);
    $admin = \App\Models\AdminUser::factory()->create();
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/PublicBlogTest.php`
Expected: FAIL — route `blog.index` not defined.

- [ ] **Step 3: Write the controller**

```php
<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::where('status', 'published')
            ->with('category', 'tags')
            ->when($request->category, fn ($q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)))
            ->when($request->tag, fn ($q, $slug) => $q->whereHas('tags', fn ($q) => $q->where('slug', $slug)))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'categories' => BlogCategory::orderBy('name')->get(['name', 'slug']),
            'activeCategory' => $request->category,
            'activeTag' => $request->tag,
        ]);
    }

    public function show(BlogPost $post)
    {
        abort_unless($post->status === 'published', 404);

        $post->load('category', 'tags', 'author');

        return Inertia::render('Blog/Show', [
            'post' => [
                'title' => $post->title,
                'content_html' => $post->content_html,
                'featured_image_url' => $post->featured_image_url,
                'published_at' => $post->published_at,
                'meta_title' => $post->meta_title ?? $post->title,
                'meta_description' => $post->meta_description ?? $post->excerpt,
                'category' => $post->category?->only(['name', 'slug']),
                'tags' => $post->tags->map->only(['name', 'slug']),
                'author_name' => $post->author->name,
            ],
        ]);
    }
}
```

Route-model binding on `slug`: use explicit binding in `routes/web.php` (`{post:slug}`) rather than overriding `getRouteKeyName()` on the model — `BlogPost` has no other route that binds by `id`, but keeping the default `id` binding available (e.g. for the admin routes in Task 4, which bind `BlogPost` by `id` via `Route::resource`) means the slug binding must be scoped to this one route, not global.

- [ ] **Step 4: Wire the routes**

Modify `routes/web.php` — add `use App\Http\Controllers\BlogController;` to the `use` block, then insert after the existing:
```php
Route::get('/syarat-ketentuan', fn () => Inertia::render('Legal/Terms'))->name('legal.terms');
```
add:
```php
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/PublicBlogTest.php`
Expected: PASS (all 7 tests)

- [ ] **Step 6: Build the public layout and pages**

`resources/js/layouts/PublicLayout.vue` — check `resources/js/pages/Welcome.vue`'s existing header/footer markup before writing this file, and reuse the same header nav / footer structure verbatim (logo, nav links, footer columns) so the blog doesn't look like a different site; wrap it around a `<slot />`:
```vue
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Copy header markup from Welcome.vue's <header> block here, keeping the same nav links (Home, Fitur, Harga, etc.) plus a "Blog" link -->
        <main>
            <slot />
        </main>
        <!-- Copy footer markup from Welcome.vue's <footer> block here verbatim -->
    </div>
</template>
```

`resources/js/pages/Blog/Index.vue`:
```vue
<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    posts: {
        data: Array<{
            slug: string;
            title: string;
            excerpt: string | null;
            featured_image_url: string | null;
            published_at: string | null;
            category: { name: string; slug: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    categories: Array<{ name: string; slug: string }>;
    activeCategory: string | null;
}>();
</script>

<template>
    <Head title="Blog" />
    <PublicLayout>
        <div class="mx-auto max-w-5xl px-4 py-12">
            <h1 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">Blog</h1>

            <div class="mb-8 flex flex-wrap gap-2">
                <Link
                    :href="route('blog.index')"
                    class="rounded-full px-3 py-1 text-sm"
                    :class="!activeCategory ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                >
                    Semua
                </Link>
                <Link
                    v-for="category in categories"
                    :key="category.slug"
                    :href="route('blog.index', { category: category.slug })"
                    class="rounded-full px-3 py-1 text-sm"
                    :class="activeCategory === category.slug ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                >
                    {{ category.name }}
                </Link>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="post in posts.data"
                    :key="post.slug"
                    :href="route('blog.show', post.slug)"
                    class="block overflow-hidden rounded-lg border border-gray-200 hover:shadow-md dark:border-gray-700"
                >
                    <img
                        v-if="post.featured_image_url"
                        :src="post.featured_image_url"
                        class="h-40 w-full object-cover"
                        :alt="post.title"
                    />
                    <div class="p-4">
                        <p v-if="post.category" class="mb-1 text-xs font-medium text-indigo-600">{{ post.category.name }}</p>
                        <h2 class="mb-1 font-semibold text-gray-900 dark:text-white">{{ post.title }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ post.excerpt }}</p>
                    </div>
                </Link>
            </div>

            <div class="mt-8 flex flex-wrap gap-2">
                <Link
                    v-for="link in posts.links"
                    :key="link.label"
                    :href="link.url ?? ''"
                    class="rounded-md px-3 py-1 text-sm"
                    :class="link.active ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                    v-html="link.label"
                />
            </div>
        </div>
    </PublicLayout>
</template>
```

`resources/js/pages/Blog/Show.vue`:
```vue
<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    post: {
        title: string;
        content_html: string;
        featured_image_url: string | null;
        published_at: string | null;
        meta_title: string;
        meta_description: string | null;
        category: { name: string; slug: string } | null;
        tags: Array<{ name: string; slug: string }>;
        author_name: string;
    };
}>();
</script>

<template>
    <Head :title="post.meta_title">
        <meta v-if="post.meta_description" name="description" :content="post.meta_description" />
    </Head>
    <PublicLayout>
        <article class="mx-auto max-w-3xl px-4 py-12">
            <p v-if="post.category" class="mb-2 text-sm font-medium text-indigo-600">{{ post.category.name }}</p>
            <h1 class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">{{ post.title }}</h1>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Oleh {{ post.author_name }} &middot; {{ post.published_at }}</p>
            <img v-if="post.featured_image_url" :src="post.featured_image_url" class="mb-6 w-full rounded-lg object-cover" />
            <div class="prose max-w-none dark:prose-invert" v-html="post.content_html" />
            <div v-if="post.tags.length" class="mt-8 flex flex-wrap gap-2">
                <Link
                    v-for="tag in post.tags"
                    :key="tag.slug"
                    :href="route('blog.index', { tag: tag.slug })"
                    class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                >
                    #{{ tag.name }}
                </Link>
            </div>
        </article>
    </PublicLayout>
</template>
```

- [ ] **Step 7: Manual verification**

Run `composer dev` (or `php artisan serve` + `npm run dev`), then in a browser:
- Log into `/admin/login`, create a category, create a published post with an image and tags, confirm it appears at `/blog` and `/blog/{slug}` renders the markdown as HTML with the image, category badge, and tags.
- Save a post as `draft` and confirm `/blog/{slug}` 404s and it doesn't appear in `/blog`.
- Toggle dark mode and confirm both admin and public pages remain readable (project-wide dark mode convention).

- [ ] **Step 8: Run the full test suite**

Run: `php artisan test --compact`
Expected: PASS (no regressions in the rest of the suite)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/BlogController.php resources/js/layouts/PublicLayout.vue resources/js/pages/Blog routes/web.php tests/Feature/PublicBlogTest.php
git commit -m "feat(blog): add public blog index and detail pages"
```

---

## Self-Review Notes

- **Spec coverage:** Data model (Task 1), admin post/category CRUD incl. slug collision, tag find-or-create, image upload/delete, `published_at` rule, preview endpoint (Tasks 3-4), public index/show incl. category/tag filters and draft 404 (Task 5), Markdown rendering shared helper (Task 2), nav entry (Task 4 Step 7), testing (every task). All spec sections have a task.
- **Placeholder scan:** none found — every step has runnable code, no "TBD"/"add validation later".
- **Type consistency:** `BlogPost::content_html`/`featured_image_url` accessor names match between Task 1 (defined) and Tasks 4/5 (consumed in controllers and Vue props). Route names (`admin.blog.*`, `admin.blog-categories.*`, `admin.blog-preview`, `blog.index`, `blog.show`) are consistent across all tasks that reference them.
- **`PublicLayout.vue` is deliberately left as a copy-from-`Welcome.vue` instruction rather than fully inlined markup** — `Welcome.vue`'s exact header/footer JSX wasn't read in full during planning; the implementer must open it and copy the real markup rather than inventing new site chrome, so the blog visually matches the existing landing page.
