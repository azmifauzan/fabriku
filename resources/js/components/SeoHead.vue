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
        <meta v-if="ogImage" head-key="og:image" property="og:image" :content="ogImage" />
        <meta head-key="twitter:card" name="twitter:card" :content="ogImage ? 'summary_large_image' : 'summary'" />

        <component :is="'script'" v-for="(schema, index) in schemas" :key="index" type="application/ld+json">{{ schema }}</component>

        <component v-if="gaMeasurementId" :is="'script'" head-key="ga4-src" async :src="`https://www.googletagmanager.com/gtag/js?id=${gaMeasurementId}`" />
        <component v-if="gaMeasurementId" :is="'script'" head-key="ga4-init">{{ gaInitScript }}</component>
    </Head>
</template>
