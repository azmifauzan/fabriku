<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Regenerates the 1200x630 social-preview PNGs in public/images/og/ (committed to the repo).
 * Dev-only: needs GD with FreeType and a system TTF; rerun after editing config/landing_pages.php.
 */
class GenerateOgImages extends Command
{
    protected $signature = 'og:generate-images {--font=/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf}';

    protected $description = 'Generate Open Graph preview images for the home page and category landing pages';

    public function handle(): int
    {
        $font = $this->option('font');

        if (! is_file($font)) {
            $this->error("Font tidak ditemukan: {$font}");

            return self::FAILURE;
        }

        $dir = public_path('images/og');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->render($font, 'UMKM Indonesia', 'Produksi, stok, penjualan, dan website usaha dalam satu aplikasi.', public_path('images/og-default.png'));

        foreach (config('landing_pages') as $slug => $page) {
            $this->render($font, $page['eyebrow'], $page['headline'], "{$dir}/{$slug}.png");
        }

        $this->info('Gambar OG dibuat di public/images.');

        return self::SUCCESS;
    }

    private function render(string $font, string $eyebrow, string $headline, string $path): void
    {
        $canvas = imagecreatetruecolor(1200, 630);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        $navy = imagecolorallocate($canvas, 0x13, 0x34, 0x62);
        $muted = imagecolorallocate($canvas, 0x64, 0x74, 0x8B);

        // Brand bar on the left edge.
        imagefilledrectangle($canvas, 0, 0, 23, 630, $navy);

        $logo = imagecreatefrompng(public_path('images/fabriku-word.png'));
        imagecopyresampled($canvas, $logo, 90, 70, 0, 0, 280, 64, 400, 91);

        imagettftext($canvas, 30, 0, 90, 230, $muted, $font, mb_strtoupper($eyebrow));

        // Shrink until the headline fits in 3 lines so it never runs into the footer.
        $size = 50;
        while (count($lines = $this->wrap($headline, $font, $size, 1020)) > 3 && $size > 30) {
            $size -= 4;
        }

        $y = 310;
        foreach ($lines as $line) {
            imagettftext($canvas, $size, 0, 90, $y, $navy, $font, $line);
            $y += (int) round($size * 1.56);
        }

        imagettftext($canvas, 26, 0, 90, 570, $muted, $font, 'fabriku.id');

        imagepng($canvas, $path, 9);
    }

    /**
     * @return list<string>
     */
    private function wrap(string $text, string $font, int $size, int $maxWidth): array
    {
        $lines = [];
        $line = '';

        foreach (explode(' ', $text) as $word) {
            $candidate = trim("{$line} {$word}");
            $box = imagettfbbox($size, 0, $font, $candidate);

            if ($line !== '' && $maxWidth < $box[2] - $box[0]) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }

        return [...$lines, $line];
    }
}
