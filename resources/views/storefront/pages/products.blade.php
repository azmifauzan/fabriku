@php
    $siteName = $site->profile['name'] ?? $site->tenant->name ?? 'Toko';
    $siteIntro = $site->seo['description'] ?? $site->profile['description'] ?? null;
    $contentPages = $contentPages ?? collect();
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 max-w-3xl">
        <h1 class="text-3xl font-bold tracking-tight text-[var(--fb-text)] mb-3">Katalog Produk {{ $siteName }}</h1>
        @if($siteIntro)
            <p class="text-base text-slate-600 leading-relaxed mb-2">{{ $siteIntro }}</p>
        @endif
        <p class="text-sm text-slate-500">
            @if($products->isNotEmpty())
                Tersedia {{ $products->count() }} pilihan produk dengan informasi harga dan status ketersediaan di bawah ini. Pilih produk untuk melihat rincian ukuran, cara pemesanan, atau menambahkan langsung ke keranjang.
            @else
                Pilihan produk yang siap dipesan akan tampil di halaman katalog ini.
            @endif
        </p>
    </div>

    @include('storefront.slots.products', ['products' => $products])

    @if($contentPages->isNotEmpty())
        <section class="mt-12 pt-8 border-t border-slate-200 max-w-4xl">
            <h2 class="text-xl font-semibold text-[var(--fb-text)] mb-3">Panduan dan kategori di {{ $siteName }}</h2>
            <p class="text-sm text-slate-600 mb-4">Pelajari pilihan menu per kategori, cara pemesanan, dan informasi pengiriman pada halaman berikut.</p>
            <nav aria-label="Halaman terkait katalog">
                <ul class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
                    @foreach($contentPages as $page)
                        <li><a class="underline text-[var(--fb-primary)]" href="/halaman/{{ $page->slug }}">{{ $page->title }}</a></li>
                    @endforeach
                </ul>
            </nav>
        </section>
    @endif
</div>
