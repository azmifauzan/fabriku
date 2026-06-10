<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle } from 'lucide-vue-next';
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-green-50 via-white to-emerald-50 px-4 py-6">
        <Head title="Email Terverifikasi - Fabriku" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-xl">
                <!-- Success Icon -->
                <div class="mb-6 text-center">
                    <div class="mb-4 flex items-center justify-center">
                        <div
                            class="animate-bounce-slow flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-emerald-500"
                        >
                            <CheckCircle :size="48" class="text-white" />
                        </div>
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-gray-900">Email Berhasil Diverifikasi! 🎉</h1>
                    <p class="text-gray-600">Selamat! Alamat email Anda telah berhasil diverifikasi. Akun Anda sekarang aktif dan siap digunakan.</p>
                </div>

                <!-- Info Box -->
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4">
                    <h3 class="mb-2 font-semibold text-green-800">✨ Langkah Selanjutnya:</h3>
                    <ul class="space-y-1 text-sm text-green-700">
                        <li>• Login ke dashboard Anda</li>
                        <li>• Lengkapi profil bisnis Anda</li>
                        <li>• Mulai kelola produksi dengan Fabriku!</li>
                    </ul>
                </div>

                <!-- Login Button -->
                <Link
                    href="/login"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl"
                >
                    Masuk ke Dashboard
                    <ArrowRight :size="20" />
                </Link>

                <!-- Footer Text -->
                <p class="mt-6 text-center text-sm text-gray-500">Anda akan menerima email selamat datang dalam beberapa saat.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes bounce-slow {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.animate-bounce-slow {
    animation: bounce-slow 2s ease-in-out infinite;
}
</style>
