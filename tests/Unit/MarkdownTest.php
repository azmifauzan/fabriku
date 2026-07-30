<?php

use App\Support\Markdown;

it('converts markdown to html', function () {
    $html = Markdown::toHtml("# Judul\n\nParagraf **tebal** dan _miring_.");

    expect($html)->toContain('<h1>Judul</h1>');
    expect($html)->toContain('<strong>tebal</strong>');
    expect($html)->toContain('<em>miring</em>');
});

it('returns empty string for empty input', function () {
    expect(Markdown::toHtml(''))->toBe('');
});
