<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    posts: {
        data: Array<{
            slug: string;
            title: string;
            excerpt: string | null;
            featured_image_url: string | null;
            published_at: string | null;
            category: { name: string; slug: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    categories: Array<{ name: string; slug: string }>;
    activeCategory: string | null;
}>();
</script>

<template>
    <Head title="Blog" />
    <PublicLayout>
        <div class="mx-auto max-w-5xl px-4 py-12">
            <h1 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">Blog</h1>

            <div class="mb-8 flex flex-wrap gap-2">
                <Link
                    href="/blog"
                    class="rounded-full px-3 py-1 text-sm"
                    :class="!activeCategory ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                >
                    Semua
                </Link>
                <Link
                    v-for="category in categories"
                    :key="category.slug"
                    :href="`/blog?category=${category.slug}`"
                    class="rounded-full px-3 py-1 text-sm"
                    :class="activeCategory === category.slug ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                >
                    {{ category.name }}
                </Link>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="post in posts.data"
                    :key="post.slug"
                    :href="`/blog/${post.slug}`"
                    class="block overflow-hidden rounded-lg border border-gray-200 hover:shadow-md dark:border-gray-700"
                >
                    <img
                        v-if="post.featured_image_url"
                        :src="post.featured_image_url"
                        class="h-40 w-full object-cover"
                        :alt="post.title"
                    />
                    <div class="p-4">
                        <p v-if="post.category" class="mb-1 text-xs font-medium text-indigo-600">{{ post.category.name }}</p>
                        <h2 class="mb-1 font-semibold text-gray-900 dark:text-white">{{ post.title }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ post.excerpt }}</p>
                    </div>
                </Link>
            </div>

            <div class="mt-8 flex flex-wrap gap-2">
                <Link
                    v-for="link in posts.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    class="rounded-md px-3 py-1 text-sm"
                    :class="link.active ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                >
                    <span v-html="link.label" />
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
