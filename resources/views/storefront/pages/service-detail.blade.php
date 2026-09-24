@php
    $img = $service->getImageUrl();
    $priceText = $service->getPriceDisplay();
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-6">
        <a href="/layanan" class="text-sm text-slate-500 hover:text-[var(--fb-primary)] transition flex items-center gap-1">
            &larr; Kembali ke Daftar Layanan
        </a>
    </div>

    <div class="bg-[var(--fb-surface)] border border-slate-200/80 rounded-[var(--fb-radius)] p-6 md:p-8 mb-12">
        @if($img)
            <div class="aspect-video w-full rounded-[calc(var(--fb-radius)-2px)] overflow-hidden mb-6 bg-slate-100">
                <img src="{{ $img }}" alt="{{ $service->name }}" class="w-full h-full object-cover">
            </div>
        @endif

        <h1 class="text-2xl md:text-3xl font-extrabold text-[var(--fb-text)] mb-3">
            {{ $service->name }}
        </h1>

        <div class="inline-block px-3 py-1 rounded-[var(--fb-radius)] bg-[var(--fb-primary)] text-white text-sm font-semibold mb-6">
            {{ $priceText }}
        </div>

        @if($service->public_description)
            <div class="prose prose-sm text-slate-600 leading-relaxed">
                <p>{{ $service->public_description }}</p>
            </div>
        @endif
    </div>

    @include('storefront.slots.lead-form', ['services' => [$service]])
</div>
