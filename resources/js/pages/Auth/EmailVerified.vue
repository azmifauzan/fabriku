<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle } from 'lucide-vue-next';
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-6">
        <SeoHead title="Email Terverifikasi - Fabriku" :noindex="true" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <!-- Success Icon -->
                <div class="mb-6 text-center">
                    <div class="mb-4 flex items-center justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-700">
                            <CheckCircle :size="48" class="text-white" />
                        </div>
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-gray-900">Email sudah terverifikasi</h1>
                    <p class="text-gray-600">Alamat email Anda terverifikasi. Akun siap digunakan.</p>
                </div>

                <!-- Info Box -->
                <div class="mb-6 rounded-xl border border-emerald-700 bg-emerald-50 p-4">
                    <h2 class="mb-2 font-semibold text-emerald-900">Langkah berikutnya</h2>
                    <ul class="list-disc space-y-1 pl-5 text-sm text-emerald-900">
                        <li>Masuk ke dashboard Anda.</li>
                        <li>Lengkapi profil bisnis.</li>
                        <li>Mulai kelola operasional usaha.</li>
                    </ul>
                </div>

                <!-- Login Button -->
                <Link
                    href="/login"
                    class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white transition-colors hover:bg-indigo-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                >
                    Masuk ke dashboard
                    <ArrowRight :size="20" />
                </Link>

                <!-- Footer Text -->
                <p class="mt-6 text-center text-sm text-gray-500">Anda akan menerima email selamat datang dalam beberapa saat.</p>
            </div>
        </div>
    </div>
</template>
