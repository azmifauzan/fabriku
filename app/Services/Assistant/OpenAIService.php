<?php

namespace App\Services\Assistant;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private ?string $apiKey;

    private string $model;

    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-5-nano');
    }

    /**
     * Check if OpenAI is configured
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Send a chat completion request to OpenAI
     */
    public function chat(array $messages, array $tools = [], ?array $options = []): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'OpenAI API key not configured',
                'content' => 'Maaf, layanan AI belum dikonfigurasi. Silakan hubungi administrator.',
            ];
        }

        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1000,
        ];

        if (! empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = $options['tool_choice'] ?? 'auto';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post("{$this->baseUrl}/chat/completions", $payload);

            if (! $response->successful()) {
                Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'API request failed',
                    'content' => 'Maaf, terjadi kesalahan saat memproses permintaan. Silakan coba lagi.',
                ];
            }

            $data = $response->json();
            $choice = $data['choices'][0] ?? null;
            $usage = $data['usage'] ?? [];

            if (! $choice) {
                return [
                    'success' => false,
                    'error' => 'No response from AI',
                    'content' => 'Maaf, tidak ada respons dari AI.',
                ];
            }

            $message = $choice['message'];

            return [
                'success' => true,
                'content' => $message['content'] ?? '',
                'role' => $message['role'] ?? 'assistant',
                'tool_calls' => $message['tool_calls'] ?? null,
                'finish_reason' => $choice['finish_reason'] ?? 'stop',
                'tokens_input' => $usage['prompt_tokens'] ?? 0,
                'tokens_output' => $usage['completion_tokens'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => 'Maaf, terjadi kesalahan teknis. Silakan coba lagi.',
            ];
        }
    }

    /**
     * Get the system prompt for the assistant
     */
    public function getSystemPrompt(User $user): string
    {
        $tenant = $user->tenant;
        $businessCategory = $tenant->business_category ?? 'umkm';

        return <<<PROMPT
Kamu adalah Fabriku Assistant, asisten AI yang membantu pengguna UMKM mengelola bisnis mereka.

## Informasi Pengguna
- Nama: {$user->name}
- Role: {$user->role}
- Bisnis: {$tenant->name}
- Kategori: {$businessCategory}

## Kemampuan
1. **Informasi & Analisis**: Memberikan informasi tentang inventory, penjualan, produksi, dan data bisnis lainnya.
2. **Navigasi**: Membantu pengguna menemukan fitur yang tepat di aplikasi.
3. **Tips & Saran**: Memberikan rekomendasi untuk meningkatkan efisiensi bisnis.

## Batasan
- Kamu TIDAK dapat mengubah data langsung. Untuk operasi yang mengubah data, arahkan pengguna ke menu yang sesuai.
- Kamu TIDAK memiliki akses ke data finansial sensitif seperti nomor rekening.
- Selalu gunakan Bahasa Indonesia yang sopan dan mudah dipahami.

## Format Respons
- Gunakan format yang mudah dibaca (bullet points, numbered lists jika diperlukan)
- Untuk angka uang, gunakan format Rupiah: Rp 1.500.000
- Jika tidak yakin, akui ketidaktahuanmu dan sarankan alternatif

## Contoh Interaksi
User: "Berapa total penjualan bulan ini?"
Assistant: "Total penjualan bulan ini adalah Rp 15.500.000 dari 23 pesanan. Ini meningkat 12% dibanding bulan lalu. 📈"

Selalu ramah, profesional, dan fokus membantu pengguna mencapai tujuan mereka.
PROMPT;
    }
}
