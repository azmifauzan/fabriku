<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    post: {
        title: string;
        content_html: string;
        featured_image_url: string | null;
        published_at: string | null;
        meta_title: string;
        meta_description: string | null;
        category: { name: string; slug: string } | null;
        tags: Array<{ name: string; slug: string }>;
        author_name: string;
    };
}>();
</script>

<template>
    <Head :title="post.meta_title">
        <meta v-if="post.meta_description" name="description" :content="post.meta_description" />
    </Head>
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
