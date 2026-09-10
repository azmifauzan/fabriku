<?php

namespace App\Services\Campaign;

use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CampaignLlmService
{
    /**
     * Get LLM Base URL (OpenAI-compatible)
     */
    public function getBaseUrl(?array $override = null): string
    {
        $url = $override['llm_base_url']
            ?? SystemSetting::get('llm_base_url', null)
            ?? config('services.openai.base_url', 'https://api.openai.com/v1');

        return rtrim((string) $url, '/');
    }

    /**
     * Get LLM API Key
     */
    public function getApiKey(?array $override = null): ?string
    {
        return $override['llm_api_key']
            ?? SystemSetting::get('llm_api_key', null)
            ?? config('services.openai.api_key');
    }

    /**
     * Get LLM Model Name
     */
    public function getModel(?array $override = null): string
    {
        return $override['llm_model']
            ?? SystemSetting::get('llm_model', null)
            ?? config('services.openai.model', 'gpt-4o-mini');
    }

    /**
     * Get Temperature
     */
    public function getTemperature(?array $override = null): float
    {
        $temp = $override['llm_temperature']
            ?? SystemSetting::get('llm_temperature', 0.7);

        return (float) $temp;
    }

    /**
     * Get Max Tokens
     */
    public function getMaxTokens(?array $override = null): int
    {
        $tokens = $override['llm_max_tokens']
            ?? SystemSetting::get('llm_max_tokens', 1200);

        return (int) $tokens;
    }

    /**
     * Get System Prompt
     */
    public function getSystemPrompt(?array $override = null): string
    {
        return $override['campaign_system_prompt']
            ?? SystemSetting::get('campaign_system_prompt', null)
            ?? 'Anda adalah copywriter profesional dan konsultan operasional bisnis untuk platform SaaS "Fabriku". Tugas Anda adalah menulis konten email edukasi & tips mingguan yang ramah, solutif, memikat, dan relevan dengan industri bisnis pengguna. Gunakan Bahasa Indonesia yang luwes, profesional, dan menginspirasi.';
    }

    /**
     * Check if LLM is properly configured
     */
    public function isConfigured(?array $override = null): bool
    {
        return ! empty($this->getApiKey($override)) && ! empty($this->getBaseUrl($override));
    }

    /**
     * Test connection to the OpenAI-compatible LLM endpoint
     *
     * @param  array<string, mixed>|null  $overrideConfig
     * @return array{success: bool, message: string, latency_ms: int, model?: string, response_preview?: string}
     */
    public function testConnection(?array $overrideConfig = null): array
    {
        $baseUrl = $this->getBaseUrl($overrideConfig);
        $apiKey = $this->getApiKey($overrideConfig);
        $model = $this->getModel($overrideConfig);

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key LLM belum dikonfigurasi.',
                'latency_ms' => 0,
            ];
        }

        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(20)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Balas dengan satu kalimat singkat dalam bahasa Indonesia: Konfirmasi bahwa koneksi LLM OpenAI-compatible untuk Fabriku berhasil terhubung.',
                        ],
                    ],
                    'max_tokens' => 60,
                    'temperature' => 0.3,
                ]);

            $latency = (int) round((microtime(true) - $startTime) * 1000);

            if (! $response->successful()) {
                $errorBody = $response->json('error.message') ?? $response->body();

                return [
                    'success' => false,
                    'message' => "HTTP {$response->status()}: " . substr($errorBody, 0, 300),
                    'latency_ms' => $latency,
                    'model' => $model,
                ];
            }

            $content = $response->json('choices.0.message.content') ?? 'Koneksi berhasil terhubung.';

            return [
                'success' => true,
                'message' => 'Koneksi ke LLM berhasil!',
                'latency_ms' => $latency,
                'model' => $model,
                'response_preview' => trim($content),
            ];
        } catch (\Throwable $e) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
                'latency_ms' => $latency,
                'model' => $model,
            ];
        }
    }

    /**
     * Generate tailored campaign email content using LLM (with robust fallback)
     *
     * @param  array<string, mixed>  $feature
     * @return array<string, mixed>
     */
    public function generateCampaignContent(Tenant $tenant, User $adminUser, array $feature): array
    {
        $categoryLabel = $tenant->getCategoryLabel();
        $businessCategory = $tenant->business_category ?? 'garment';
        $featureName = $feature['name'];
        $featureDesc = $feature['description'] ?? '';
        $featureBenefit = $feature['benefit'] ?? '';
        $painPoint = $feature['pain_point'] ?? '';

        // Prepare Prompt
        $prompt = <<<PROMPT
Bisnis Penerima:
- Nama Bisnis: "{$tenant->name}"
- Kategori Industri: "{$categoryLabel}" (kode: {$businessCategory})
- Nama Admin: "{$adminUser->name}"

Fitur Fabriku yang Dibahas:
- Nama Fitur: "{$featureName}"
- Deskripsi: "{$featureDesc}"
- Nilai Manfaat Utama: "{$featureBenefit}"
- Masalah Umum yang Dihadapi: "{$painPoint}"

Tugas:
Tulis materi email campaign mingguan dalam format JSON murni.
Format JSON yang WAJIB dihasilkan:
{
  "subject": "Subjek email yang menarik, bernada solutif untuk {$categoryLabel}, dan relevan (maksimal 65 karakter, bisa gunakan emoji yang sesuai)",
  "headline": "Judul utama email yang memikat (1 kalimat pendek)",
  "intro": "1 paragraf pembuka (2-3 kalimat) menyapa {$adminUser->name} dan mengangkat tantangan nyata yang sering dialami pelaku usaha {$categoryLabel} sebelum menggunakan fitur ini",
  "benefit_points": [
    "Poin manfaat konkret ke-1 untuk bisnis {$tenant->name}",
    "Poin manfaat konkret ke-2 untuk menghemat waktu atau biaya",
    "Poin manfaat konkret ke-3 untuk peningkatan efisiensi atau omzet"
  ],
  "step_by_step": [
    "Langkah praktis ke-1 di dashboard Fabriku",
    "Langkah praktis ke-2 di dashboard Fabriku",
    "Langkah praktis ke-3 di dashboard Fabriku"
  ],
  "pro_tip": "1 saran/tips rahasia praktis dari konsultan bisnis agar bisnis mereka makin berkembang dengan fitur ini",
  "cta_text": "Teks tombol CTA yang memotivasi (contoh: 'Coba Fitur di Dashboard', 'Optimalkan Bisnis Anda')"
}

Kembalikan HANYA JSON yang valid, tanpa teks pengantar atau markdown blocks lain di luar JSON.
PROMPT;

        $llmUsed = 'fallback';

        if ($this->isConfigured()) {
            try {
                $baseUrl = $this->getBaseUrl();
                $apiKey = $this->getApiKey();
                $model = $this->getModel();
                $temp = $this->getTemperature();
                $maxTokens = $this->getMaxTokens();
                $systemPrompt = $this->getSystemPrompt();

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])
                    ->timeout(35)
                    ->post("{$baseUrl}/chat/completions", [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => $temp,
                        'max_tokens' => $maxTokens,
                        'response_format' => ['type' => 'json_object'],
                    ]);

                if ($response->successful()) {
                    $rawContent = $response->json('choices.0.message.content');
                    // Clean possible markdown code blocks ```json ... ```
                    $cleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent ?? ''));
                    $cleaned = preg_replace('/\s*```$/', '', $cleaned);

                    $parsed = json_decode($cleaned, true);

                    if (is_array($parsed) && ! empty($parsed['subject'])) {
                        return [
                            'subject' => $parsed['subject'],
                            'headline' => $parsed['headline'] ?? "Optimalkan Bisnis {$categoryLabel} Anda",
                            'greeting' => "Halo {$adminUser->name},",
                            'intro' => $parsed['intro'] ?? $painPoint,
                            'benefit_points' => is_array($parsed['benefit_points'] ?? null) ? $parsed['benefit_points'] : [$featureBenefit],
                            'step_by_step' => is_array($parsed['step_by_step'] ?? null) ? $parsed['step_by_step'] : ($feature['steps'] ?? []),
                            'pro_tip' => $parsed['pro_tip'] ?? ($feature['pro_tip'] ?? ''),
                            'cta_text' => $parsed['cta_text'] ?? 'Buka Dashboard Fabriku →',
                            'cta_path' => $feature['cta_path'] ?? '/dashboard',
                            'feature_name' => $featureName,
                            'category_label' => $categoryLabel,
                            'llm_used' => $model,
                            'prompt_used' => $prompt,
                        ];
                    }
                } else {
                    Log::warning('Campaign LLM returned non-200', [
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 300),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Campaign LLM execution exception', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Return curated fallback content if LLM is unavailable or fails
        return $this->buildFallbackContent($tenant, $adminUser, $feature, $prompt);
    }

    /**
     * High-quality pre-crafted fallback content
     */
    protected function buildFallbackContent(Tenant $tenant, User $adminUser, array $feature, string $prompt): array
    {
        $categoryLabel = $tenant->getCategoryLabel();
        $featureName = $feature['name'];

        return [
            'subject' => "💡 Tips {$categoryLabel}: Optimasi {$featureName} di Fabriku",
            'headline' => "Maksimalkan Operasional {$tenant->name} dengan {$featureName}",
            'greeting' => "Halo {$adminUser->name},",
            'intro' => $feature['pain_point'] ?? "Kelola operasional bisnis {$categoryLabel} Anda dengan lebih rapi, cepat, dan tanpa over-budget bersama Fabriku.",
            'benefit_points' => [
                $feature['benefit'] ?? "Meningkatkan efisiensi kerja dan mengurangi kesalahan pencatatan manual.",
                "Pencatatan data bisnis terpusat, aman, dan dapat diakses dari mana saja.",
                "Keputusan bisnis lebih terarah berkat laporan otomatis dan real-time.",
            ],
            'step_by_step' => $feature['steps'] ?? [
                'Login ke akun Fabriku Anda',
                'Buka menu terkait di sidebar dashboard',
                'Mulai rasakan kemudahan pengelolaan bisnis Anda',
            ],
            'pro_tip' => $feature['pro_tip'] ?? 'Konsistensi pencatatan setiap hari adalah kunci agar data analitik bisnis Anda semakin tajam dan akurat.',
            'cta_text' => 'Buka Dashboard Sekarang →',
            'cta_path' => $feature['cta_path'] ?? '/dashboard',
            'feature_name' => $featureName,
            'category_label' => $categoryLabel,
            'llm_used' => 'Template Bawaan (Fallback)',
            'prompt_used' => $prompt,
        ];
    }
}
