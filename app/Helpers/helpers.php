<?php

if (! function_exists('fake')) {
    /**
     * Get a faker instance.
     *
     * @param  string|null  $locale
     * @return \Faker\Generator
     */
    function fake(?string $locale = null)
    {
        if (app()->bound('config')) {
            $locale ??= app('config')->get('app.faker_locale', 'en_US');
        }

        $abstract = \Faker\Generator::class.':'.$locale;

        if (! app()->bound($abstract)) {
            app()->singleton($abstract, fn () => \Faker\Factory::create($locale ?? 'en_US'));
        }

        return app()->make($abstract);
    }
}
