<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    public const DEMO_EMAILS = [
        'admin@konveksi.com',
        'admin@kuemama.com',
        'admin@crafty.com',
        'admin@glowbeauty.com',
        'admin@tokoserbaada.com',
        'admin@homemade.com',
        'admin@bengkel.com',
    ];

    public const DEMO_DOMAINS = [
        'konveksi.com',
        'kuemama.com',
        'crafty.com',
        'glowbeauty.com',
        'tokoserbaada.com',
        'homemade.com',
        'bengkel.com',
    ];

    public const DEMO_NAMES = [
        'Konveksi Fabriku',
        'Kue Mama Homemade',
        'Crafty Handmade',
        'Glow Beauty Lab',
        'Toko Serba Ada',
        'Dapur Coklat Rumahan',
        'Bengkel Motor Maju Jaya',
    ];

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
        'trial_reminder_7days_sent_at',
        'trial_reminder_3days_sent_at',
        'trial_reminder_1day_sent_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'subscription_expires_at' => 'datetime',
            'trial_reminder_7days_sent_at' => 'datetime',
            'trial_reminder_3days_sent_at' => 'datetime',
            'trial_reminder_1day_sent_at' => 'datetime',
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

    /**
     * Check if tenant is a demo tenant
     */
    public function isDemo(): bool
    {
        if (in_array($this->name, self::DEMO_NAMES, true)) {
            return true;
        }

        if (stripos((string) $this->name, 'demo') !== false) {
            return true;
        }

        if ($this->relationLoaded('users')) {
            return $this->users->contains(fn ($u) => $u->isDemo());
        }

        return $this->users()->where(function ($q) {
            $q->whereIn('email', self::DEMO_EMAILS)
                ->orWhere('email', 'like', '%demo%');
            foreach (self::DEMO_DOMAINS as $domain) {
                $q->orWhere('email', 'like', "%@{$domain}");
            }
        })->exists();
    }

    /**
     * Scope a query to exclude demo tenants
     */
    public function scopeWithoutDemo($query)
    {
        return $query->whereNotIn('name', self::DEMO_NAMES)
            ->where('name', 'not like', '%demo%')
            ->whereDoesntHave('users', function ($uq) {
                $uq->whereIn('email', self::DEMO_EMAILS)
                    ->orWhere('email', 'like', '%demo%');
                foreach (self::DEMO_DOMAINS as $domain) {
                    $uq->orWhere('email', 'like', "%@{$domain}");
                }
            });
    }
}
