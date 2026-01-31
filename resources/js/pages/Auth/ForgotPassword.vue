<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Mail, ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

const form = {
    email: '',
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center px-4 py-6">
        <Head title="Lupa Password - Fabriku" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
                <!-- Header -->
                <div class="text-center mb-6">
                    <Link href="/" class="inline-block">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-lg">F</span>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900">Fabriku</h1>
                        </div>
                    </Link>
                    <h2 class="text-xl font-bold text-gray-900 mt-4 mb-2">Lupa Password?</h2>
                    <p class="text-gray-600 text-sm">
                        Masukkan email Anda dan kami akan mengirimkan link untuk reset password.
                    </p>
                </div>

                <Form action="/forgot-password" method="post" class="space-y-4" v-slot="{ processing, errors, wasSuccessful }">
                    <!-- Success Message -->
                    <div v-if="wasSuccessful" class="text-sm text-green-600 bg-green-50 px-4 py-3 rounded-lg">
                        Link reset password telah dikirim ke email Anda!
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            v-model="form.email"
                            placeholder="email@contoh.com"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
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
                        class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
                    >
                        {{ processing ? 'Mengirim...' : 'Kirim Link Reset Password' }}
                    </button>
                </Form>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <Link
                        href="/login"
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
