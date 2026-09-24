<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'style',
        'mode',
        'artifact',
        'css_path',
        'preview_image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'artifact' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function themeVersions(): HasMany
    {
        return $this->hasMany(SiteThemeVersion::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForMode(Builder $query, string $mode): Builder
    {
        return $query->where('mode', $mode);
    }
}
