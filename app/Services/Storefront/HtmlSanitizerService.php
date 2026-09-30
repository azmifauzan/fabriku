<?php

namespace App\Services\Storefront;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizerService
{
    protected HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeMedias()
            ->allowRelativeLinks()
            ->allowAttribute('data-fb-shell', '*')
            ->allowAttribute('data-fb-section', '*')
            ->allowAttribute('data-fb-slot', '*')
            ->allowAttribute('data-fb-text', '*')
            ->allowAttribute('data-fb-image', '*')
            ->allowAttribute('data-fb-link', '*')
            ->allowAttribute('class', '*')
            ->allowAttribute('id', '*')
            ->allowAttribute('target', ['a'])
            ->allowAttribute('rel', ['a'])
            ->allowElement('svg', ['class', 'viewbox', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'width', 'height', 'xmlns'])
            ->allowElement('path', ['d', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin'])
            ->allowElement('circle', ['cx', 'cy', 'r', 'fill', 'stroke'])
            ->allowElement('line', ['x1', 'y1', 'x2', 'y2', 'stroke'])
            ->allowElement('rect', ['x', 'y', 'width', 'height', 'rx', 'ry', 'fill', 'stroke']);

        $this->sanitizer = new HtmlSanitizer($config);
    }

    /**
     * Sanitize HTML content according to fabriku-site-v1 rules.
     */
    public function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        // 1. Sanitize via Symfony HTML Sanitizer (removes script, iframe, forms, event handlers, javascript: URIs)
        $clean = $this->sanitizer->sanitize($html);

        // 2. Extra defense: strip any remaining dangerous patterns like data:text/html or inline events if any slipped
        $clean = preg_replace('/javascript:/i', 'blocked:', $clean);
        $clean = preg_replace('/vbscript:/i', 'blocked:', $clean);
        $clean = preg_replace('/data:(?!image\/)/i', 'blocked:', $clean);

        return $clean;
    }

    public function sanitizeCss(string $css): string
    {
        $css = trim($css);
        if ($css === '' || strlen($css) > 100_000) {
            return '';
        }

        // ponytail: no CSS parser installed; reject network loads and executable legacy syntax.
        $css = preg_replace('~/\*.*?\*/~s', '', $css) ?? '';
        if (str_contains($css, '\\') || preg_match('/@import|url\s*\(|expression\s*\(|javascript:|vbscript:|-moz-binding|behavior\s*:|[<>]/i', $css)) {
            return '';
        }

        return $css;
    }
}
