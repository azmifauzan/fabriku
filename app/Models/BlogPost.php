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
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
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
