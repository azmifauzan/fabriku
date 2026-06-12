<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const form = {
    email: '',
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4 py-6">
        <Head title="Lupa Password - Fabriku" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xl">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <Link href="/" class="inline-block">
                        <div class="mb-2 flex items-center justify-center gap-2">
                            <img src="/images/fabriku-logo-only.png?v=2" alt="Fabriku Logo" class="h-8 w-8 object-contain" />
                            <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-6 object-contain" />
                        </div>
                    </Link>
                    <h2 class="mt-4 mb-2 text-xl font-bold text-gray-900">Lupa Password?</h2>
                    <p class="text-sm text-gray-600">Masukkan email Anda dan kami akan mengirimkan link untuk reset password.</p>
                </div>

                <Form action="/forgot-password" method="post" class="space-y-4" v-slot="{ processing, errors, wasSuccessful }">
                    <!-- Success Message -->
                    <div v-if="wasSuccessful" class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-600">
                        Link reset password telah dikirim ke email Anda!
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
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            :class="{ 'border-red-500': errors.email }"
                        />
                        <div v-if="errors.email" class="mt-2 text-sm text-red-500">
                            {{ errors.email }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ processing ? 'Mengirim...' : 'Kirim Link Reset Password' }}
                    </button>
                </Form>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <Link href="/login" class="inline-flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-indigo-600">
                        <ArrowLeft :size="16" />
                        Kembali ke Login
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
