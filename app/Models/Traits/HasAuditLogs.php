<?php

namespace App\Models\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasAuditLogs
{
    /**
     * Boot the trait
     */
    public static function bootHasAuditLogs(): void
    {
        static::created(function (Model $model) {
            $model->logAudit('created', [], $model->getAuditableAttributes());
        });

        static::updated(function (Model $model) {
            $oldValues = $model->getOriginalAuditableAttributes();
            $newValues = $model->getChangedAuditableAttributes();

            if (! empty($newValues)) {
                $model->logAudit('updated', $oldValues, $newValues);
            }
        });

        static::deleted(function (Model $model) {
            // Check if soft delete
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                $model->logAudit('deleted', $model->getAuditableAttributes(), []);
            } else {
                $model->logAudit('force_deleted', $model->getAuditableAttributes(), []);
            }
        });

        // Handle soft delete restoration
        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                $model->logAudit('restored', [], $model->getAuditableAttributes());
            });
        }
    }

    /**
     * Get the audit logs for this model
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Create an audit log entry
     *
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     * @param  array<string, mixed>  $metadata
     */
    public function logAudit(string $event, array $oldValues = [], array $newValues = [], array $metadata = []): ?AuditLog
    {
        // Don't log if disabled
        if (property_exists($this, 'disableAuditing') && $this->disableAuditing) {
            return null;
        }

        $user = Auth::user();

        return AuditLog::create([
            'tenant_id' => $this->getTenantIdForAudit(),
            'user_id' => $user?->id,
            'event' => $event,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'old_values' => ! empty($oldValues) ? $oldValues : null,
            'new_values' => ! empty($newValues) ? $newValues : null,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => ! empty($metadata) ? $metadata : null,
        ]);
    }

    /**
     * Get the tenant ID for audit logging
     */
    protected function getTenantIdForAudit(): ?int
    {
        // First try to get from model
        if (isset($this->tenant_id)) {
            return $this->tenant_id;
        }

        // Then try to get from authenticated user
        $user = Auth::user();
        if ($user && isset($user->tenant_id)) {
            return $user->tenant_id;
        }

        return null;
    }

    /**
     * Get attributes that should be audited
     *
     * @return array<string, mixed>
     */
    protected function getAuditableAttributes(): array
    {
        $attributes = $this->getAttributes();

        return $this->filterExcludedAttributes($attributes);
    }

    /**
     * Get original attributes that were changed (filtered for auditing)
     *
     * @return array<string, mixed>
     */
    protected function getOriginalAuditableAttributes(): array
    {
        $dirty = $this->getDirty();
        $original = [];

        foreach (array_keys($dirty) as $key) {
            $original[$key] = $this->getOriginal($key);
        }

        return $this->filterExcludedAttributes($original);
    }

    /**
     * Get changed attributes (filtered for auditing)
     *
     * @return array<string, mixed>
     */
    protected function getChangedAuditableAttributes(): array
    {
        $dirty = $this->getDirty();

        return $this->filterExcludedAttributes($dirty);
    }

    /**
     * Filter out excluded attributes from auditing
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function filterExcludedAttributes(array $attributes): array
    {
        $excluded = $this->getExcludedAuditAttributes();

        return array_diff_key($attributes, array_flip($excluded));
    }

    /**
     * Get attributes that should be excluded from auditing
     *
     * @return array<string>
     */
    protected function getExcludedAuditAttributes(): array
    {
        // Default excluded attributes
        $defaults = [
            'password',
            'remember_token',
            'updated_at',
            'created_at',
            'deleted_at',
        ];

        // Allow models to define custom excluded attributes
        if (property_exists($this, 'excludedFromAudit')) {
            return array_merge($defaults, $this->excludedFromAudit);
        }

        return $defaults;
    }

    /**
     * Temporarily disable auditing for this model instance
     */
    public function disableAuditing(): static
    {
        $this->disableAuditing = true;

        return $this;
    }

    /**
     * Re-enable auditing for this model instance
     */
    public function enableAuditing(): static
    {
        $this->disableAuditing = false;

        return $this;
    }

    /**
     * Execute a callback without auditing
     */
    public static function withoutAuditing(callable $callback): mixed
    {
        $model = new static;
        $model->disableAuditing = true;

        return $callback($model);
    }
}
