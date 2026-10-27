@php
    $measurementId = $site->googleAnalyticsMeasurementId();
    $nonce = request()->attributes->get('storefront_analytics_nonce');
@endphp
<script nonce="{{ $nonce }}" async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
<script nonce="{{ $nonce }}">
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', @js($measurementId), {allow_google_signals: false, allow_ad_personalization_signals: false});
</script>
