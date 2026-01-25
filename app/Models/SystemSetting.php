<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the tenant that owns the setting
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get a setting value
     */
    public static function get(string $key, $default = null, ?int $tenantId = null)
    {
        $setting = static::where('key', $key)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string', ?int $tenantId = null): void
    {
        static::updateOrCreate(
            ['key' => $key, 'tenant_id' => $tenantId],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
            ]
        );
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? (float) $value : $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Get all settings for a tenant
     */
    public static function getAllForTenant(?int $tenantId = null): array
    {
        $settings = static::where('tenant_id', $tenantId)->get();

        return $settings->mapWithKeys(function ($setting) {
            return [$setting->key => static::castValue($setting->value, $setting->type)];
        })->toArray();
    }
}
