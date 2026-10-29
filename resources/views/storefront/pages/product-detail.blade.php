@php
    $price = $product->getStartingPrice();
    $hasVariation = $product->hasPriceVariation();
    $status = $product->getAvailabilityStatus();
    $isOpenOrder = $status === 'Open Order';
    $leadTime = $isOpenOrder ? $product->getLeadTimeDays() : null;
    $statusClass = match($status) {
        'Tersedia' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Stok terbatas' => 'bg-amber-50 text-amber-700 border-amber-200',
        'Open Order' => 'bg-sky-50 text-sky-700 border-sky-200',
        default => 'bg-rose-50 text-rose-700 border-rose-200',
    };
    $img = $product->getImageUrl() ?: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400"><rect width="400" height="400" fill="%23f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%2394a3b8" font-family="sans-serif">Produk</text></svg>';
    $phone = $site->profile['whatsapp'] ?? $site->tenant->phone ?? '';
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($cleanPhone, '0')) {
        $cleanPhone = '62' . substr($cleanPhone, 1);
    }
    $waUrl = $cleanPhone ? "https://wa.me/{$cleanPhone}?text=" . urlencode("Halo, saya tertarik dengan produk {$product->title} (Rp " . number_format($price, 0, ',', '.') . "). " . ($isOpenOrder ? "Saya mau pesan open order, kapan bisa dibuat?" : "Apakah masih ada?")) : '#';
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
                    {{ $status }}@if($leadTime) · estimasi {{ $leadTime }} hari @endif
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

            @if($isOpenOrder)
                <p class="mb-6 text-sm text-slate-600">
                    Produk ini dibuat setelah pesanan masuk{{ $leadTime ? ', estimasi pengerjaan '.$leadTime.' hari' : '' }}. Tim toko akan mengonfirmasi tanggal siap sebelum pembayaran.
                </p>
            @endif

            @if($product->description)
                <div class="prose prose-sm text-slate-600 mb-8 leading-relaxed">
                    <p>{{ $product->description }}</p>
                </div>
            @endif

            <div class="storefront-purchase-actions mt-auto pt-6 border-t border-slate-100 flex flex-col gap-3">
                @if($status !== 'Habis' && $price > 0 && $site->canAcceptOrders())
                    <form method="POST" action="/keranjang" class="flex-1 flex gap-2">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <label for="product-quantity" class="sr-only">Jumlah</label>
                        <input id="product-quantity" name="quantity" type="number" min="1" max="50" value="1" class="w-16 rounded-lg border border-slate-400 px-2 text-center">
                        <button type="submit" class="min-h-12 flex-1 inline-flex items-center justify-center gap-2 py-3 px-4 rounded-[var(--fb-radius)] bg-[var(--fb-primary)] text-white font-semibold text-sm focus-visible:outline-2 focus-visible:outline-offset-2">
                            <svg width="20" height="20" aria-hidden="true" class="w-5 h-5 shrink-0 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 7H6"/><circle cx="10" cy="21" r="1"/><circle cx="19" cy="21" r="1"/></svg>
                            <span>Tambah ke keranjang</span>
                        </button>
                    </form>
                @endif
                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="min-h-12 flex-1 py-3 px-6 rounded-[var(--fb-radius)] bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-center text-sm transition shadow-sm flex items-center justify-center gap-2">
                    @include('storefront.icons.whatsapp')
                    <span>Pesan via WhatsApp</span>
                </a>
            </div>
        </div>
    </div>
</div>
