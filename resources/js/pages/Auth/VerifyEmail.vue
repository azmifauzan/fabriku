<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Mail, ArrowLeft } from 'lucide-vue-next';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center px-4 py-6">
        <Head title="Verifikasi Email - Fabriku" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
                <!-- Header -->
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                            <Mail :size="32" class="text-white" />
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Verifikasi Email Anda</h1>
                    <p class="text-gray-600">
                        Kami telah mengirim link verifikasi ke email Anda. Silakan cek inbox atau folder spam Anda.
                    </p>
                </div>

                <Form action="/email/verification-notification" method="post" v-slot="{ processing, wasSuccessful }">
                    <div v-if="wasSuccessful" class="text-sm text-green-600 bg-green-50 px-4 py-3 rounded-lg mb-4">
                        Link verifikasi baru telah dikirim!
                    </div>

                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
                    >
                        {{ processing ? 'Mengirim...' : 'Kirim Ulang Email Verifikasi' }}
                    </button>
                </Form>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-indigo-600 transition-colors"
                    >
                        <ArrowLeft :size="16" />
                        Kembali ke Login
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
