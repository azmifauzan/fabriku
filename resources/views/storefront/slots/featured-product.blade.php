@php
    $count = $slides->count();
    $step = 4; // seconds each photo stays on screen
    $share = round(100 / max($count, 1), 3);
    $fade = 2.5;
@endphp
{{-- CSS-only slideshow: the storefront CSP forbids scripts. Keyframes depend on the slide count, so they are emitted here. --}}
<style>
.fb-slides{position:relative;overflow:hidden;background:var(--fb-surface);border-radius:var(--fb-radius);aspect-ratio:{{ $count > 1 ? '1 / 1' : '4 / 3' }}}
.fb-slides .fb-slide{position:absolute;inset:0;display:block;color:#fff;text-decoration:none}
.fb-slides .fb-slide img{position:absolute;inset:0;display:block;width:100%;height:100%;aspect-ratio:auto;object-fit:cover}
.fb-slides .fb-cap{position:absolute;left:0;right:0;bottom:0;display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:.25rem 1rem;padding:2.5rem 1rem 1.75rem;background:linear-gradient(to top,rgba(0,0,0,.78),rgba(0,0,0,0))}
.fb-slides .fb-cap-name{font-weight:600}
@if($count > 1)
.fb-slides .fb-slide{opacity:0;animation:fb-hero-{{ $count }} {{ $count * $step }}s linear infinite;animation-delay:calc(var(--i) * {{ $step }}s)}
.fb-slides .fb-dots{position:absolute;left:0;right:0;bottom:.6rem;display:flex;justify-content:center;gap:.4rem}
.fb-slides .fb-dots i{display:block;width:.5rem;height:.5rem;border-radius:50%;background:#fff;opacity:.4;animation:fb-dot-{{ $count }} {{ $count * $step }}s linear infinite;animation-delay:calc(var(--i) * {{ $step }}s)}
.fb-slides:hover .fb-slide,.fb-slides:focus-within .fb-slide,.fb-slides:hover .fb-dots i,.fb-slides:focus-within .fb-dots i{animation-play-state:paused}
@keyframes fb-hero-{{ $count }}{0%{opacity:0}{{ $fade }}%{opacity:1}{{ $share }}%{opacity:1}{{ $share + $fade }}%{opacity:0}100%{opacity:0}}
@keyframes fb-dot-{{ $count }}{0%{opacity:1}{{ $share }}%{opacity:1}{{ $share + 0.01 }}%{opacity:.4}100%{opacity:.4}}
@media (prefers-reduced-motion:reduce){.fb-slides .fb-slide{animation:none}.fb-slides .fb-slide:first-child{opacity:1}.fb-slides .fb-dots{display:none}}
@endif
</style>
<div class="fb-slides">
    @foreach($slides as $slide)
        @php
            $product = $slide['product'];
            $price = $product->getStartingPrice();
        @endphp
        <a class="fb-slide" href="/produk/{{ $product->slug }}" style="--i:{{ $loop->index }}">
            <img src="{{ $slide['image'] }}" alt="{{ $product->title }}" width="1200" height="1200" @if(! $loop->first) loading="lazy" @endif decoding="async">
            <span class="fb-cap">
                <span class="fb-cap-name">{{ $product->title }}</span>
                @if($price > 0)
                    <span>{{ $product->hasPriceVariation() ? 'Mulai ' : '' }}Rp {{ number_format($price, 0, ',', '.') }}</span>
                @endif
            </span>
        </a>
    @endforeach
    @if($count > 1)
        <span class="fb-dots" aria-hidden="true">@foreach($slides as $slide)<i style="--i:{{ $loop->index }}"></i>@endforeach</span>
    @endif
</div>
