@php
    $siteName = $site->profile['name'] ?? $site->tenant->name ?? 'Toko';
    $logoUrl = $site->profile['logo_url'] ?? $site->tenant->logo_url ?? null;
@endphp

<a href="/" class="flex items-center gap-3 decoration-transparent">
    @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="h-9 w-auto max-w-[160px] object-contain">
    @else
        <span class="text-xl font-bold tracking-tight text-[var(--fb-primary)]">{{ $siteName }}</span>
    @endif
</a>
