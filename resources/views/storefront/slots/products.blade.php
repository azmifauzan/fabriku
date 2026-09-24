@php
    $productList = $products ?? [];
@endphp

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @forelse($productList as $product)
        @php
            $price = $product->getStartingPrice();
            $hasVariation = $product->hasPriceVariation();
            $status = $product->getAvailabilityStatus();
            $statusClass = match($status) {
                'Tersedia' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'Stok terbatas' => 'bg-amber-50 text-amber-700 border-amber-200',
                default => 'bg-rose-50 text-rose-700 border-rose-200',
            };
            $img = $product->getImageUrl() ?: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect width="200" height="200" fill="%23f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%2394a3b8" font-family="sans-serif">Produk</text></svg>';
        @endphp
        <div class="bg-[var(--fb-surface)] border border-slate-200/80 rounded-[var(--fb-radius)] overflow-hidden flex flex-col group hover:shadow-md transition">
            <a href="/produk/{{ $product->slug }}" class="block aspect-square w-full overflow-hidden bg-slate-100">
                <img src="{{ $img }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
            </a>
            <div class="p-4 flex flex-col flex-1">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $statusClass }}">
                        {{ $status }}
                    </span>
                </div>
                <a href="/produk/{{ $product->slug }}" class="font-medium text-[var(--fb-text)] hover:text-[var(--fb-primary)] line-clamp-2 transition mb-2">
                    {{ $product->title }}
                </a>
                <div class="mt-auto pt-2 flex items-baseline justify-between">
                    <div>
                        @if($hasVariation)
                            <span class="text-xs text-slate-500 block">Mulai</span>
                        @endif
                        <span class="text-base font-bold text-[var(--fb-primary)]">
                            Rp {{ number_format($price, 0, ',', '.') }}
                        </span>
                    </div>
                    <a href="/produk/{{ $product->slug }}" class="text-xs font-semibold px-3 py-1.5 rounded-[var(--fb-radius)] bg-[var(--fb-primary)] text-white hover:opacity-90 transition">
                        Lihat
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full py-12 text-center text-slate-500">
            Belum ada produk yang ditampilkan.
        </div>
    @endforelse
</div>
