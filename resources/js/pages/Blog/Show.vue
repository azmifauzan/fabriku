<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    post: {
        title: string;
        content_html: string;
        excerpt: string | null;
        featured_image_url: string | null;
        published_at: string | null;
        updated_at: string | null;
        meta_title: string;
        meta_description: string | null;
        category: { name: string; slug: string } | null;
        tags: Array<{ name: string; slug: string }>;
        author_name: string;
        canonical: string;
    };
}>();

const ogImage = computed(() => props.post.featured_image_url ?? new URL('/images/fabriku-word.png', props.post.canonical).toString());

const jsonLd = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: props.post.title,
        description: props.post.meta_description ?? props.post.excerpt ?? undefined,
        image: ogImage.value,
        datePublished: props.post.published_at ?? undefined,
        dateModified: props.post.updated_at ?? props.post.published_at ?? undefined,
        author: { '@type': 'Person', name: props.post.author_name },
        publisher: {
            '@type': 'Organization',
            name: 'Fabriku',
            logo: { '@type': 'ImageObject', url: new URL('/images/fabriku-logo-only.png', props.post.canonical).toString() },
        },
        mainEntityOfPage: { '@type': 'WebPage', '@id': props.post.canonical },
    },
    {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Beranda', item: new URL('/', props.post.canonical).toString() },
            { '@type': 'ListItem', position: 2, name: 'Blog', item: new URL('/blog', props.post.canonical).toString() },
            { '@type': 'ListItem', position: 3, name: props.post.title, item: props.post.canonical },
        ],
    },
]);
</script>

<template>
    <SeoHead
        :title="post.meta_title"
        :description="post.meta_description"
        :canonical="post.canonical"
        :og-image="ogImage"
        og-type="article"
        :json-ld="jsonLd"
    />
    <PublicLayout>
        <article class="mx-auto max-w-3xl px-4 py-12">
            <p v-if="post.category" class="mb-2 text-sm font-medium text-indigo-600">{{ post.category.name }}</p>
            <h1 class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">{{ post.title }}</h1>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                Oleh {{ post.author_name }}
                <template v-if="post.published_at"> &middot; {{ new Date(post.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</template>
            </p>
            <img v-if="post.featured_image_url" :src="post.featured_image_url" class="mb-6 w-full rounded-lg object-cover" />
            <div class="prose max-w-none dark:prose-invert" v-html="post.content_html" />
            <div v-if="post.tags.length" class="mt-8 flex flex-wrap gap-2">
                <Link
                    v-for="tag in post.tags"
                    :key="tag.slug"
                    :href="`/blog?tag=${tag.slug}`"
                    class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                >
                    #{{ tag.name }}
                </Link>
            </div>
        </article>
    </PublicLayout>
</template>
