<?php

test('401 unauthorized page renders successfully with brand elements', function () {
    $response = $this->get('/errors/401');

    $response->assertStatus(401);
    $response->assertSee('Akses Tidak Diizinkan');
    $response->assertSee('Fabriku');
    $response->assertSee('fabriku-logo-only.png');
});

test('403 forbidden page renders successfully with brand elements', function () {
    $response = $this->get('/errors/403');

    $response->assertStatus(403);
    $response->assertSee('Akses Dibatasi');
    $response->assertSee('Fabriku');
    $response->assertSee('fabriku-logo-only.png');
});

test('404 not found page renders successfully with brand elements', function () {
    $response = $this->get('/errors/404');

    $response->assertStatus(404);
    $response->assertSee('Halaman Tidak Ditemukan');
    $response->assertSee('Fabriku');
    $response->assertSee('fabriku-logo-only.png');
});

test('419 page expired page renders successfully with brand elements', function () {
    $response = $this->get('/errors/419');

    $response->assertStatus(419);
    $response->assertSee('Sesi Telah Berakhir');
    $response->assertSee('Fabriku');
    $response->assertSee('fabriku-logo-only.png');
});

test('429 too many requests page renders successfully with brand elements', function () {
    $response = $this->get('/errors/429');

    $response->assertStatus(429);
    $response->assertSee('Terlalu Banyak Permintaan');
    $response->assertSee('Fabriku');
    $response->assertSee('fabriku-logo-only.png');
});

test('500 internal server error page renders successfully with brand elements', function () {
    $response = $this->get('/errors/500');

    $response->assertStatus(500);
    $response->assertSee('Kesalahan Server Internal');
    $response->assertSee('Fabriku');
    $response->assertSee('fabriku-logo-only.png');
});

test('503 service unavailable page renders successfully with brand elements', function () {
    $response = $this->get('/errors/503');

    $response->assertStatus(503);
    $response->assertSee('Pemeliharaan Sistem');
    $response->assertSee('Fabriku');
    $response->assertSee('fabriku-logo-only.png');
});
