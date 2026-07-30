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
