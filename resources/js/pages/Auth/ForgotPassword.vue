<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import { Form, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const form = {
    email: '',
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-6">
        <SeoHead title="Lupa Kata Sandi - Fabriku" :noindex="true" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <Link href="/" class="inline-flex min-h-11 items-center justify-center gap-2">
                        <div class="mb-2 flex items-center justify-center gap-2">
                            <img src="/images/fabriku-logo-only.png?v=3" alt="" class="h-8 w-8 object-contain" />
                            <img src="/images/fabriku-word.png?v=3" alt="Fabriku" class="h-6 object-contain" />
                        </div>
                    </Link>
                    <h1 class="mt-4 mb-2 text-xl font-bold text-gray-900">Lupa kata sandi?</h1>
                    <p class="text-sm text-gray-600">Masukkan email Anda untuk menerima tautan membuat kata sandi baru.</p>
                </div>

                <Form action="/forgot-password" method="post" class="space-y-4" v-slot="{ processing, errors, wasSuccessful }">
                    <!-- Success Message -->
                    <div v-if="wasSuccessful" class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">
                        Tautan untuk mengatur ulang kata sandi sudah dikirim ke email Anda.
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700"> Email </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            v-model="form.email"
                            placeholder="email@contoh.com"
                            class="w-full rounded-xl border border-[#858A94] px-4 py-2.5 text-gray-900 placeholder-gray-500 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            :class="{ 'border-red-500': errors.email }"
                        />
                        <div v-if="errors.email" class="mt-2 text-sm text-red-700">
                            {{ errors.email }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="min-h-12 w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white transition-colors hover:bg-indigo-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ processing ? 'Mengirim...' : 'Kirim tautan' }}
                    </button>
                </Form>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <Link href="/login" class="inline-flex min-h-11 items-center gap-2 text-sm text-gray-600 transition-colors hover:text-indigo-700">
                        <ArrowLeft :size="16" />
                        Kembali ke halaman masuk
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
