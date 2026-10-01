<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';

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
    canonical: string;
    noindex: boolean;
}>();
</script>

<template>
    <SeoHead
        title="Blog Fabriku | Panduan Produksi dan Stok UMKM"
        description="Panduan praktis untuk mengelola bahan baku, produksi, stok, dan penjualan usaha."
        :canonical="canonical"
        :noindex="noindex"
    />
    <PublicLayout>
        <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16 lg:px-10">
            <header class="grid gap-6 border-b border-slate-300 pb-8 lg:grid-cols-[1fr_0.7fr] lg:items-end">
                <div>
                    <p class="text-sm font-bold text-indigo-800">Panduan Fabriku</p>
                    <h1 class="mt-3 max-w-2xl text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-5xl">
                        Produksi, stok, dan penjualan usaha.
                    </h1>
                </div>
                <p class="max-w-xl leading-7 text-slate-700">
                    Catatan praktis untuk membantu pemilik UMKM menjaga pekerjaan harian tetap tercatat dan mudah ditindaklanjuti.
                </p>
            </header>

            <nav aria-label="Kategori artikel" class="mt-9 flex flex-wrap gap-2">
                <Link
                    href="/blog"
                    class="inline-flex min-h-11 items-center rounded-md border px-4 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                    :class="
                        !activeCategory ? 'border-[#163761] bg-[#163761] text-white' : 'border-slate-300 bg-white text-slate-800 hover:bg-slate-100'
                    "
                    :aria-current="!activeCategory ? 'page' : undefined"
                >
                    Semua
                </Link>
                <Link
                    v-for="category in categories"
                    :key="category.slug"
                    :href="`/blog?category=${category.slug}`"
                    class="inline-flex min-h-11 items-center rounded-md border px-4 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                    :class="
                        activeCategory === category.slug
                            ? 'border-[#163761] bg-[#163761] text-white'
                            : 'border-slate-300 bg-white text-slate-800 hover:bg-slate-100'
                    "
                    :aria-current="activeCategory === category.slug ? 'page' : undefined"
                >
                    {{ category.name }}
                </Link>
            </nav>

            <div v-if="posts.data.length" class="mt-8 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="post in posts.data"
                    :key="post.slug"
                    :href="`/blog/${post.slug}`"
                    class="group block rounded-lg border border-slate-300 bg-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-700"
                >
                    <img
                        v-if="post.featured_image_url"
                        :src="post.featured_image_url"
                        class="aspect-[4/3] w-full rounded-t-[7px] object-cover"
                        :alt="post.title"
                    />
                    <div class="p-5">
                        <p v-if="post.category" class="text-xs font-bold text-indigo-800">{{ post.category.name }}</p>
                        <h2 class="mt-2 text-lg leading-snug font-bold text-[#163761] group-hover:text-indigo-800">{{ post.title }}</h2>
                        <p v-if="post.excerpt" class="mt-2 text-sm leading-6 text-slate-700">{{ post.excerpt }}</p>
                        <time v-if="post.published_at" class="mt-4 block text-xs text-slate-600">
                            {{ new Date(post.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </time>
                    </div>
                </Link>
            </div>
            <div v-else class="mt-8 border-y border-slate-300 py-10">
                <h2 class="text-xl font-bold text-[#163761]">Belum ada artikel di kategori ini.</h2>
                <p class="mt-2 text-slate-700">Pilih kategori lain untuk melihat panduan yang sudah terbit.</p>
                <Link
                    href="/blog"
                    class="mt-4 inline-flex min-h-11 items-center font-bold text-indigo-800 underline decoration-indigo-300 underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                >
                    Tampilkan semua artikel
                </Link>
            </div>

            <nav v-if="posts.links.length > 1" aria-label="Navigasi halaman artikel" class="mt-9 flex flex-wrap gap-2">
                <template v-for="link in posts.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="inline-flex min-h-11 items-center rounded-md border px-3 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                        :class="
                            link.active ? 'border-[#163761] bg-[#163761] text-white' : 'border-slate-300 bg-white text-slate-800 hover:bg-slate-100'
                        "
                        :aria-current="link.active ? 'page' : undefined"
                    >
                        <span v-html="link.label" />
                    </Link>
                    <span
                        v-else
                        class="inline-flex min-h-11 items-center rounded-md border border-slate-200 px-3 text-sm text-slate-500"
                        aria-disabled="true"
                    >
                        <span v-html="link.label" />
                    </span>
                </template>
            </nav>
        </div>
    </PublicLayout>
</template>
