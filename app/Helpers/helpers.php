<?php

use Faker\Factory;
use Faker\Generator;

if (! function_exists('fake')) {
    /**
     * Get a faker instance.
     *
     * @return Generator
     */
    function fake(?string $locale = null)
    {
        if (app()->bound('config')) {
            $locale ??= app('config')->get('app.faker_locale', 'en_US');
        }

        $abstract = Generator::class.':'.$locale;

        if (! app()->bound($abstract)) {
            app()->singleton($abstract, fn () => Factory::create($locale ?? 'en_US'));
        }

        return app()->make($abstract);
    }
}
