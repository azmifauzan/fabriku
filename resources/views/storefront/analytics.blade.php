@php
    $measurementId = $site->googleAnalyticsMeasurementId();
    $nonce = request()->attributes->get('storefront_analytics_nonce');
    $event = request()->session()->pull('storefront_analytics.'.$site->id);
@endphp
<script nonce="{{ $nonce }}" async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
<script nonce="{{ $nonce }}">
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', @js($measurementId), {allow_google_signals: false, allow_ad_personalization_signals: false});
    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[href]');
        if (!link) return;
        const url = new URL(link.href, window.location.href);
        if (!['wa.me', 'api.whatsapp.com', 'web.whatsapp.com'].includes(url.hostname)) return;
        gtag('event', 'whatsapp_click', {
            page_path: window.location.pathname,
            item_id: @js($product ? (string) $product->id : null),
            item_name: @js($product?->title),
            transport_type: 'beacon'
        });
    });
    @if ($event)
        gtag('event', @js($event['name']), {{ \Illuminate\Support\Js::from($event['params']) }});
    @endif
</script>
