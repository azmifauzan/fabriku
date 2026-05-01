<?php

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('redirects to login with flash error when session expires (419)', function () {
    $this->withExceptionHandling();

    // Simulate a 419 HttpException like the CSRF middleware produces
    Route::post('/_test/csrf-mismatch', function () {
        throw new HttpException(419, 'CSRF token mismatch.');
    })->withoutMiddleware(ValidateCsrfToken::class);

    $response = $this->post('/_test/csrf-mismatch');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
});
