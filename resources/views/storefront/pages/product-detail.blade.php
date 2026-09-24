@php
    $price = $product->getStartingPrice();
    $hasVariation = $product->hasPriceVariation();
    $status = $product->getAvailabilityStatus();
    $statusClass = match($status) {
        'Tersedia' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Stok terbatas' => 'bg-amber-50 text-amber-700 border-amber-200',
        default => 'bg-rose-50 text-rose-700 border-rose-200',
    };
    $img = $product->getImageUrl() ?: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400"><rect width="400" height="400" fill="%23f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%2394a3b8" font-family="sans-serif">Produk</text></svg>';
    $phone = $site->profile['whatsapp'] ?? $site->tenant->phone ?? '';
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($cleanPhone, '0')) {
        $cleanPhone = '62' . substr($cleanPhone, 1);
    }
    $waUrl = $cleanPhone ? "https://wa.me/{$cleanPhone}?text=" . urlencode("Halo, saya tertarik dengan produk {$product->title} (Rp " . number_format($price, 0, ',', '.') . "). Apakah masih ada?") : '#';
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-6">
        <a href="/produk" class="text-sm text-slate-500 hover:text-[var(--fb-primary)] transition flex items-center gap-1">
            &larr; Kembali ke Katalog
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-14">
        {{-- Product Image --}}
        <div class="aspect-square bg-slate-100 rounded-[var(--fb-radius)] overflow-hidden border border-slate-200">
            <img src="{{ $img }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
        </div>

        {{-- Product Information --}}
        <div class="flex flex-col">
            <div class="mb-3">
                <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full border {{ $statusClass }}">
                    {{ $status }}
                </span>
            </div>

            <h1 class="text-2xl md:text-3xl font-extrabold text-[var(--fb-text)] mb-4">
                {{ $product->title }}
            </h1>

            <div class="mb-6 p-4 rounded-[var(--fb-radius)] bg-[var(--fb-surface)] border border-slate-100">
                @if($hasVariation)
                    <span class="text-xs text-slate-500 block mb-0.5">Mulai dari</span>
                @endif
                <div class="text-3xl font-extrabold text-[var(--fb-primary)]">
                    Rp {{ number_format($price, 0, ',', '.') }}
                </div>
            </div>

            @if($product->description)
                <div class="prose prose-sm text-slate-600 mb-8 leading-relaxed">
                    <p>{{ $product->description }}</p>
                </div>
            @endif

            <div class="mt-auto pt-6 border-t border-slate-100 flex flex-col sm:flex-row gap-3">
                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="flex-1 py-3 px-6 rounded-[var(--fb-radius)] bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-center text-sm transition shadow-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.698.055-1.131-.082-.279-.088-.636-.217-1.096-.416-1.954-.844-3.228-2.825-3.326-2.955-.098-.13-1.042-1.385-1.042-2.641 0-1.256.657-1.874.89-2.12.234-.247.512-.309.683-.309.171 0 .343.002.493.009.158.008.371-.06.58.441.217.519.742 1.808.808 1.94.066.133.11.288.022.463-.088.176-.133.287-.263.441-.13.155-.274.346-.391.464-.13.13-.266.272-.115.531.152.259.676 1.112 1.452 1.802.999.889 1.841 1.165 2.1 1.294.26.13.412.115.565-.06.153-.175.657-.765.832-1.028.175-.262.35-.219.589-.13.24.088 1.528.72 1.79.851.262.13.437.195.502.306.066.111.066.645-.078 1.05z"/>
                    </svg>
                    <span>Pesan via WhatsApp</span>
                </a>
            </div>
        </div>
    </div>
</div>
