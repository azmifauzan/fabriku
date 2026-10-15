<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        title: string;
        description?: string | null;
        canonical?: string | null;
        ogImage?: string | null;
        ogType?: string;
        noindex?: boolean;
        jsonLd?: Record<string, unknown> | Record<string, unknown>[] | null;
    }>(),
    {
        description: null,
        canonical: null,
        ogImage: null,
        ogType: 'website',
        noindex: false,
        jsonLd: null,
    },
);

// Threads/FB/WA skip previews without a big-enough og:image, so every page with a
// canonical gets one. A relative ogImage is one of our own 1200x630 PNGs in
// public/images (php artisan og:generate-images), resolved against canonical since
// SSR has no window; an absolute one (e.g. a blog featured image) has unknown size.
const ogImagePath = computed(() => props.ogImage ?? '/images/og-default.png');
const image = computed(() => (props.canonical ? new URL(ogImagePath.value, props.canonical).toString() : null));
const isOwnImage = computed(() => ogImagePath.value.startsWith('/'));

// Escape "</" so a value containing it can't break out of the <script> tag.
const schemas = computed(() => {
    if (!props.jsonLd) return [];
    const list = Array.isArray(props.jsonLd) ? props.jsonLd : [props.jsonLd];
    return list.map((schema) => JSON.stringify(schema).replace(/</g, '\\u003c'));
});

// GA4 Enhanced Measurement's history-change detection handles page_view on
// Inertia's client-side navigations between public pages, so gtag only needs
// initializing once (head-key keeps it from re-running on every navigation).
const gaMeasurementId = import.meta.env.VITE_GA_MEASUREMENT_ID as string | undefined;
const gaInitScript = gaMeasurementId
    ? `window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','${gaMeasurementId}');`
    : '';
</script>

<template>
    <Head :title="title">
        <meta v-if="description" head-key="description" name="description" :content="description" />
        <meta v-if="noindex" head-key="robots" name="robots" content="noindex,follow" />
        <link v-if="canonical" head-key="canonical" rel="canonical" :href="canonical" />

        <meta head-key="og:type" property="og:type" :content="ogType" />
        <meta head-key="og:title" property="og:title" :content="title" />
        <meta v-if="description" head-key="og:description" property="og:description" :content="description" />
        <meta v-if="canonical" head-key="og:url" property="og:url" :content="canonical" />
        <meta head-key="og:site_name" property="og:site_name" content="Fabriku" />
        <meta head-key="og:locale" property="og:locale" content="id_ID" />
        <meta v-if="image" head-key="og:image" property="og:image" :content="image" />
        <meta v-if="image && isOwnImage" head-key="og:image:type" property="og:image:type" content="image/png" />
        <meta v-if="image && isOwnImage" head-key="og:image:width" property="og:image:width" content="1200" />
        <meta v-if="image && isOwnImage" head-key="og:image:height" property="og:image:height" content="630" />
        <meta v-if="image" head-key="og:image:alt" property="og:image:alt" :content="title" />
        <meta v-if="image" head-key="twitter:image" name="twitter:image" :content="image" />
        <meta v-if="image" head-key="twitter:image:alt" name="twitter:image:alt" :content="title" />
        <meta head-key="twitter:card" name="twitter:card" :content="image ? 'summary_large_image' : 'summary'" />

        <component :is="'script'" v-for="(schema, index) in schemas" :key="index" type="application/ld+json">{{ schema }}</component>

        <component v-if="gaMeasurementId" :is="'script'" head-key="ga4-src" async :src="`https://www.googletagmanager.com/gtag/js?id=${gaMeasurementId}`" />
        <component v-if="gaMeasurementId" :is="'script'" head-key="ga4-init">{{ gaInitScript }}</component>
    </Head>
</template>
