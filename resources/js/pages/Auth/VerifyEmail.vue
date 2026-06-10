<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Mail } from 'lucide-vue-next';
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4 py-6">
        <Head title="Verifikasi Email - Fabriku" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xl">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <div class="mb-4 flex items-center justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600">
                            <Mail :size="32" class="text-white" />
                        </div>
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-gray-900">Verifikasi Email Anda</h1>
                    <p class="text-gray-600">Kami telah mengirim link verifikasi ke email Anda. Silakan cek inbox atau folder spam Anda.</p>
                </div>

                <Form action="/email/verification-notification" method="post" v-slot="{ processing, wasSuccessful }">
                    <div v-if="wasSuccessful" class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-600">
                        Link verifikasi baru telah dikirim!
                    </div>

                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
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
                        class="inline-flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-indigo-600"
                    >
                        <ArrowLeft :size="16" />
                        Kembali ke Login
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
