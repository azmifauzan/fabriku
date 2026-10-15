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
            'featured_image' => ['nullable', 'image', 'max:4096', 'dimensions:min_width=600,min_height=315'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'blog_category_id' => ['nullable', Rule::exists('blog_categories', 'id')],
            'tags' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Social previews (Threads/FB/WA) drop images under 600x315.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'featured_image.dimensions' => 'Gambar utama minimal 600×315 px (disarankan 1200×630) agar tampil sebagai thumbnail di medsos.',
        ];
    }
}
