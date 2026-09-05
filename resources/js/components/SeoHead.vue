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

        <script v-for="(schema, index) in schemas" :key="index" type="application/ld+json">{{ schema }}</script>
    </Head>
</template>
