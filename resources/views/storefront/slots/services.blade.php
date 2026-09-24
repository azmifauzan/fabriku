@php
    $serviceList = $services ?? [];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($serviceList as $service)
        @php
            $img = $service->getImageUrl();
            $priceText = $service->getPriceDisplay();
        @endphp
        <div class="bg-[var(--fb-surface)] border border-slate-200/80 rounded-[var(--fb-radius)] overflow-hidden flex flex-col p-6 hover:shadow-md transition">
            @if($img)
                <div class="aspect-video w-full rounded-[calc(var(--fb-radius)-2px)] overflow-hidden mb-4 bg-slate-100">
                    <img src="{{ $img }}" alt="{{ $service->name }}" class="w-full h-full object-cover">
                </div>
            @endif
            <h3 class="text-lg font-bold text-[var(--fb-text)] mb-2">{{ $service->name }}</h3>
            @if($service->public_description)
                <p class="text-sm text-slate-600 line-clamp-3 mb-4">{{ $service->public_description }}</p>
            @endif
            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 block">Biaya Estimasi</span>
                    <span class="text-base font-bold text-[var(--fb-primary)]">{{ $priceText }}</span>
                </div>
                <a href="#lead-form" class="text-xs font-semibold px-4 py-2 rounded-[var(--fb-radius)] bg-[var(--fb-primary)] text-white hover:opacity-90 transition">
                    Minta Penawaran
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full py-12 text-center text-slate-500">
            Belum ada layanan yang ditampilkan.
        </div>
    @endforelse
</div>
