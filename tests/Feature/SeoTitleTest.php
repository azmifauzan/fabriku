<?php

use Inertia\Ssr\Gateway;
use Inertia\Ssr\Response as SsrResponse;

it('renders the fallback title when SSR is not available', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect(substr_count($html, '<title'))->toBe(1)
        ->and($html)->toContain('Operasional UMKM dalam satu alur');
});

it('renders only the SSR title when SSR responds', function () {
    app()->instance(Gateway::class, new class implements Gateway
    {
        public function dispatch(array $page): ?SsrResponse
        {
            return new SsrResponse('<title inertia>Judul Halaman SSR</title>', '<div id="app"></div>');
        }
    });

    $html = $this->get('/')->assertOk()->getContent();

    expect(substr_count($html, '<title'))->toBe(1)
        ->and($html)->toContain('Judul Halaman SSR')
        ->and($html)->not->toContain('Operasional UMKM dalam satu alur');
});
