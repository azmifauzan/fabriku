@php
    $address = $site->profile['address'] ?? $site->tenant->address ?? '';
    $area = $site->profile['service_area'] ?? '';
    $hours = $site->profile['opening_hours'] ?? '';
@endphp

<div class="space-y-3 text-sm text-slate-600">
    @if($address)
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-[var(--fb-primary)] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <div>
                <p class="font-medium text-[var(--fb-text)]">{{ $address }}</p>
                <a href="https://maps.google.com/?q={{ urlencode($address) }}" target="_blank" rel="noopener noreferrer" class="text-xs text-[var(--fb-primary)] hover:underline inline-block mt-1">
                    Buka di Google Maps &rarr;
                </a>
            </div>
        </div>
    @endif

    @if($area)
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-[var(--fb-primary)] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
            </svg>
            <p>Area Layanan: <span class="font-medium text-[var(--fb-text)]">{{ $area }}</span></p>
        </div>
    @endif

    @if($hours)
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-[var(--fb-primary)] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>Jam Buka: <span class="font-medium text-[var(--fb-text)]">{{ $hours }}</span></p>
        </div>
    @endif
</div>
