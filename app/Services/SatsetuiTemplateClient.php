<?php

namespace App\Services;

use App\Models\BusinessSite;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SatsetuiTemplateClient
{
    public function launch(User $user, BusinessSite $site): string
    {
        $baseUrl = rtrim((string) config('services.satsetui.base_url'), '/');
        $secret = (string) config('services.satsetui.integration_secret');
        if ($baseUrl === '' || $secret === '') {
            throw new RuntimeException('Integrasi Satsetui belum dikonfigurasi.');
        }

        $result = Http::acceptJson()->withToken($secret)->timeout(15)
            ->post($baseUrl.'/api/integrations/fabriku/launch', [
                'fabriku_user_id' => $user->id,
                'site_id' => $site->id,
                'email' => $user->email,
                'name' => $user->name,
            ])->throw()->json();

        $url = $result['redirect_url'] ?? null;
        if (! is_string($url) || parse_url($url, PHP_URL_HOST) !== parse_url($baseUrl, PHP_URL_HOST)) {
            throw new RuntimeException('Satsetui mengembalikan alamat login yang tidak valid.');
        }

        return $url;
    }

    public function export(string $ticket, User $user, BusinessSite $site): array
    {
        $baseUrl = rtrim((string) config('services.satsetui.base_url'), '/');
        $secret = (string) config('services.satsetui.integration_secret');
        if ($baseUrl === '' || $secret === '' || ! preg_match('/^[A-Za-z0-9]{40,128}$/', $ticket)) {
            throw new RuntimeException('Tiket ekspor tidak valid.');
        }

        return Http::acceptJson()->withToken($secret)->timeout(20)
            ->post($baseUrl.'/api/integrations/fabriku/exports/'.$ticket, [
                'fabriku_user_id' => $user->id,
                'site_id' => $site->id,
            ])->throw()->json();
    }
}
