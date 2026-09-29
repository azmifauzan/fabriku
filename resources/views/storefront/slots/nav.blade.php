@php
    $mode = $site->mode ?? 'produk';
@endphp

<nav aria-label="Navigasi toko" class="flex items-center gap-5 whitespace-nowrap text-sm font-medium text-[var(--fb-text)]">
    <a href="/" class="hover:text-[var(--fb-primary)] transition">Beranda</a>
    @if($mode === 'produk' || $mode === 'gabungan')
        <a href="/produk" class="hover:text-[var(--fb-primary)] transition">Produk</a>
    @endif
    @if($mode === 'jasa' || $mode === 'gabungan')
        <a href="/layanan" class="hover:text-[var(--fb-primary)] transition">Layanan</a>
    @endif
    <a href="/#contact" class="hover:text-[var(--fb-primary)] transition">Kontak</a>
</nav>
