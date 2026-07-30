# Public Blog — Design Spec

Date: 2026-07-30

## Problem

Fabriku has no content-marketing/blog surface. Need a public-facing blog (SEO, announcements, UMKM tips) plus an admin-panel authoring flow. This is platform-level content (one blog for fabriku.my.id), not per-tenant — authored by super-admin users (`AdminUser` guard, `/admin/*`), read by anonymous visitors.

## Scope

- New `blog_posts`, `blog_categories`, `blog_tags`, `blog_post_tag` tables. No `tenant_id`, no `TenantScope` — these are platform-level, not tenant-owned.
- Admin CRUD for posts and categories under `/admin/blog/*`, guarded by `auth:admin` + `AdminMiddleware` (same stack as every other `Admin*Controller`).
- Public read routes `GET /blog` (list, published only) and `GET /blog/{slug}` (detail), no auth.
- Content authored as Markdown, rendered to HTML server-side via `league/commonmark` (already vendored transitively — confirmed in `composer.lock`, no new Composer dependency needed).
- Featured image upload via existing `uploads_disk` convention (`config('filesystems.uploads_disk', 'fabriku_s3')`), same pattern as `InventoryItemController::store()`/`update()`.

Out of scope (YAGNI — add later if needed): comments, related-posts, RSS feed, post revisions, scheduled publishing beyond a manual status toggle, multiple authors/roles beyond a single `admin_user_id` author field, a dedicated tag-management UI (tags are free-text, find-or-create on save).

## Data Model

### `blog_categories`
- `id`, `name` (string), `slug` (string, **globally** unique — not tenant-scoped, this table has no `tenant_id`), timestamps.

### `blog_tags`
- `id`, `name` (string), `slug` (string, unique), timestamps.

### `blog_post_tag` (pivot)
- `blog_post_id` (FK, cascade delete), `blog_tag_id` (FK, cascade delete).

### `blog_posts`
- `id`
- `admin_user_id` (FK → `admin_users.id`, author, `restrict` on delete — a post must not go orphaned/silently reassigned if an admin user is removed; block deletion of an `AdminUser` who has authored posts, matching the `services` FK-restrict convention already used for `sales_order_items.service_id`)
- `blog_category_id` (FK → `blog_categories.id`, nullable, `nullOnDelete`)
- `title` (string)
- `slug` (string, unique, globally — no `tenant_id` on this table)
- `excerpt` (text, nullable)
- `content` (longtext, raw Markdown)
- `featured_image` (string, nullable — storage path, not URL, mirrors `InventoryItem::image_path` convention)
- `status` (string enum via `casts()`: `draft` / `published`, default `draft`)
- `published_at` (timestamp, nullable — set automatically the first time status flips to `published`; not editable by hand, avoids backdating confusion)
- `meta_title` (string, nullable)
- `meta_description` (string, nullable)
- timestamps

Indexes: `slug` unique, `status` + `published_at` composite (list-query filter).

## Backend

### Models
- `App\Models\BlogPost` — `belongsTo(AdminUser::class)`, `belongsTo(BlogCategory::class)`, `belongsToMany(BlogTag::class)`, no `HasAuditLogs` (platform content, not tenant business data — consistent with other `Admin*`-owned models), no `TenantScope`. Accessor `getFeaturedImageUrlAttribute()` mirroring `InventoryItem::getImageUrlAttribute()` (`Storage::disk(uploads_disk)->temporaryUrl(...)` since `fabriku_s3` visibility is `public` but the existing accessor pattern is what the codebase already does — reuse it verbatim for consistency). Accessor `getContentHtmlAttribute()` — renders `content` through the `Markdown` helper described below.
- `App\Models\BlogCategory` — `hasMany(BlogPost::class)`.
- `App\Models\BlogTag` — `belongsToMany(BlogPost::class)`.

Markdown rendering: put the `CommonMarkConverter` call behind a tiny `app/Support/Markdown.php` static helper (`Markdown::toHtml(string $md): string`) rather than duplicating `new CommonMarkConverter()` at both the admin-preview endpoint and the public show page — two call sites is exactly the "avoid duplicating a non-trivial external call" case, not premature abstraction.

### `App\Http\Controllers\Admin\AdminBlogController`
Routes (inside existing `Route::prefix('admin')->name('admin.')->middleware(['auth:admin', AdminMiddleware::class])` group in `routes/web.php`):
```php
Route::resource('blog', AdminBlogController::class);
Route::post('blog-preview', [AdminBlogController::class, 'preview'])->name('blog.preview');
Route::resource('blog-categories', AdminBlogCategoryController::class)->except(['show']);
```

- **index()** — paginated list, eager-load `category`, filter by `?status=`. `Inertia::render('Admin/Blog/Index', ...)`.
- **create()** — pass `categories` list for the select. `Inertia::render('Admin/Blog/Form', ...)`.
- **store(StoreBlogPostRequest)** —
  - Auto-slug from title via `Str::slug()`; if taken, suffix `-2`, `-3`, ... (same collision-handling convention as `RoleController`/`sku` generators — this table's `slug` is globally unique, not per-tenant, so the naive `Str::slug($title)` collision is checked against the whole table, not scoped).
  - Tags: split the incoming comma-separated `tags` string, trim, `BlogTag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])` per tag, then `$post->tags()->sync($tagIds)`.
  - Featured image: `$request->file('featured_image')->storePublicly('blog', config('filesystems.uploads_disk', 'fabriku_s3'))` if present.
  - `published_at`: set to `now()` only if `status === 'published'` and `published_at` is currently null.
  - `admin_user_id = auth('admin')->id()`.
- **edit(BlogPost $post)** — no tenant scoping to worry about (table isn't tenant-scoped), so no special binding concerns; load `tags` for the comma-joined input default.
- **update(UpdateBlogPostRequest, BlogPost $post)** — same slug/tag/image handling as store; delete old `featured_image` from disk if a new file is uploaded (mirrors `InventoryItemController` update pattern); `published_at` only back-filled if transitioning draft→published for the first time (never overwritten once set).
- **destroy(BlogPost $post)** — delete `featured_image` from disk if present, then `$post->delete()` (pivot rows cascade via FK).
- **preview(Request $request)** — accepts raw `content` markdown from the form, returns `['html' => Markdown::toHtml($request->input('content', ''))]` as JSON — powers a live preview pane in the admin form without duplicating the render logic client-side.

### `App\Http\Controllers\Admin\AdminBlogCategoryController`
Standard resource minus `show` — `index`/`create`/`store`/`edit`/`update`/`destroy`. `destroy()` blocks deletion if `$category->posts()->exists()` (flash error, same pattern as `RoleController::destroy()`'s in-use guard).

### Form Requests
`StoreBlogPostRequest` / `UpdateBlogPostRequest`:
```php
'title' => ['required', 'string', 'max:255'],
'excerpt' => ['nullable', 'string', 'max:500'],
'content' => ['required', 'string'],
'featured_image' => ['nullable', 'image', 'max:4096'],
'status' => ['required', Rule::in(['draft', 'published'])],
'blog_category_id' => ['nullable', Rule::exists('blog_categories', 'id')],
'tags' => ['nullable', 'string', 'max:500'],
'meta_title' => ['nullable', 'string', 'max:255'],
'meta_description' => ['nullable', 'string', 'max:500'],
```
`authorize()` returns `$this->user() !== null` (matches project convention; real gate is the `auth:admin` + `AdminMiddleware` route stack, not the Form Request).

### Public controller — `App\Http\Controllers\BlogController`
```php
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
```
(placed near the existing `Route::get('/privasi', ...)` public routes in `routes/web.php`, outside every tenant/admin middleware group)

- **index(Request $request)** — `BlogPost::where('status', 'published')->with('category', 'tags')->when($request->category, fn ($q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)))->when($request->tag, fn ($q, $slug) => $q->whereHas('tags', fn ($q) => $q->where('slug', $slug)))->latest('published_at')->paginate(12)`. Also pass `categories` (all, for a filter nav) to the view.
- **show(BlogPost $post)** — route-model binding on `slug` column (`{post:slug}`); `abort_unless($post->status === 'published', 404)` (draft posts 404 for anonymous visitors, no distinction needed for a logged-out-only surface); render with `content_html` (via the `Markdown` helper / model accessor), `meta_title`, `meta_description`.

## Frontend

### Admin
- `resources/js/pages/Admin/Blog/Index.vue` — table (Judul, Kategori, Status badge, Tanggal publish, aksi Edit/Hapus), status filter tabs, "+ Tulis Post" button. Delete via existing `useSweetAlert` confirm pattern.
- `resources/js/pages/Admin/Blog/Form.vue` — `useForm()` with `title`, `excerpt`, `content`, `featured_image` (file input, same inline pattern as `Materials/Form.vue`: `FileReader` preview + `forceFormData: true` on submit), `status` (select draft/published), `blog_category_id` (select), `tags` (text input, comma-separated, pre-filled from `post.tags.map(t => t.name).join(', ')` on edit), `meta_title`, `meta_description`. Content field: textarea + a "Preview" tab that POSTs to `admin.blog.preview` (debounced) and renders the returned HTML in a bordered pane — no client-side markdown JS dependency.
- `resources/js/pages/Admin/BlogCategories/Index.vue` — simple list + inline create/rename (small enough to skip a separate Form page — same "lookup table" pattern as other simple admin master-data screens).
- Add a "Blog" nav entry to the admin sidebar/layout navigation (wherever `AdminLayout.vue`'s nav list currently lives), linking to `admin.blog.index`.

### Public
- `resources/js/pages/Blog/Index.vue` — card grid (featured image via existing `ProductThumbnail.vue`-style fallback, title, excerpt, date, category badge), category/tag filter chips, pagination. Not wrapped in `AppLayout` (that's tenant-authenticated chrome) — new lightweight public wrapper matching `Welcome.vue`'s header/footer, e.g. `resources/js/layouts/PublicLayout.vue`, reused by both `Blog/Index.vue` and `Blog/Show.vue` (two consumers — worth the shared layout, not premature).
- `resources/js/pages/Blog/Show.vue` — renders `content_html` via `v-html` (safe here: content authored exclusively by trusted `AdminUser`s through Commonmark, not user-generated input), `<Head>` (Inertia) sets `meta_title`/`meta_description` and `<title>`.

## Edge Cases / Validation Summary

| Case | Behavior |
|---|---|
| Public GET `/blog/{slug}` where post is `draft` | 404 |
| Public GET `/blog/{slug}` for nonexistent slug | 404 (default route-model-binding behavior) |
| Duplicate `title` → same slug | Auto-suffixed `-2`, `-3`, ... on create |
| Non-admin (or no admin session) hits `/admin/blog/*` | Redirect to admin login (existing `auth:admin` middleware behavior) |
| Delete a `BlogCategory` with existing posts | Rejected, flash error (mirrors `RoleController::destroy()` in-use guard) |
| Delete a `BlogPost` | Deletes `featured_image` from disk first, then row (pivot rows cascade) |
| Status flips draft→published | `published_at` set to `now()` once, never overwritten on subsequent edits |
| Status flips published→draft | `published_at` left untouched (so re-publishing doesn't need to "rediscover" original publish date) — post simply stops appearing publicly while draft |
| `tags` field with duplicate/whitespace-only entries | Trimmed, empty entries dropped, `firstOrCreate` de-dupes by slug |

## Testing

`tests/Feature/AdminBlogTest.php` (new):
- Admin can create a post (with image, category, tags) → visible in admin index.
- Admin can update a post, re-uploading a featured image deletes the old file from disk.
- Admin can delete a post.
- Non-admin / guest hitting any `admin.blog.*` route is redirected to admin login, not 500/403 crash.
- Creating two posts with the same title produces two different slugs.
- `blog_category_id` pointing at a nonexistent category → 422.
- Deleting a category that still has posts is rejected.

`tests/Feature/PublicBlogTest.php` (new):
- `GET /blog` lists only `published` posts, not `draft`.
- `GET /blog/{slug}` for a published post returns 200 with rendered HTML content.
- `GET /blog/{slug}` for a draft post returns 404.
- `GET /blog?category=<slug>` filters correctly.
- `GET /blog?tag=<slug>` filters correctly.
- Publishing a post sets `published_at`; a later unrelated edit doesn't change it.
