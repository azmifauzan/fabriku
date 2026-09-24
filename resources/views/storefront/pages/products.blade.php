<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-[var(--fb-text)] mb-2">Semua Produk</h1>
        <p class="text-sm text-slate-500">Pilihan produk berkualitas yang siap dipesan.</p>
    </div>

    @include('storefront.slots.products', ['products' => $products])
</div>
