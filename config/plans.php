<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Plans and Add-ons Configuration
    |--------------------------------------------------------------------------
    |
    | Defines pricing, features, and quotas for Core plans and Pro add-on.
    | As decided in Master Plan (23 September 2026):
    | - Core: Rp25.000/month, Rp250.000/year (includes catalog Website Usaha)
    | - Pro Add-on: Rp49.000/month, Rp490.000/year (AI theme design, custom domain,
    |   AI copywriting, and Pusat Sosial).
    |
    */

    'pricing' => [
        'core' => [
            'monthly' => 25000,
            'yearly' => 250000,
        ],
        'pro' => [
            'monthly' => 49000,
            'yearly' => 490000,
        ],
    ],

    'plans' => [
        'trial' => [
            'name' => 'Trial 30 Hari',
            'features' => [
                'business_site' => true,
                'catalog_templates' => true,
                'storefront_orders' => true,
            ],
            'quotas' => [],
        ],
        'core' => [
            'name' => 'Fabriku Core',
            'features' => [
                'business_site' => true,
                'catalog_templates' => true,
                'storefront_orders' => true,
            ],
            'quotas' => [],
        ],
    ],

    'addons' => [
        'pro' => [
            'name' => 'Fabriku Pro',
            'features' => [
                'ai_theme_design' => true,
                'ai_section_regeneration' => true,
                'custom_domain' => true,
                'ai_copywriting' => true,
                'social_center' => true,
                'social_stats' => true,
                'social_inbox' => true,
                'social_scheduled_posts' => true,
                'social_kit' => true,
            ],
            'quotas' => [
                'social_accounts' => 2,
                'social_scheduled_posts' => 60,
                'ai_copywriting' => 50,
                'ai_theme_design' => 3,
                'ai_section_regeneration' => 20,
                'social_kit' => 8,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reserved Slugs
    |--------------------------------------------------------------------------
    |
    | Subdomains reserved for system, internal, or infrastructure use.
    |
    */
    'reserved_slugs' => [
        'www',
        'app',
        'admin',
        'api',
        'mail',
        'smtp',
        'ftp',
        'blog',
        'help',
        'status',
        'toko',
        'cdn',
        'static',
        'assets',
        'fabriku',
        'satsetui',
        'repliz',
    ],
];
