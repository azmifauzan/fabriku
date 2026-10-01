<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import { Form, Link } from '@inertiajs/vue3';
import { ArrowLeft, Mail } from 'lucide-vue-next';
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-6">
        <SeoHead title="Verifikasi Email - Fabriku" :noindex="true" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <div class="mb-4 flex items-center justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-600">
                            <Mail :size="32" class="text-white" />
                        </div>
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-gray-900">Verifikasi Email Anda</h1>
                    <p class="text-gray-600">Kami telah mengirim tautan verifikasi ke email Anda. Periksa kotak masuk atau folder spam.</p>
                </div>

                <Form action="/email/verification-notification" method="post" v-slot="{ processing, wasSuccessful }">
                    <div v-if="wasSuccessful" class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">
                        Tautan verifikasi baru sudah dikirim.
                    </div>

                    <button
                        type="submit"
                        :disabled="processing"
                        class="min-h-12 w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white transition-colors hover:bg-indigo-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ processing ? 'Mengirim...' : 'Kirim ulang tautan verifikasi' }}
                    </button>
                </Form>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="inline-flex min-h-11 items-center gap-2 text-sm text-gray-600 transition-colors hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    >
                        <ArrowLeft :size="16" />
                        Kembali ke halaman masuk
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
