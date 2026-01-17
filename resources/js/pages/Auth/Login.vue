<script setup lang="ts">
import { Form } from '@inertiajs/vue3';

const form = {
    email: '',
    password: '',
    remember: false,
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Fabriku</h2>
                <p class="mt-2 text-center text-sm text-gray-600">Masuk ke akun Anda</p>
            </div>

            <Form action="/login" method="post" class="mt-8 space-y-6" v-slot="{ processing, errors }">
                <div class="-space-y-px rounded-md shadow-sm">
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            v-model="form.email"
                            class="relative block w-full appearance-none rounded-none rounded-t-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm"
                            :class="{ 'border-red-500': errors.email }"
                            placeholder="Email"
                        />
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            v-model="form.password"
                            class="relative block w-full appearance-none rounded-none rounded-b-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm"
                            :class="{ 'border-red-500': errors.password || errors.email }"
                            placeholder="Password"
                        />
                    </div>
                </div>

                <div v-if="errors.email" class="text-sm text-red-500">
                    {{ errors.email }}
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            v-model="form.remember"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="remember" class="ml-2 block text-sm text-gray-900"> Ingat saya </label>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="group relative flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="processing">Memproses...</span>
                        <span v-else>Masuk</span>
                    </button>
                </div>

                <div class="text-center text-sm text-gray-600">
                    <p class="mb-3 font-semibold text-gray-900">🎯 Demo Credentials</p>

                    <div class="grid grid-cols-1 gap-3 text-left">
                        <!-- Garment Demo -->
                        <div class="rounded-md border border-indigo-200 bg-indigo-50 p-3">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="text-base">🧵</span>
                                <span class="text-sm font-semibold text-gray-900">Konveksi Fabriku</span>
                            </div>
                            <div class="space-y-1 text-xs text-gray-600">
                                <p>Email: <span class="font-mono text-indigo-600">admin@konveksi.com</span></p>
                                <p>Password: <span class="font-mono text-indigo-600">password</span></p>
                                <p class="mt-1 text-xs text-gray-500">Kategori: Garment & Konveksi</p>
                            </div>
                        </div>

                        <!-- Food Demo -->
                        <div class="rounded-md border border-pink-200 bg-pink-50 p-3">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="text-base">🍰</span>
                                <span class="text-sm font-semibold text-gray-900">Kue Mama Homemade</span>
                            </div>
                            <div class="space-y-1 text-xs text-gray-600">
                                <p>Email: <span class="font-mono text-pink-600">admin@kuemama.com</span></p>
                                <p>Password: <span class="font-mono text-pink-600">password</span></p>
                                <p class="mt-1 text-xs text-gray-500">Kategori: Makanan & Kue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Form>
        </div>
    </div>
</template>
