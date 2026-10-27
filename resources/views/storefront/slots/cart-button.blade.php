@php
    $cartCount = array_sum(session('storefront_cart.'.$site->tenant_id, []));
@endphp
<a href="/keranjang" aria-label="Keranjang belanja, {{ $cartCount }} barang" class="relative inline-flex items-center justify-center min-w-11 min-h-11 p-2 rounded-[var(--fb-radius)] text-[var(--fb-text)] hover:text-[var(--fb-primary)] hover:bg-slate-100 transition" title="Keranjang belanja">
    <svg width="24" height="24" aria-hidden="true" class="w-6 h-6 shrink-0 stroke-current fill-none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="9" cy="21" r="1"></circle>
        <circle cx="20" cy="21" r="1"></circle>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
    </svg>
    @if($cartCount > 0)
        <span id="storefront-cart-count" aria-hidden="true" class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-[var(--fb-accent)] text-white text-[10px] font-bold inline-flex items-center justify-center">{{ $cartCount }}</span>
    @endif
</a>
