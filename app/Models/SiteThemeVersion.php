<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteThemeVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'business_site_id',
        'source',
        'site_template_id',
        'satsetui_generation_id',
        'version',
        'theme',
        'sections',
        'css_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'theme' => 'array',
            'sections' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function businessSite(): BelongsTo
    {
        return $this->belongsTo(BusinessSite::class);
    }

    public function siteTemplate(): BelongsTo
    {
        return $this->belongsTo(SiteTemplate::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get CSS variables declared in the theme.
     */
    public function getCssVariables(): array
    {
        return $this->theme['css_variables'] ?? [
            '--fb-primary' => '#1e3a8a',
            '--fb-accent' => '#3b82f6',
            '--fb-bg' => '#ffffff',
            '--fb-surface' => '#f8fafc',
            '--fb-text' => '#0f172a',
            '--fb-font-heading' => 'inherit',
            '--fb-font-body' => 'inherit',
            '--fb-radius' => '0.5rem',
        ];
    }

    /**
     * Get the shell HTML (header + footer wrappers).
     */
    public function getShellHtml(): string
    {
        return $this->theme['shell_html'] ?? '';
    }
}
