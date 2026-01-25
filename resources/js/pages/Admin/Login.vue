<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Admin Login" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-900 via-indigo-900 to-purple-800 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo & Title -->
            <div class="text-center">
                <div class="flex justify-center">
                    <div class="bg-white/10 backdrop-blur-sm p-4 rounded-2xl">
                        <svg class="w-16 h-16 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                </div>
                <h2 class="mt-6 text-4xl font-extrabold text-white">
                    Fabriku Admin
                </h2>
                <p class="mt-2 text-sm text-purple-200">
                    Platform Management Dashboard
                </p>
            </div>

            <!-- Login Form -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-2xl p-8 border border-white/20">
                <form class="space-y-6" @submit.prevent="submit">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-purple-100">
                            Email Address
                        </label>
                        <div class="mt-1">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                class="appearance-none block w-full px-4 py-3 border border-purple-300/30 rounded-lg bg-white/10 backdrop-blur-sm text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition"
                                placeholder="admin@fabriku.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-300">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-purple-100">
                            Password
                        </label>
                        <div class="mt-1">
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="appearance-none block w-full px-4 py-3 border border-purple-300/30 rounded-lg bg-white/10 backdrop-blur-sm text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition"
                                placeholder="••••••••"
                            />
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
                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-purple-300 rounded bg-white/10"
                        />
                        <label for="remember" class="ml-2 block text-sm text-purple-100">
                            Remember me
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
                        >
                            <span v-if="!form.processing">Sign in to Admin Panel</span>
                            <span v-else class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Signing in...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Info -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-purple-200">
                        Admin access only • Secure connection
                    </p>
                </div>
            </div>

            <!-- Back to Site -->
            <div class="text-center">
                <a href="/" class="text-sm text-purple-200 hover:text-white transition">
                    ← Back to main site
                </a>
            </div>
        </div>
    </div>
</template>
