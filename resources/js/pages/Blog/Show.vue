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
        <article class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14 lg:px-10">
            <Link
                href="/blog"
                class="inline-flex min-h-11 items-center rounded-sm text-sm font-bold text-indigo-800 underline decoration-indigo-300 underline-offset-4 hover:text-indigo-950 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-700"
            >
                Kembali ke blog
            </Link>

            <div class="mx-auto mt-7 max-w-3xl">
                <p v-if="post.category" class="mb-3 text-sm font-bold text-indigo-800">{{ post.category.name }}</p>
                <h1 class="text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-5xl">{{ post.title }}</h1>
                <p class="mt-5 text-sm text-slate-600">
                    Oleh {{ post.author_name }}
                    <time v-if="post.published_at" :datetime="post.published_at">
                        &middot; {{ new Date(post.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                    </time>
                </p>
                <img
                    v-if="post.featured_image_url"
                    :src="post.featured_image_url"
                    :alt="post.title"
                    class="mt-7 max-h-[32rem] w-full rounded-xl object-cover"
                    decoding="async"
                />
                <div
                    class="prose mt-8 max-w-none prose-slate prose-headings:font-bold prose-headings:tracking-tight prose-headings:text-[#163761] prose-a:text-indigo-800 prose-a:underline prose-a:underline-offset-4"
                    v-html="post.content_html"
                />
            </div>
            <div v-if="post.tags.length" class="mt-8 flex flex-wrap gap-2">
                <Link
                    v-for="tag in post.tags"
                    :key="tag.slug"
                    :href="`/blog?tag=${tag.slug}`"
                    class="inline-flex min-h-11 items-center rounded-full border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                >
                    #{{ tag.name }}
                </Link>
            </div>
        </article>
    </PublicLayout>
</template>
