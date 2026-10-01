<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import { useForm } from '@inertiajs/vue3';
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
    <SeoHead title="Masuk Admin - Fabriku" :noindex="true" />

    <div class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <!-- Logo & Title -->
            <div class="text-center">
                <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="/images/fabriku-logo-only.png?v=3" alt="" class="h-11 w-11 object-contain" />
                        <img src="/images/fabriku-word.png?v=3" alt="Fabriku" class="h-6 w-[108px] object-contain object-left" />
                        <span class="rounded-md border border-slate-300 px-2 py-1 text-sm font-semibold text-slate-700">Admin</span>
                    </div>
                </div>
                <h1 class="mt-3 text-lg font-semibold text-slate-900">Masuk ke admin Fabriku</h1>
                <p class="text-sm text-slate-600">Akses khusus tim Fabriku</p>
            </div>

            <!-- Login Form -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <form class="space-y-6" @submit.prevent="submit">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                        <div class="mt-1">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                class="block w-full appearance-none rounded-lg border border-[#858A94] bg-white px-4 py-3 text-slate-900 placeholder-slate-500 transition focus:border-transparent focus:ring-2 focus:ring-indigo-600 focus:outline-none"
                                placeholder="admin@fabriku.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-700">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Kata sandi</label>
                        <div class="relative mt-1">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                class="block w-full appearance-none rounded-lg border border-[#858A94] bg-white px-4 py-3 pr-12 text-slate-900 placeholder-slate-500 transition focus:border-transparent focus:ring-2 focus:ring-indigo-600 focus:outline-none"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute top-1/2 right-1 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-lg text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                :aria-pressed="showPassword"
                            >
                                <Eye v-if="!showPassword" :size="20" />
                                <EyeOff v-else :size="20" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-700">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-[#858A94] text-indigo-600 focus:ring-indigo-600"
                        />
                        <label for="remember" class="ml-2 block text-sm text-slate-700">Ingat saya</label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative flex min-h-12 w-full justify-center rounded-lg border border-transparent bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="!form.processing">Masuk ke panel admin</span>
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
                                Memproses...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Info -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-slate-600">Hanya untuk tim Fabriku</p>
                </div>
            </div>

            <!-- Back to Site -->
            <div class="text-center">
                <a href="/" class="inline-flex min-h-11 items-center text-sm font-medium text-indigo-700 transition hover:text-indigo-900"
                    >Kembali ke halaman utama</a
                >
            </div>

            <div class="text-center text-xs text-slate-600">
                <a href="/privasi" class="hover:text-indigo-700 hover:underline">Kebijakan Privasi</a>
                ·
                <a href="/syarat-ketentuan" class="hover:text-indigo-700 hover:underline">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>
</template>
