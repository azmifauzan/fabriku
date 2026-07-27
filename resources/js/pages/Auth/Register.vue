<script setup lang="ts">
import GoogleAuthButton from '@/components/GoogleAuthButton.vue';
import { businessCategoryIcons } from '@/lib/businessCategoryIcons';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Gift } from 'lucide-vue-next';
import { ref } from 'vue';

interface Category {
    label: string;
    icon: string;
    description: string;
}

defineProps<{
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
    <div class="auth-page relative min-h-screen overflow-hidden bg-slate-50 text-gray-800">
        <Head title="Daftar - Fabriku" />
        <div class="blueprint absolute inset-0 opacity-50" aria-hidden="true"></div>

        <div class="relative mx-auto grid min-h-screen max-w-[1440px] lg:grid-cols-[0.72fr_1.28fr]">
            <aside class="hidden flex-col justify-between border-r border-indigo-100 bg-indigo-50/90 px-12 pt-24 pb-14 lg:flex xl:px-16">
                <div class="max-w-lg">
                    <p class="mb-5 text-xs font-black tracking-[0.18em] text-indigo-600 uppercase">30 hari akses penuh</p>
                    <h1 class="text-5xl leading-[0.94] font-black tracking-[-0.05em] uppercase xl:text-6xl">
                        Mulai dari proses yang paling perlu dirapikan.
                    </h1>
                    <p class="mt-7 max-w-md text-lg leading-relaxed font-medium text-slate-600">
                        Buat ruang kerja bisnis Anda, pilih kategori, lalu mulai mencatat dengan alur yang sesuai.
                    </p>

                    <ul class="mt-9 space-y-3 text-sm font-bold text-slate-700">
                        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-indigo-500"></span>Tanpa kartu kredit</li>
                        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-cyan-500"></span>Semua modul langsung terbuka</li>
                        <li class="flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>Data tetap dapat dibaca setelah trial
                        </li>
                    </ul>
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
                            <h2 class="mt-2 text-3xl font-black tracking-tight text-gray-900">Mulai dengan bisnis Anda.</h2>
                            <p class="mt-2 text-slate-500">Isi data dasar berikut. Pengaturan lain bisa dilengkapi nanti.</p>
                        </div>

                        <!-- Google Register -->
                        <GoogleAuthButton label="Daftar dengan Google" />

                        <div class="my-6 flex items-center gap-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-xs font-semibold tracking-wide text-gray-400 uppercase">atau</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
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
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                                            :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                            :aria-pressed="showPassword"
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
                                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700">
                                        Konfirmasi Password
                                    </label>
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
                                            :aria-label="
                                                showPasswordConfirmation ? 'Sembunyikan konfirmasi password' : 'Tampilkan konfirmasi password'
                                            "
                                            :aria-pressed="showPasswordConfirmation"
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

                        <!-- Login Link -->
                        <div class="mt-4 border-t border-gray-200 pt-4 text-center">
                            <p class="text-gray-600">
                                Sudah punya akun?
                                <Link href="/login" class="font-semibold text-indigo-600 transition-colors hover:text-indigo-500">
                                    Masuk di sini
                                </Link>
                            </p>
                        </div>
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
