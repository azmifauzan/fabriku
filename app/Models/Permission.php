<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    /**
     * Get the roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Get permissions by module
     */
    public static function byModule(string $module)
    {
        return static::where('module', $module)->get();
    }

    /**
     * Get all modules
     */
    public static function getModules(): array
    {
        return static::distinct('module')
            ->whereNotNull('module')
            ->pluck('module')
            ->toArray();
    }
}
