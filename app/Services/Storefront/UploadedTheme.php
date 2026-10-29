<?php

namespace App\Services\Storefront;

use App\Models\BusinessSite;
use App\Models\SiteThemeVersion;
use DOMDocument;
use DOMElement;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class UploadedTheme
{
    public function __construct(private HtmlSanitizerService $sanitizer) {}

    public function parseArchive(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            $this->invalid('Arsip ZIP tidak dapat dibaca.');
        }
        try {
            if ($zip->numFiles > 30) {
                $this->invalid('ZIP maksimal berisi 30 file.');
            }
            $files = [];
            $size = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->statIndex($i);
                $name = $entry['name'];
                $size += $entry['size'];
                if (! preg_match('/^[a-z0-9][a-z0-9-]*\.(html|css)$/', $name) || isset($files[$name]) || $size > 2_000_000 || $entry['size'] > 500_000) {
                    $this->invalid('Gunakan maksimal 30 file HTML/CSS di root ZIP dengan nama huruf kecil, angka, atau tanda hubung. Ukuran isi maksimal 2 MB.');
                }
                $content = $zip->getFromIndex($i);
                if ($content === false || strlen($content) !== $entry['size']) {
                    $this->invalid('Isi ZIP tidak dapat dibaca.');
                }
                $files[$name] = $content;
            }
        } finally {
            $zip->close();
        }

        return $this->parseFiles($files);
    }

    public function parseFiles(array $files): array
    {
        if (count($files) > 30) {
            $this->invalid('Template maksimal berisi 30 file.');
        }
        $size = 0;
        foreach ($files as $name => $content) {
            if (! is_string($name) || ! is_string($content) || ! preg_match('/^[a-z0-9][a-z0-9-]*\.(html|css)$/', $name)) {
                $this->invalid('Gunakan nama file HTML/CSS dengan huruf kecil, angka, dan tanda hubung.');
            }
            $size += strlen($content);
            if (strlen($content) > 500_000 || $size > 2_000_000) {
                $this->invalid('Isi template maksimal 2 MB dan setiap file maksimal 500 KB.');
            }
        }
        if (! isset($files['index.html'])) {
            $this->invalid('Template harus memiliki index.html sebagai halaman depan.');
        }
        $commonCss = '';
        foreach ($files as $name => $content) {
            if (str_ends_with($name, '.css')) {
                $commonCss .= "\n".$content;
            }
        }
        $commonCss = $this->safeCss($commonCss);
        $pages = [];
        foreach ($files as $name => $content) {
            if (! str_ends_with($name, '.html')) {
                continue;
            }
            $page = $this->parse($content);
            if ($name === 'index.html') {
                $home = $page;
            } else {
                $slug = substr($name, 0, -5);
                $pages[$slug] = ['html' => $page['html'], 'css' => $page['css']];
            }
        }
        $home['css'] = $commonCss."\n".$home['css'];
        foreach ($pages as &$page) {
            $page['css'] = $home['css']."\n".$page['css'];
        }
        unset($page);
        if (strlen($home['css']) > 100_000 || collect($pages)->contains(fn ($page) => strlen($page['css']) > 100_000)) {
            $this->invalid('Gabungan CSS setiap halaman maksimal 100 KB.');
        }
        $home['pages'] = $pages;
        $home['files'] = $files;

        return $home;
    }

    public function editorFiles(BusinessSite $site, SiteThemeVersion $template, ThemeRenderer $renderer): array
    {
        if (isset($template->theme['editor_files'])) {
            return $template->theme['editor_files'];
        }
        $sections = $template->sections ?? $renderer->getDefaultSections($site);
        usort($sections, fn ($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));
        $body = '';
        foreach ($sections as $section) {
            if (($section['is_visible'] ?? true) === true) {
                $body .= $renderer->replaceVariables($section['html'] ?? '', $section['variables'] ?? [])."\n";
            }
        }
        $shell = $template->theme['shell_html'] ?? '';
        preg_match('/<header\b.*?<\/header>/is', $shell, $header);
        preg_match('/<footer\b.*?<\/footer>/is', $shell, $footer);
        $document = fn ($html) => "<!doctype html>\n<html lang=\"id\">\n<head><meta charset=\"utf-8\"></head>\n<body>\n{$html}\n</body>\n</html>";
        $files = ['index.html' => $document(($header[0] ?? '')."\n".$body."\n".($footer[0] ?? ''))];
        $css = $template->theme['satsetui_css'] ?? '';
        foreach ($renderer->resolveCssVariables($site, $template) as $key => $value) {
            if (preg_match('/^--fb-[a-z-]+$/', $key) && is_string($value) && preg_match('/^(#[0-9a-fA-F]{6}|[0-9.]+(?:px|rem)|inherit)$/', $value)) {
                $css .= "\n:root { {$key}: {$value}; }";
            }
        }
        $files['theme.css'] = $css;
        foreach ($template->templatePages(true) as $slug => $page) {
            $pageCss = $page['css'] ?? '';
            $style = $pageCss === '' || $pageCss === ($template->theme['satsetui_css'] ?? '') ? '' : '<style>'.$pageCss.'</style>';
            $files[$slug.'.html'] = $document($style.($page['html'] ?? ''));
        }

        return $files;
    }

    public function attributes(array $parsed, string $name, array $originalTheme = []): array
    {
        return [
            'theme' => array_merge($originalTheme, [
                'name' => $name,
                'shell_html' => $parsed['shell'],
                'satsetui_css' => $parsed['css'],
                'uploaded_pages' => $parsed['pages'],
                'editor_files' => $parsed['files'],
                'uses_template_pages' => true,
            ]),
            'sections' => [['type' => 'template', 'sort' => 0, 'is_visible' => true, 'variables' => [], 'html' => $parsed['html']]],
        ];
    }

    public function parse(string $html): array
    {
        if (! mb_check_encoding($html, 'UTF-8') || trim($html) === '') {
            $this->invalid('File harus berupa HTML UTF-8 yang tidak kosong.');
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument;
        if (! preg_match('/<html\b/i', $html)) {
            $html = '<!doctype html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>';
        }
        $loaded = $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (! $loaded) {
            $this->invalid('HTML tidak dapat dibaca.');
        }

        $slots = ['logo', 'nav', 'products', 'services', 'lead-form', 'whatsapp-button', 'cart-button', 'map', 'footer-links', 'featured-product'];
        $css = '';
        foreach (iterator_to_array($document->getElementsByTagName('style')) as $style) {
            $css .= "\n".$style->textContent;
            $style->parentNode->removeChild($style);
        }
        $css = $this->safeCss($css);

        foreach ($document->getElementsByTagName('*') as $element) {
            if (in_array(strtolower($element->tagName), ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'link', 'base'], true)) {
                $this->invalid('HTML berisi elemen yang tidak diizinkan: '.$element->tagName.'. Gunakan slot Fabriku untuk fungsi interaktif.');
            }
            foreach ($element->attributes as $attribute) {
                if (str_starts_with(strtolower($attribute->name), 'on') || strtolower($attribute->name) === 'style') {
                    $this->invalid('Event handler dan atribut style tidak diizinkan; gunakan elemen style untuk CSS.');
                }
            }
            if ($element->hasAttribute('data-fb-slot')) {
                if (! in_array($element->getAttribute('data-fb-slot'), $slots, true)
                    || ! in_array(strtolower($element->tagName), ['div', 'span'], true)) {
                    $this->invalid('Gunakan elemen div/span dengan nama slot Fabriku yang dikenal.');
                }
                foreach ($element->getElementsByTagName('*') as $child) {
                    if ($child->hasAttribute('data-fb-slot')) {
                        $this->invalid('Slot tidak boleh berisi slot lain.');
                    }
                }
            }
        }

        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body) {
            $this->invalid('HTML tidak memiliki isi halaman.');
        }
        $shell = '';
        $content = '';
        $seen = [];
        foreach ($body->childNodes as $node) {
            if ($node instanceof DOMElement && $node->hasAttribute('data-fb-shell')) {
                $type = $node->getAttribute('data-fb-shell');
                if (! in_array($type, ['header', 'footer'], true) || $node->tagName !== $type || isset($seen[$type])) {
                    $this->invalid('Shell harus berupa satu header dan satu footer langsung di dalam body.');
                }
                $seen[$type] = true;
                $shell .= $document->saveHTML($node);
            } else {
                $content .= $document->saveHTML($node);
            }
        }
        $content = $this->sanitizer->sanitize($content);
        if (trim($content) === '') {
            $this->invalid('Isi halaman tidak boleh kosong.');
        }

        return ['html' => $content, 'shell' => $this->sanitizer->sanitize($shell), 'css' => $css];
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['template' => $message]);
    }

    private function safeCss(string $css): string
    {
        if (strlen($css) <= 100_000 && trim(preg_replace('~/\*.*?\*/~s', '', $css) ?? '') === '') {
            return '';
        }
        $clean = $this->sanitizer->sanitizeCss($css);
        if (trim($css) !== '' && ($clean === '' || preg_match('~https?:|//|image-set\s*\(|@font-face~i', $clean))) {
            $this->invalid('CSS tidak aman atau melebihi 100 KB. URL, @import, font eksternal dan escape CSS tidak diperbolehkan.');
        }

        return $clean;
    }
}
