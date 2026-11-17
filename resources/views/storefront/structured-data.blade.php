@php
    $baseUrl = rtrim($site->getStorefrontUrl(), '/');
    $organization = [
        '@type' => 'Organization',
        '@id' => $baseUrl.'/#organization',
        'name' => $site->profile['name'] ?? $site->tenant->name,
        'url' => $baseUrl.'/',
    ];
    if ($logo = ($site->profile['logo_url'] ?? null)) {
        $organization['logo'] = $logo;
    }
    $graph = [$organization];
    $product = $context['product'] ?? null;
    if ($product) {
        $item = [
            '@type' => 'Product',
            '@id' => $canonical.'#product',
            'url' => $canonical,
            'name' => $product->title,
            'description' => trim(strip_tags($product->description ?? '')),
        ];
        if ($image = $product->getImageUrl()) {
            $item['image'] = [$image];
        }
        $price = $product->getStartingPrice();
        if ($price > 0) {
            $item['offers'] = [
                '@type' => 'Offer',
                'url' => $canonical,
                'priceCurrency' => 'IDR',
                'price' => number_format($price, 2, '.', ''),
                'availability' => 'https://schema.org/'.($site->canAcceptOrders() && $product->isAvailable() ? ($product->isOpenOrder() ? 'PreOrder' : 'InStock') : 'OutOfStock'),
                'seller' => ['@id' => $organization['@id']],
            ];
        }
        $graph[] = $item;
    }
    if (($context['page'] ?? 'home') !== 'home') {
        $crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => $baseUrl.'/']];
        if ($product) {
            $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => 'Katalog', 'item' => $baseUrl.'/produk'];
        }
        $crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $product?->title ?? $context['title'], 'item' => $canonical];
        $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $crumbs];
    }
    $json = json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
@endphp
<script type="application/ld+json">{!! $json !!}</script>
