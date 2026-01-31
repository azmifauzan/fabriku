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
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center px-4 py-6">
        <Head title="Daftar - Fabriku" />

        <div class="w-full max-w-xl">
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
                    <p class="text-gray-600">Mulai kelola produksi bisnis Anda</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Business Name -->
                    <div>
                        <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Bisnis
                        </label>
                        <input
                            id="business_name"
                            v-model="form.business_name"
                            type="text"
                            required
                            placeholder="Contoh: Konveksi Maju Jaya"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            :class="{ 'border-red-500': form.errors.business_name }"
                        />
                        <p v-if="form.errors.business_name" class="mt-1 text-sm text-red-500">
                            {{ form.errors.business_name }}
                        </p>
                    </div>

                    <!-- Business Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Kategori Bisnis
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                v-for="(category, key) in categories"
                                :key="key"
                                type="button"
                                @click="selectCategory(key as string)"
                                class="relative p-3 rounded-xl border-2 transition-colors text-left"
                                :class="[
                                    selectedCategory === key
                                        ? 'border-indigo-500 bg-indigo-50'
                                        : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'
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
                                    class="absolute top-2 right-2 w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center"
                                >
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Anda
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Nama lengkap"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="email@contoh.com"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    placeholder="Min. 8 karakter"
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                    :class="{ 'border-red-500': form.errors.password }"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
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
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Konfirmasi Password
                            </label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    required
                                    placeholder="Ulangi password"
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                />
                                <button
                                    type="button"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
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
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Memproses...</span>
                        <span v-else>Mulai Trial 30 Hari</span>
                    </button>

                    <!-- Trial Info -->
                    <div class="text-center">
                        <p class="text-xs text-gray-500">
                            🎁 Gratis 30 hari tanpa kartu kredit • Akses penuh semua fitur
                        </p>
                    </div>
                </form>

                <!-- Login Link -->
                <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                    <p class="text-gray-600">
                        Sudah punya akun?
                        <Link href="/login" class="text-indigo-600 font-semibold hover:text-indigo-500 transition-colors">
                            Masuk di sini
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
