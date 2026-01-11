<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo_url',
        'is_active',
        'business_category',
        'category_settings',
        'subscription_plan',
        'subscription_expires_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'subscription_expires_at' => 'datetime',
            'settings' => 'array',
            'category_settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isActive(): bool
    {
        return $this->is_active &&
               ($this->subscription_expires_at === null || $this->subscription_expires_at->isFuture());
    }

    /**
     * Get category configuration
     */
    public function getCategoryConfig(): array
    {
        $category = $this->business_category ?? config('business.default_category');

        return config("business.categories.{$category}", []);
    }

    /**
     * Get category terminology
     */
    public function getTerminology(string $key): string
    {
        $config = $this->getCategoryConfig();

        return $config['terminology'][$key] ?? ucfirst($key);
    }

    /**
     * Get category label
     */
    public function getCategoryLabel(): string
    {
        $config = $this->getCategoryConfig();

        return $config['label'] ?? ucfirst($this->business_category);
    }
}
