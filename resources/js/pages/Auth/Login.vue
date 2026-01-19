<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const form = {
    email: '',
    password: '',
    remember: false,
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center px-4 py-12">
        <Head title="Masuk - Fabriku" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100">
                <!-- Header -->
                <div class="text-center mb-8">
                    <Link href="/" class="inline-block">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-lg">F</span>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900">Fabriku</h1>
                        </div>
                    </Link>
                    <p class="text-gray-600">Masuk ke akun Anda</p>
                </div>

                <Form action="/login" method="post" class="space-y-6" v-slot="{ processing, errors }">
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
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            :class="{ 'border-red-500': errors.email }"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            v-model="form.password"
                            placeholder="Masukkan password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            :class="{ 'border-red-500': errors.password || errors.email }"
                        />
                    </div>

                    <!-- Error Message -->
                    <div v-if="errors.email" class="text-sm text-red-500 bg-red-50 px-4 py-3 rounded-lg">
                        {{ errors.email }}
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            v-model="form.remember"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Ingat saya
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="processing">Memproses...</span>
                        <span v-else>Masuk</span>
                    </button>
                </Form>

                <!-- Demo Credentials -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-center text-sm font-semibold text-gray-700 mb-4">🎯 Demo Credentials</p>
                    <div class="grid grid-cols-1 gap-3">
                        <!-- Garment Demo -->
                        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-lg">🧵</span>
                                <span class="text-sm font-semibold text-gray-900">Konveksi Fabriku</span>
                            </div>
                            <div class="space-y-1 text-xs text-gray-600">
                                <p>Email: <span class="font-mono text-indigo-600">admin@konveksi.com</span></p>
                                <p>Password: <span class="font-mono text-indigo-600">password</span></p>
                            </div>
                        </div>

                        <!-- Food Demo -->
                        <div class="rounded-xl border border-pink-200 bg-pink-50 p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-lg">🍰</span>
                                <span class="text-sm font-semibold text-gray-900">Kue Mama Homemade</span>
                            </div>
                            <div class="space-y-1 text-xs text-gray-600">
                                <p>Email: <span class="font-mono text-pink-600">admin@kuemama.com</span></p>
                                <p>Password: <span class="font-mono text-pink-600">password</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registration Link -->
                <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                    <p class="text-gray-600">
                        Belum punya akun?
                        <Link href="/register" class="text-indigo-600 font-semibold hover:text-indigo-500 transition-colors">
                            Daftar di sini
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
