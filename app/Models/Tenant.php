<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    protected $attributes = [
        'plan_code' => 'trial',
        'pro_auto_renew' => false,
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
        'plan_code',
        'pro_expires_at',
        'pro_auto_renew',
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
            'pro_expires_at' => 'datetime',
            'pro_auto_renew' => 'boolean',
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

    public function businessSite(): HasOne
    {
        return $this->hasOne(BusinessSite::class);
    }

    public function usageEvents(): HasMany
    {
        return $this->hasMany(UsageEvent::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function siteProducts(): HasMany
    {
        return $this->hasMany(SiteProduct::class);
    }

    public function isActive(): bool
    {
        return $this->is_active &&
               ($this->subscription_expires_at === null || $this->subscription_expires_at->isFuture());
    }

    /**
     * Check if tenant has an active Pro subscription or within 7-day grace period.
     */
    public function hasPro(bool $allowGracePeriod = true): bool
    {
        if (! $this->pro_expires_at) {
            return false;
        }

        if ($this->pro_expires_at->isFuture()) {
            return true;
        }

        if ($allowGracePeriod) {
            return now()->lessThanOrEqualTo($this->pro_expires_at->copy()->addDays(7));
        }

        return false;
    }

    /**
     * Check if tenant has access to a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        $coreFeatures = config('plans.plans.core.features', []);
        if (isset($coreFeatures[$feature]) && $coreFeatures[$feature]) {
            return $this->isActive();
        }

        $proFeatures = config('plans.addons.pro.features', []);
        if (isset($proFeatures[$feature]) && $proFeatures[$feature]) {
            return $this->hasPro();
        }

        return false;
    }

    /**
     * Get maximum quota allowed for a given type.
     */
    public function quotaLimit(string $type): int
    {
        if (! $this->hasPro()) {
            return 0;
        }

        return (int) config("plans.addons.pro.quotas.{$type}", 0);
    }

    /**
     * Determine the start date of the current billing cycle.
     */
    public function currentPeriodStart(): Carbon
    {
        $latestPayment = $this->subscriptionPayments()
            ->where('status', 'approved')
            ->whereNotNull('period_start')
            ->where('period_start', '<=', now())
            ->latest('period_start')
            ->first();

        if ($latestPayment && $latestPayment->period_start) {
            return Carbon::parse($latestPayment->period_start)->startOfDay();
        }

        return now()->startOfMonth();
    }

    /**
     * Calculate quota used in the current period.
     */
    public function quotaUsed(string $type, ?CarbonInterface $periodStart = null): int
    {
        $start = $periodStart ? Carbon::instance($periodStart) : $this->currentPeriodStart();

        return (int) $this->usageEvents()
            ->active()
            ->forType($type)
            ->forPeriod($start)
            ->sum('quantity');
    }

    /**
     * Calculate remaining quota for a given type.
     */
    public function quotaRemaining(string $type, ?CarbonInterface $periodStart = null): int
    {
        return max(0, $this->quotaLimit($type) - $this->quotaUsed($type, $periodStart));
    }

    /**
     * Record consumption of quota with idempotency.
     */
    public function recordUsage(string $type, int $quantity = 1, ?string $idempotencyKey = null, ?array $metadata = null): UsageEvent
    {
        $key = $idempotencyKey ?: (string) Str::uuid();

        $existing = $this->usageEvents()->where('idempotency_key', $key)->first();
        if ($existing) {
            return $existing;
        }

        $start = $this->currentPeriodStart();
        $remaining = $this->quotaRemaining($type, $start);

        if ($remaining < $quantity) {
            throw new \DomainException("Kuota untuk '{$type}' tidak mencukupi (sisa: {$remaining}, diminta: {$quantity}).");
        }

        return $this->usageEvents()->create([
            'type' => $type,
            'period_start' => $start->toDateString(),
            'quantity' => $quantity,
            'idempotency_key' => $key,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Reverse/refund a previously recorded usage.
     */
    public function reverseUsage(string $idempotencyKey): bool
    {
        $event = $this->usageEvents()
            ->where('idempotency_key', $idempotencyKey)
            ->active()
            ->first();

        if (! $event) {
            return false;
        }

        $event->update(['reversed_at' => now()]);

        return true;
    }

    /**
     * Check if a module is enabled according to category rules.
     */
    public function isModuleEnabled(string $module): bool
    {
        $config = $this->getCategoryConfig();

        return $config['rules'][$module] ?? true;
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
