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
