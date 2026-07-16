<?php

it('does not leak the raw exception message when APP_DEBUG is false', function () {
    config(['app.debug' => false]);

    $html = view('errors.500', ['exception' => new Exception('SECRET SQL: insert into material_receipts (receipt_number) values (REC-2026-0001)')])->render();

    expect($html)->not->toContain('SECRET SQL');
    expect($html)->toContain('Terjadi kesalahan internal pada server kami');
});

it('shows the raw exception message when APP_DEBUG is true', function () {
    config(['app.debug' => true]);

    $html = view('errors.500', ['exception' => new Exception('Specific debug detail')])->render();

    expect($html)->toContain('Specific debug detail');
});
