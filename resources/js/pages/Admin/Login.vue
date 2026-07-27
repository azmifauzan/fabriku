<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Admin Login" />

    <div
        class="flex min-h-screen items-center justify-center bg-gradient-to-br from-purple-900 via-indigo-900 to-purple-800 px-4 py-12 sm:px-6 lg:px-8"
    >
        <div class="w-full max-w-md space-y-8">
            <!-- Logo & Title -->
            <div class="text-center">
                <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                        <img src="/images/fabriku-logo-only.png?v=2" alt="Fabriku Logo" class="h-16 w-16 object-contain" />
                    </div>
                    <div class="flex items-center space-x-2">
                        <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-8 object-contain" />
                        <span class="text-4xl font-extrabold text-white">Admin</span>
                    </div>
                </div>
                <p class="mt-2 text-sm text-purple-200">Platform Management Dashboard</p>
            </div>

            <!-- Login Form -->
            <div class="rounded-2xl border border-white/20 bg-white/10 p-8 shadow-2xl backdrop-blur-md">
                <form class="space-y-6" @submit.prevent="submit">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-purple-100"> Email Address </label>
                        <div class="mt-1">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                class="block w-full appearance-none rounded-lg border border-purple-300/30 bg-white/10 px-4 py-3 text-white placeholder-purple-300 backdrop-blur-sm transition focus:border-transparent focus:ring-2 focus:ring-purple-400 focus:outline-none"
                                placeholder="admin@fabriku.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-300">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-purple-100"> Password </label>
                        <div class="relative mt-1">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                class="block w-full appearance-none rounded-lg border border-purple-300/30 bg-white/10 px-4 py-3 pr-12 text-white placeholder-purple-300 backdrop-blur-sm transition focus:border-transparent focus:ring-2 focus:ring-purple-400 focus:outline-none"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute top-1/2 right-3 -translate-y-1/2 text-purple-300 transition-colors hover:text-white"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                :aria-pressed="showPassword"
                            >
                                <Eye v-if="!showPassword" :size="20" />
                                <EyeOff v-else :size="20" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-300">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-purple-300 bg-white/10 text-purple-600 focus:ring-purple-500"
                        />
                        <label for="remember" class="ml-2 block text-sm text-purple-100"> Remember me </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative flex w-full justify-center rounded-lg border border-transparent bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-lg transition-all duration-200 hover:from-purple-700 hover:to-indigo-700 hover:shadow-xl focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="!form.processing">Sign in to Admin Panel</span>
                            <span v-else class="flex items-center">
                                <svg
                                    class="mr-3 -ml-1 h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Signing in...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Info -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-purple-200">Admin access only • Secure connection</p>
                </div>
            </div>

            <!-- Back to Site -->
            <div class="text-center">
                <a href="/" class="text-sm text-purple-200 transition hover:text-white"> ← Back to main site </a>
            </div>

            <div class="text-center text-xs text-purple-300">
                <a href="/privasi" class="hover:text-white hover:underline">Kebijakan Privasi</a>
                ·
                <a href="/syarat-ketentuan" class="hover:text-white hover:underline">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>
</template>
