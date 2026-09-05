<script setup lang="ts">
import { faqs } from '@/data/faq';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

const openIndex = ref<number | null>(0);
</script>

<template>
    <section id="faq" class="bg-white py-20 sm:py-28">
        <div class="mx-auto grid max-w-[1200px] gap-12 px-5 sm:px-8 lg:grid-cols-[0.65fr_1.35fr] lg:gap-20">
            <div>
                <p class="mb-5 text-xs font-black tracking-[0.18em] text-indigo-600 uppercase">Tanya sebelum mulai</p>
                <h2 class="text-5xl leading-[0.92] font-black tracking-[-0.055em] uppercase sm:text-7xl">Yang sering bikin ragu.</h2>
                <p class="mt-6 max-w-sm text-lg font-medium text-slate-500">Jawaban pendek untuk keputusan yang seharusnya tidak rumit.</p>
            </div>

            <div class="border-t border-gray-200">
                <article v-for="(faq, index) in faqs" :key="faq.question" class="border-b border-gray-200">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-5 py-6 text-left sm:py-8"
                        :aria-expanded="openIndex === index"
                        :aria-controls="`faq-answer-${index}`"
                        @click="openIndex = openIndex === index ? null : index"
                    >
                        <span class="text-lg font-black sm:text-xl">{{ String(index + 1).padStart(2, '0') }}. {{ faq.question }}</span>
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-indigo-200 bg-indigo-50 text-indigo-600"
                        >
                            <Plus :size="19" class="transition-transform duration-200" :class="openIndex === index && 'rotate-45'" />
                        </span>
                    </button>
                    <div
                        v-show="openIndex === index"
                        :id="`faq-answer-${index}`"
                        class="max-w-2xl pr-12 pb-7 text-base leading-relaxed font-medium text-slate-500 sm:pb-8"
                    >
                        {{ faq.answer }}
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
