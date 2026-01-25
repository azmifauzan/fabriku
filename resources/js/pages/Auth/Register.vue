<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Category {
    label: string;
    icon: string;
    description: string;
}

const props = defineProps<{
    categories: Record<string, Category>;
    prices: {
        monthly: number;
        yearly: number;
    };
}>();

const form = useForm({
    business_name: '',
    business_category: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    subscription_plan: 'trial',
});

const selectedCategory = ref<string | null>(null);

const selectCategory = (key: string) => {
    selectedCategory.value = key;
    form.business_category = key;
};

const submit = () => {
    form.post('/register');
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center px-4 py-12">
        <Head title="Daftar - Fabriku" />

        <div class="w-full max-w-xl">
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
                    <p class="text-gray-600">Mulai kelola produksi bisnis Anda</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
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
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
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
                                class="relative p-4 rounded-xl border-2 transition-colors text-left"
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



                    <hr class="border-gray-200" />

                     <!-- Subscription Plan -->
                     <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Pilih Paket
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Trial -->
                            <button
                                type="button"
                                @click="form.subscription_plan = 'trial'"
                                class="relative p-4 rounded-xl border-2 text-left transition-all"
                                :class="[
                                    form.subscription_plan === 'trial'
                                        ? 'border-indigo-500 bg-indigo-50 shadow-sm'
                                        : 'border-gray-200 hover:border-indigo-200'
                                ]"
                            >
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">Free Trial</span>
                                    <span class="text-sm text-gray-500">30 Hari</span>
                                    <span class="mt-2 text-indigo-600 font-bold">Rp 0</span>
                                </div>
                                <div 
                                    v-if="form.subscription_plan === 'trial'"
                                    class="absolute top-2 right-2 w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center"
                                >
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </button>

                            <!-- Full Member -->
                            <button
                                type="button"
                                @click="form.subscription_plan = 'full'"
                                class="relative p-4 rounded-xl border-2 text-left transition-all"
                                :class="[
                                    form.subscription_plan === 'full'
                                        ? 'border-indigo-500 bg-indigo-50 shadow-sm'
                                        : 'border-gray-200 hover:border-indigo-200'
                                ]"
                            >
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">Full Member</span>
                                    <span class="text-sm text-gray-500">Akses Selamanya</span>
                                    <span class="mt-2 text-indigo-600 font-bold">
                                        Rp {{ new Intl.NumberFormat('id-ID').format(prices.monthly) }}/bln
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        atau Rp {{ new Intl.NumberFormat('id-ID').format(prices.yearly) }}/thn
                                    </span>
                                </div>
                                <div 
                                    v-if="form.subscription_plan === 'full'"
                                    class="absolute top-2 right-2 w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center"
                                >
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </button>
                        </div>
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
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
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
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
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
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                placeholder="Min. 8 karakter"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                :class="{ 'border-red-500': form.errors.password }"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">
                                {{ form.errors.password }}
                            </p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Konfirmasi Password
                            </label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                placeholder="Ulangi password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Memproses...</span>

                        <span v-else-if="form.subscription_plan === 'trial'">Mulai Trial 30 Hari</span>
                        <span v-else>Daftar Membership</span>
                    </button>

                    <!-- Trial Info -->
                    <div class="text-center">
                        <p class="text-xs text-gray-500">
                            🎁 Gratis 30 hari tanpa kartu kredit • Akses penuh semua fitur
                        </p>
                    </div>
                </form>

                <!-- Login Link -->
                <div class="mt-8 pt-6 border-t border-gray-200 text-center">
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
