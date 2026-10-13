<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Block = { title: string; body: string };

const props = defineProps<{
    page: {
        category: string;
        category_label: string;
        meta_title: string;
        meta_description: string;
        eyebrow: string;
        headline: string;
        intro: string;
        pains: Block[];
        features: Block[];
        faqs: Array<{ question: string; answer: string }>;
    };
    relatedPosts: Array<{ slug: string; title: string; excerpt: string | null }>;
    otherPages: Array<{ slug: string; label: string }>;
    canonical: string;
}>();

const openIndex = ref<number | null>(0);

const jsonLd = computed(() => {
    const origin = new URL(props.canonical).origin;

    return [
        {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: [
                { '@type': 'ListItem', position: 1, name: 'Fabriku', item: origin },
                { '@type': 'ListItem', position: 2, name: props.page.eyebrow, item: props.canonical },
            ],
        },
        {
            '@context': 'https://schema.org',
            '@type': 'FAQPage',
            mainEntity: props.page.faqs.map((faq) => ({
                '@type': 'Question',
                name: faq.question,
                acceptedAnswer: { '@type': 'Answer', text: faq.answer },
            })),
        },
    ];
});
</script>

<template>
    <SeoHead :title="page.meta_title" :description="page.meta_description" :canonical="canonical" :json-ld="jsonLd" />
    <PublicLayout>
        <section class="border-b border-slate-200">
            <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20 lg:px-10">
                <nav aria-label="Breadcrumb" class="text-sm text-slate-600">
                    <Link href="/" class="font-semibold hover:text-indigo-800">Fabriku</Link>
                    <span class="mx-2" aria-hidden="true">/</span>
                    <span>{{ page.eyebrow }}</span>
                </nav>
                <h1 class="mt-5 max-w-4xl text-4xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-5xl">
                    {{ page.headline }}
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-700">{{ page.intro }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        href="/register"
                        class="inline-flex min-h-12 items-center rounded-md bg-indigo-700 px-5 font-bold text-white hover:bg-indigo-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                    >
                        Coba gratis
                    </Link>
                    <Link
                        href="/#harga"
                        class="inline-flex min-h-12 items-center rounded-md border border-slate-300 bg-white px-5 font-bold text-[#163761] hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                    >
                        Lihat harga
                    </Link>
                </div>
            </div>
        </section>

        <section class="border-b border-slate-200 bg-[#eef2f7]">
            <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20 lg:px-10">
                <h2 class="max-w-2xl text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-4xl">Masalah yang sering muncul</h2>
                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <article v-for="pain in page.pains" :key="pain.title" class="rounded-lg border border-slate-300 bg-white p-5">
                        <h3 class="text-lg font-bold text-[#163761]">{{ pain.title }}</h3>
                        <p class="mt-2 leading-7 text-slate-700">{{ pain.body }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="border-b border-slate-200">
            <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20 lg:px-10">
                <p class="text-sm font-bold text-indigo-800">Fitur untuk {{ page.category_label }}</p>
                <h2 class="mt-3 max-w-2xl text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-4xl">
                    Yang bisa dicatat di Fabriku
                </h2>
                <ol class="mt-8 grid gap-x-10 divide-y divide-slate-300 border-y border-slate-300 md:grid-cols-2 md:divide-y-0">
                    <li v-for="(feature, index) in page.features" :key="feature.title" class="grid gap-2 py-5 sm:grid-cols-[3rem_1fr]">
                        <span class="text-sm font-bold text-indigo-800">{{ String(index + 1).padStart(2, '0') }}</span>
                        <div>
                            <h3 class="text-lg font-bold text-[#163761]">{{ feature.title }}</h3>
                            <p class="mt-1 leading-7 text-slate-700">{{ feature.body }}</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <section v-if="relatedPosts.length" class="border-b border-slate-200 bg-[#eef2f7]">
            <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20 lg:px-10">
                <h2 class="text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761]">Panduan terkait</h2>
                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <Link
                        v-for="post in relatedPosts"
                        :key="post.slug"
                        :href="`/blog/${post.slug}`"
                        class="rounded-lg border border-slate-300 bg-white p-5 hover:border-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                    >
                        <h3 class="font-bold text-[#163761]">{{ post.title }}</h3>
                        <p v-if="post.excerpt" class="mt-2 line-clamp-3 text-sm leading-6 text-slate-700">{{ post.excerpt }}</p>
                    </Link>
                </div>
            </div>
        </section>

        <section class="border-b border-slate-200 bg-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[0.75fr_1.25fr] lg:gap-20 lg:px-10">
                <h2 class="text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-4xl">Pertanyaan umum</h2>
                <div class="border-t border-slate-300">
                    <article v-for="(faq, index) in page.faqs" :key="faq.question" class="border-b border-slate-300">
                        <h3>
                            <button
                                type="button"
                                class="flex min-h-14 w-full items-center justify-between gap-5 rounded-sm py-5 text-left focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-600"
                                :aria-expanded="openIndex === index"
                                :aria-controls="`faq-answer-${index}`"
                                @click="openIndex = openIndex === index ? null : index"
                            >
                                <span class="text-lg font-bold text-[#163761]">{{ faq.question }}</span>
                                <span
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-700"
                                    aria-hidden="true"
                                >
                                    <Plus :size="19" class="transition-transform duration-200" :class="openIndex === index && 'rotate-45'" />
                                </span>
                            </button>
                        </h3>
                        <div v-show="openIndex === index" :id="`faq-answer-${index}`" class="max-w-2xl pr-12 pb-6 leading-7 text-slate-600">
                            {{ faq.answer }}
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section>
            <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 lg:px-10">
                <h2 class="text-xl font-bold text-[#163761]">Fabriku untuk jenis usaha lain</h2>
                <ul class="mt-4 flex flex-wrap gap-2">
                    <li v-for="other in otherPages" :key="other.slug">
                        <Link
                            :href="`/aplikasi/${other.slug}`"
                            class="inline-flex min-h-11 items-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-[#163761] hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                        >
                            {{ other.label }}
                        </Link>
                    </li>
                </ul>
            </div>
        </section>
    </PublicLayout>
</template>
