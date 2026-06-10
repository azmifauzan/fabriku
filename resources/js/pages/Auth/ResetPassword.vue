<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const form = {
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
};

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4 py-6">
        <Head title="Reset Password - Fabriku" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xl">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <Link href="/" class="inline-block">
                        <div class="mb-2 flex items-center justify-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600">
                                <span class="text-lg font-bold text-white">F</span>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900">Fabriku</h1>
                        </div>
                    </Link>
                    <h2 class="mt-4 mb-2 text-xl font-bold text-gray-900">Reset Password</h2>
                    <p class="text-sm text-gray-600">Masukkan password baru Anda</p>
                </div>

                <Form action="/reset-password" method="post" class="space-y-4" v-slot="{ processing, errors }">
                    <input type="hidden" name="token" :value="form.token" />
                    <input type="hidden" name="email" :value="form.email" />

                    <!-- Email (Read-only) -->
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700"> Email </label>
                        <input
                            id="email"
                            type="email"
                            :value="form.email"
                            readonly
                            class="w-full cursor-not-allowed rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-500"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700"> Password Baru </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                v-model="form.password"
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 pr-12 text-gray-900 placeholder-gray-400 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                :class="{ 'border-red-500': errors.password }"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                tabindex="-1"
                            >
                                <Eye v-if="!showPassword" :size="20" />
                                <EyeOff v-else :size="20" />
                            </button>
                        </div>
                        <div v-if="errors.password" class="mt-2 text-sm text-red-500">
                            {{ errors.password }}
                        </div>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700"> Konfirmasi Password </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                required
                                v-model="form.password_confirmation"
                                placeholder="Ulangi password baru"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 pr-12 text-gray-900 placeholder-gray-400 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                            <button
                                type="button"
                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                                class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                tabindex="-1"
                            >
                                <Eye v-if="!showPasswordConfirmation" :size="20" />
                                <EyeOff v-else :size="20" />
                            </button>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errors.email" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-500">
                        {{ errors.email }}
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ processing ? 'Mereset Password...' : 'Reset Password' }}
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
