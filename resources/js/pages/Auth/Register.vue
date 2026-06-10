<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

interface Category {
    label: string;
    icon: string;
    description: string;
}

const props = defineProps<{
    categories: Record<string, Category>;
}>();

const form = useForm({
    business_name: '',
    business_category: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const selectedCategory = ref<string | null>(null);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const selectCategory = (key: string) => {
    selectedCategory.value = key;
    form.business_category = key;
};

const submit = () => {
    form.post('/register');
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4 py-6">
        <Head title="Daftar - Fabriku" />

        <div class="w-full max-w-xl">
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
                    <p class="text-gray-600">Mulai kelola produksi bisnis Anda</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Business Name -->
                    <div>
                        <label for="business_name" class="mb-2 block text-sm font-medium text-gray-700"> Nama Bisnis </label>
                        <input
                            id="business_name"
                            v-model="form.business_name"
                            type="text"
                            required
                            placeholder="Contoh: Konveksi Maju Jaya"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            :class="{ 'border-red-500': form.errors.business_name }"
                        />
                        <p v-if="form.errors.business_name" class="mt-1 text-sm text-red-500">
                            {{ form.errors.business_name }}
                        </p>
                    </div>

                    <!-- Business Category -->
                    <div>
                        <label class="mb-3 block text-sm font-medium text-gray-700"> Kategori Bisnis </label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                v-for="(category, key) in categories"
                                :key="key"
                                type="button"
                                @click="selectCategory(key as string)"
                                class="relative rounded-xl border-2 p-3 text-left transition-colors"
                                :class="[
                                    selectedCategory === key
                                        ? 'border-indigo-500 bg-indigo-50'
                                        : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50',
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ category.icon }}</span>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ category.label }}</p>
                                        <p class="text-xs text-gray-500">{{ category.description }}</p>
                                    </div>
                                </div>
                                <div
                                    v-if="selectedCategory === key"
                                    class="absolute top-2 right-2 flex h-5 w-5 items-center justify-center rounded-full bg-indigo-500"
                                >
                                    <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </button>
                        </div>
                        <p v-if="form.errors.business_category" class="mt-1 text-sm text-red-500">
                            {{ form.errors.business_category }}
                        </p>
                    </div>

                    <!-- User Name -->
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700"> Nama Anda </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Nama lengkap"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700"> Email </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="email@contoh.com"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium text-gray-700"> Password </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    placeholder="Min. 8 karakter"
                                    class="w-full rounded-xl border border-gray-300 px-4 py-3 pr-12 text-gray-900 placeholder-gray-400 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                    :class="{ 'border-red-500': form.errors.password }"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                    tabindex="-1"
                                >
                                    <Eye v-if="!showPassword" :size="18" />
                                    <EyeOff v-else :size="18" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">
                                {{ form.errors.password }}
                            </p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700"> Konfirmasi Password </label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    required
                                    placeholder="Ulangi password"
                                    class="w-full rounded-xl border border-gray-300 px-4 py-3 pr-12 text-gray-900 placeholder-gray-400 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                />
                                <button
                                    type="button"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                    tabindex="-1"
                                >
                                    <Eye v-if="!showPasswordConfirmation" :size="18" />
                                    <EyeOff v-else :size="18" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-indigo-600 py-3 font-semibold text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="form.processing">Memproses...</span>
                        <span v-else>Mulai Trial 30 Hari</span>
                    </button>

                    <!-- Trial Info -->
                    <div class="text-center">
                        <p class="text-xs text-gray-500">🎁 Gratis 30 hari tanpa kartu kredit • Akses penuh semua fitur</p>
                    </div>
                </form>

                <!-- Login Link -->
                <div class="mt-4 border-t border-gray-200 pt-4 text-center">
                    <p class="text-gray-600">
                        Sudah punya akun?
                        <Link href="/login" class="font-semibold text-indigo-600 transition-colors hover:text-indigo-500"> Masuk di sini </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
