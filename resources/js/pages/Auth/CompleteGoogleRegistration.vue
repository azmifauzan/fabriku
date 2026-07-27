<script setup lang="ts">
import { storeComplete } from '@/actions/App/Http/Controllers/Auth/GoogleAuthController';
import { businessCategoryIcons } from '@/lib/businessCategoryIcons';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Gift } from 'lucide-vue-next';
import { ref } from 'vue';

interface Category {
    label: string;
    icon: string;
    description: string;
}

const props = defineProps<{
    categories: Record<string, Category>;
    name: string;
    email: string;
}>();

const form = useForm({
    business_name: '',
    business_category: '',
    name: props.name,
});

const selectedCategory = ref<string | null>(null);

const selectCategory = (key: string) => {
    selectedCategory.value = key;
    form.business_category = key;
};

const submit = () => {
    form.post(storeComplete.url());
};
</script>

<template>
    <div class="auth-page relative min-h-screen overflow-hidden bg-slate-50 text-gray-800">
        <Head title="Lengkapi Pendaftaran - Fabriku" />
        <div class="blueprint absolute inset-0 opacity-50" aria-hidden="true"></div>

        <div class="relative mx-auto grid min-h-screen max-w-[1440px] lg:grid-cols-[0.72fr_1.28fr]">
            <aside class="hidden flex-col justify-between border-r border-indigo-100 bg-indigo-50/90 px-12 pt-24 pb-14 lg:flex xl:px-16">
                <div class="max-w-lg">
                    <p class="mb-5 text-xs font-black tracking-[0.18em] text-indigo-600 uppercase">Satu langkah lagi</p>
                    <h1 class="text-5xl leading-[0.94] font-black tracking-[-0.05em] uppercase xl:text-6xl">Lengkapi ruang kerja bisnis Anda.</h1>
                    <p class="mt-7 max-w-md text-lg leading-relaxed font-medium text-slate-600">
                        Akun Google Anda sudah terhubung. Tinggal beri tahu kami nama bisnis dan kategorinya.
                    </p>
                </div>

                <p class="text-xs font-bold tracking-[0.14em] text-slate-400 uppercase">Fabriku · Sistem kerja UMKM Indonesia</p>
            </aside>

            <main class="flex items-start justify-center px-4 py-8 sm:px-8 lg:px-12 lg:py-16">
                <div class="w-full max-w-2xl">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        <Link href="/" class="mb-7 flex w-fit items-center gap-2">
                            <img src="/images/fabriku-logo-only.png?v=2" alt="" class="h-9 w-14 object-contain" />
                            <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-5 w-[92px] object-contain object-left" />
                        </Link>

                        <div class="mb-7">
                            <p class="text-xs font-black tracking-[0.16em] text-indigo-600 uppercase">Buat ruang kerja</p>
                            <h2 class="mt-2 text-3xl font-black tracking-tight text-gray-900">Hampir selesai, {{ props.name }}.</h2>
                            <p class="mt-2 text-slate-500">
                                Masuk sebagai <span class="font-semibold text-gray-700">{{ props.email }}</span>
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-4">
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
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                            </div>

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
                                <p v-if="form.errors.business_name" class="mt-1 text-sm text-red-500">{{ form.errors.business_name }}</p>
                            </div>

                            <!-- Business Category -->
                            <div>
                                <label class="mb-3 block text-sm font-medium text-gray-700"> Kategori Bisnis </label>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
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
                                            <span
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                                            >
                                                <component :is="businessCategoryIcons[key as string]" :size="18" />
                                            </span>
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
                                <p v-if="form.errors.business_category" class="mt-1 text-sm text-red-500">{{ form.errors.business_category }}</p>
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
                                <p class="flex items-center justify-center gap-1.5 text-xs text-gray-500">
                                    <Gift :size="14" /> Gratis 30 hari tanpa kartu kredit • Akses penuh semua fitur
                                </p>
                                <p class="mt-2 text-xs text-gray-500">
                                    Dengan mendaftar, Anda menyetujui
                                    <Link href="/syarat-ketentuan" class="font-medium text-indigo-600 hover:underline">Syarat & Ketentuan</Link>
                                    dan
                                    <Link href="/privasi" class="font-medium text-indigo-600 hover:underline">Kebijakan Privasi</Link>
                                    kami.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.blueprint {
    background-image:
        linear-gradient(rgba(79, 70, 229, 0.055) 1px, transparent 1px), linear-gradient(90deg, rgba(79, 70, 229, 0.055) 1px, transparent 1px);
    background-size: 32px 32px;
}
</style>
