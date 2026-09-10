<script setup lang="ts">
import { faqs } from '@/data/faq';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

const openIndex = ref<number | null>(0);
</script>

<template>
    <section id="faq" class="scroll-mt-6 bg-white py-16 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 sm:px-8 lg:grid-cols-[0.75fr_1.25fr] lg:gap-20 lg:px-10">
            <div>
                <p class="text-sm font-bold text-indigo-700">Pertanyaan umum</p>
                <h2 class="mt-3 text-4xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-5xl">Sebelum mulai memakai Fabriku.</h2>
            </div>

            <div class="border-t border-slate-300">
                <article v-for="(faq, index) in faqs" :key="faq.question" class="border-b border-slate-300">
                    <button
                        type="button"
                        class="flex min-h-14 w-full items-center justify-between gap-5 rounded-sm py-5 text-left focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-600 sm:py-6"
                        :aria-expanded="openIndex === index"
                        :aria-controls="`faq-answer-${index}`"
                        @click="openIndex = openIndex === index ? null : index"
                    >
                        <span class="text-lg font-bold text-[#163761]">{{ faq.question }}</span>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-700" aria-hidden="true">
                            <Plus :size="19" class="transition-transform duration-200" :class="openIndex === index && 'rotate-45'" />
                        </span>
                    </button>
                    <div v-show="openIndex === index" :id="`faq-answer-${index}`" class="max-w-2xl pr-12 pb-6 text-base leading-7 text-slate-600">
                        {{ faq.answer }}
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
