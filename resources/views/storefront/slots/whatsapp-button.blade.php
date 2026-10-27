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

@if($cleanPhone)
<a href="{{ $waUrl }}" aria-label="Hubungi {{ $siteName }} via WhatsApp" title="WhatsApp admin" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[var(--fb-radius)] bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm transition shadow-sm">
    @include('storefront.icons.whatsapp')
    <span>Hubungi via WhatsApp</span>
</a>
@endif
