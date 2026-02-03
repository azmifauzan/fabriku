<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { CheckCircle, ArrowRight } from 'lucide-vue-next';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-emerald-50 flex items-center justify-center px-4 py-6">
        <Head title="Email Terverifikasi - Fabriku" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100">
                <!-- Success Icon -->
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center animate-bounce-slow">
                            <CheckCircle :size="48" class="text-white" />
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Email Berhasil Diverifikasi! 🎉</h1>
                    <p class="text-gray-600">
                        Selamat! Alamat email Anda telah berhasil diverifikasi. Akun Anda sekarang aktif dan siap digunakan.
                    </p>
                </div>

                <!-- Info Box -->
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                    <h3 class="font-semibold text-green-800 mb-2">✨ Langkah Selanjutnya:</h3>
                    <ul class="text-sm text-green-700 space-y-1">
                        <li>• Login ke dashboard Anda</li>
                        <li>• Lengkapi profil bisnis Anda</li>
                        <li>• Mulai kelola produksi dengan Fabriku!</li>
                    </ul>
                </div>

                <!-- Login Button -->
                <Link
                    href="/login"
                    class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                >
                    Masuk ke Dashboard
                    <ArrowRight :size="20" />
                </Link>

                <!-- Footer Text -->
                <p class="text-center text-sm text-gray-500 mt-6">
                    Anda akan menerima email selamat datang dalam beberapa saat.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes bounce-slow {
    0%, 100% {
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
