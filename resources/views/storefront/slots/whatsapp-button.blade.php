@php
    $phone = $site->profile['whatsapp'] ?? $site->tenant->phone ?? '';
    // Normalize to 62...
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($cleanPhone, '0')) {
        $cleanPhone = '62' . substr($cleanPhone, 1);
    }
    $siteName = $site->profile['name'] ?? $site->tenant->name ?? 'Toko';
    $waUrl = $cleanPhone ? "https://wa.me/{$cleanPhone}?text=" . urlencode("Halo {$siteName}, saya ingin bertanya tentang produk/layanan Anda.") : '#';
@endphp

<a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[var(--fb-radius)] bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm transition shadow-sm">
    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.698.055-1.131-.082-.279-.088-.636-.217-1.096-.416-1.954-.844-3.228-2.825-3.326-2.955-.098-.13-1.042-1.385-1.042-2.641 0-1.256.657-1.874.89-2.12.234-.247.512-.309.683-.309.171 0 .343.002.493.009.158.008.371-.06.58.441.217.519.742 1.808.808 1.94.066.133.11.288.022.463-.088.176-.133.287-.263.441-.13.155-.274.346-.391.464-.13.13-.266.272-.115.531.152.259.676 1.112 1.452 1.802.999.889 1.841 1.165 2.1 1.294.26.13.412.115.565-.06.153-.175.657-.765.832-1.028.175-.262.35-.219.589-.13.24.088 1.528.72 1.79.851.262.13.437.195.502.306.066.111.066.645-.078 1.05z"/>
    </svg>
    <span>Hubungi via WhatsApp</span>
</a>
