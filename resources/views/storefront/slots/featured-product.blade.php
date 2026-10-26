@php
    $price = $featured->getStartingPrice();
@endphp

<a href="/produk/{{ $featured->slug }}" class="group block overflow-hidden rounded-[var(--fb-radius)] bg-[var(--fb-surface)]">
    <img src="{{ $featured->getImageUrl() }}" alt="{{ $featured->title }}" width="800" height="600" class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.02]">
    <span class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 p-4">
        <span class="font-semibold text-[var(--fb-text)]">{{ $featured->title }}</span>
        @if($price > 0)
            <span class="text-[var(--fb-primary)]">{{ $featured->hasPriceVariation() ? 'Mulai ' : '' }}Rp {{ number_format($price, 0, ',', '.') }}</span>
        @endif
    </span>
</a>
