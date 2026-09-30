<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudflareDomains
{
    public function create(string $hostname): array
    {
        return $this->request('post', 'custom_hostnames', [
            'hostname' => $hostname,
            'ssl' => ['method' => 'txt', 'type' => 'dv', 'settings' => ['min_tls_version' => '1.2']],
        ]);
    }

    public function details(string $id): array
    {
        return $this->request('get', 'custom_hostnames/'.rawurlencode($id));
    }

    public function isReady(array $details): bool
    {
        return ($details['status'] ?? null) === 'active' && ($details['ssl']['status'] ?? null) === 'active';
    }

    public function cnameTarget(): string
    {
        return strtolower((string) config('services.cloudflare_storefront.cname_target'));
    }

    public function dnsPointsToTarget(string $hostname): bool
    {
        $target = rtrim($this->cnameTarget(), '.');
        if ($target === '') {
            return false;
        }

        foreach (dns_get_record($hostname, DNS_CNAME) ?: [] as $record) {
            if (rtrim(strtolower($record['target'] ?? ''), '.') === $target) {
                return true;
            }
        }

        $addresses = static function (string $domain): array {
            $records = dns_get_record($domain, DNS_A | DNS_AAAA) ?: [];

            return array_merge(array_column($records, 'ip'), array_column($records, 'ipv6'));
        };

        $hostnameAddresses = $addresses($hostname);
        $targetAddresses = $addresses($target);

        return $hostnameAddresses !== [] && array_diff($hostnameAddresses, $targetAddresses) === [];
    }

    private function request(string $method, string $path, array $data = []): array
    {
        $token = config('services.cloudflare_storefront.token');
        if (! $token) {
            throw new RuntimeException('Token Cloudflare belum dikonfigurasi.');
        }
        $zoneId = $this->zoneId($token);
        $response = Http::withToken($token)->timeout(15)->{$method}(
            "https://api.cloudflare.com/client/v4/zones/{$zoneId}/{$path}", $data
        );
        if (! $response->successful() || ! $response->json('success')) {
            $message = $response->json('errors.0.message') ?? 'Cloudflare tidak dapat memproses domain.';
            throw new RuntimeException($message);
        }

        return $response->json('result') ?? [];
    }

    private function zoneId(string $token): string
    {
        if ($configured = config('services.cloudflare_storefront.zone_id')) {
            return $configured;
        }

        return Cache::remember('storefront_cloudflare_zone_id', now()->addDay(), function () use ($token) {
            $domain = config('app.storefront_domain');
            $response = Http::withToken($token)->timeout(15)->get('https://api.cloudflare.com/client/v4/zones', ['name' => $domain]);
            $zone = collect($response->json('result', []))->firstWhere('name', $domain);
            if (! $response->successful() || ! $zone || ($zone['status'] ?? null) !== 'active') {
                throw new RuntimeException('Zone storefront belum aktif atau tidak tersedia untuk token Cloudflare.');
            }

            return $zone['id'];
        });
    }
}
