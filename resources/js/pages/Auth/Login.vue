<script setup lang="ts">
import { useSweetAlert } from '@/composables/useSweetAlert';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { ChefHat, Eye, EyeOff, Home, Palette, RefreshCw, Scissors, Sparkles, Store, Target, Wrench } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const fillCredentials = (email: string) => {
    form.email = email;
    form.password = 'password';
};

const showPassword = ref(false);
const showDemoCredentials = ref(false);

const page = usePage();
const { showError } = useSweetAlert();
const flash = computed(() => page.props.flash as { success?: string; error?: string; warning?: string } | null);

watch(
    flash,
    (newFlash) => {
        if (newFlash?.error) {
            showError('Sesi Berakhir', newFlash.error);
        }
    },
    { immediate: true, deep: true },
);
</script>

<template>
    <div class="auth-page relative min-h-screen overflow-hidden bg-slate-50 text-gray-800">
        <Head title="Masuk - Fabriku" />
        <div class="blueprint absolute inset-0 opacity-50" aria-hidden="true"></div>

        <div class="relative mx-auto grid min-h-screen max-w-[1440px] lg:grid-cols-[0.88fr_1.12fr]">
            <aside class="hidden flex-col justify-between border-r border-indigo-100 bg-indigo-50/90 px-12 pt-24 pb-14 lg:flex xl:px-16">
                <div class="max-w-xl">
                    <p class="mb-5 text-xs font-black tracking-[0.18em] text-indigo-600 uppercase">Kembali ke alur kerja</p>
                    <h1 class="text-6xl leading-[0.92] font-black tracking-[-0.055em] uppercase xl:text-7xl">Lanjutkan yang sedang berjalan.</h1>
                    <p class="mt-7 max-w-md text-lg leading-relaxed font-medium text-slate-600">
                        Produksi, stok, pesanan, dan laporan Anda tetap tersambung dalam satu tempat.
                    </p>

                    <div class="mt-10 grid grid-cols-2 gap-3 text-sm font-bold">
                        <div class="rounded-xl border border-indigo-100 bg-white/80 p-4">01 · Bahan</div>
                        <div class="rounded-xl border border-indigo-100 bg-white/80 p-4">02 · Produksi</div>
                        <div class="rounded-xl border border-indigo-100 bg-white/80 p-4">03 · Inventory</div>
                        <div class="rounded-xl border border-indigo-100 bg-white/80 p-4">04 · Laporan</div>
                    </div>
                </div>

                <p class="text-xs font-bold tracking-[0.14em] text-slate-400 uppercase">Fabriku · Sistem kerja UMKM Indonesia</p>
            </aside>

            <main class="flex items-start justify-center px-4 py-8 sm:px-8 lg:px-12 lg:py-16">
                <div class="w-full max-w-lg">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        <Link href="/" class="mb-7 flex w-fit items-center gap-2">
                            <img src="/images/fabriku-logo-only.png?v=2" alt="" class="h-9 w-14 object-contain" />
                            <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-5 w-[92px] object-contain object-left" />
                        </Link>

                        <div class="mb-7">
                            <p class="text-xs font-black tracking-[0.16em] text-indigo-600 uppercase">Area anggota</p>
                            <h2 class="mt-2 text-3xl font-black tracking-tight text-gray-900">Selamat datang kembali.</h2>
                            <p class="mt-2 text-slate-500">Masuk untuk melanjutkan operasional bisnis Anda.</p>
                        </div>

                        <Form action="/login" method="post" class="space-y-4" v-slot="{ processing, errors }">
                            <!-- Email -->
                            <div>
                                <label for="email" class="mb-2 block text-sm font-medium text-gray-700"> Email </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    v-model="form.email"
                                    placeholder="email@contoh.com"
                                    class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                    :class="{ 'border-red-500': errors.email }"
                                />
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="mb-2 block text-sm font-medium text-gray-700"> Password </label>
                                <div class="relative">
                                    <input
                                        id="password"
                                        name="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        required
                                        v-model="form.password"
                                        placeholder="Masukkan password"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-2.5 pr-12 text-gray-900 placeholder-gray-400 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                        :class="{ 'border-red-500': errors.password || errors.email }"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                        :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                        :aria-pressed="showPassword"
                                    >
                                        <Eye v-if="!showPassword" :size="20" />
                                        <EyeOff v-else :size="20" />
                                    </button>
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div v-if="errors.email" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-500">
                                {{ errors.email }}
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input
                                        id="remember"
                                        name="remember"
                                        type="checkbox"
                                        v-model="form.remember"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <label for="remember" class="ml-2 block text-sm text-gray-700"> Ingat saya </label>
                                </div>
                                <Link href="/forgot-password" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                    Lupa Password?
                                </Link>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                :disabled="processing"
                                class="w-full rounded-xl bg-indigo-600 py-3 font-semibold text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span v-if="processing">Memproses...</span>
                                <span v-else>Masuk</span>
                            </button>
                        </Form>

                        <!-- Demo Credentials -->
                        <div class="mt-6 border-t border-gray-200 pt-4">
                            <button
                                type="button"
                                @click="showDemoCredentials = !showDemoCredentials"
                                class="flex w-full cursor-pointer flex-col items-center justify-center gap-1 py-2 transition-all duration-300 focus:outline-none"
                            >
                                <div
                                    class="flex items-center gap-2 text-gray-500 transition-colors hover:text-indigo-600"
                                    :class="{ 'text-indigo-600': showDemoCredentials }"
                                >
                                    <p class="flex items-center gap-1.5 text-sm font-semibold"><Target :size="14" /> Demo Credentials</p>
                                    <svg
                                        class="h-4 w-4 transition-transform duration-300"
                                        :class="{ 'rotate-180': showDemoCredentials }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                <p class="text-[10px] text-gray-400" v-show="!showDemoCredentials">Klik untuk melihat akun demo</p>
                            </button>

                            <transition
                                enter-active-class="transition-all duration-300 ease-out"
                                enter-from-class="max-h-0 opacity-0 transform -translate-y-2"
                                enter-to-class="max-h-[600px] opacity-100 transform translate-y-0"
                                leave-active-class="transition-all duration-200 ease-in"
                                leave-from-class="max-h-[600px] opacity-100 transform translate-y-0"
                                leave-to-class="max-h-0 opacity-0 transform -translate-y-2"
                            >
                                <div v-show="showDemoCredentials" class="overflow-hidden">
                                    <div
                                        class="mb-3 flex items-center justify-center gap-1.5 rounded-lg border border-indigo-100 bg-indigo-50 p-2 text-center text-xs text-indigo-700"
                                    >
                                        <RefreshCw :size="12" />
                                        Data demo akan direset otomatis ke kondisi awal setiap 1 jam.
                                    </div>

                                    <div class="space-y-1.5">
                                        <!-- Retail Demo -->
                                        <div
                                            @click="fillCredentials('admin@tokoserbaada.com')"
                                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors select-none hover:border-indigo-200 hover:bg-indigo-50/30"
                                        >
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white">
                                                <Store :size="14" class="text-gray-500" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold text-gray-800">
                                                    Toko Serba Ada <span class="font-normal text-gray-400">· Retail</span>
                                                </p>
                                                <p class="truncate font-mono text-[11px] text-indigo-600">admin@tokoserbaada.com</p>
                                            </div>
                                            <span class="shrink-0 text-[10px] text-gray-400">password</span>
                                        </div>

                                        <!-- Garment Demo -->
                                        <div
                                            @click="fillCredentials('admin@konveksi.com')"
                                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors select-none hover:border-indigo-200 hover:bg-indigo-50/30"
                                        >
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white">
                                                <Scissors :size="14" class="text-gray-500" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold text-gray-800">
                                                    Konveksi Fabriku <span class="font-normal text-gray-400">· Garment</span>
                                                </p>
                                                <p class="truncate font-mono text-[11px] text-indigo-600">admin@konveksi.com</p>
                                            </div>
                                            <span class="shrink-0 text-[10px] text-gray-400">password</span>
                                        </div>

                                        <!-- Food Demo -->
                                        <div
                                            @click="fillCredentials('admin@kuemama.com')"
                                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors select-none hover:border-indigo-200 hover:bg-indigo-50/30"
                                        >
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white">
                                                <ChefHat :size="14" class="text-gray-500" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold text-gray-800">
                                                    Kue Mama Homemade <span class="font-normal text-gray-400">· Makanan</span>
                                                </p>
                                                <p class="truncate font-mono text-[11px] text-indigo-600">admin@kuemama.com</p>
                                            </div>
                                            <span class="shrink-0 text-[10px] text-gray-400">password</span>
                                        </div>

                                        <!-- Craft Demo -->
                                        <div
                                            @click="fillCredentials('admin@crafty.com')"
                                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors select-none hover:border-indigo-200 hover:bg-indigo-50/30"
                                        >
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white">
                                                <Palette :size="14" class="text-gray-500" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold text-gray-800">
                                                    Crafty Handmade <span class="font-normal text-gray-400">· Craft</span>
                                                </p>
                                                <p class="truncate font-mono text-[11px] text-indigo-600">admin@crafty.com</p>
                                            </div>
                                            <span class="shrink-0 text-[10px] text-gray-400">password</span>
                                        </div>

                                        <!-- Cosmetic Demo -->
                                        <div
                                            @click="fillCredentials('admin@glowbeauty.com')"
                                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors select-none hover:border-indigo-200 hover:bg-indigo-50/30"
                                        >
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white">
                                                <Sparkles :size="14" class="text-gray-500" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold text-gray-800">
                                                    Glow Beauty Lab <span class="font-normal text-gray-400">· Kosmetik</span>
                                                </p>
                                                <p class="truncate font-mono text-[11px] text-indigo-600">admin@glowbeauty.com</p>
                                            </div>
                                            <span class="shrink-0 text-[10px] text-gray-400">password</span>
                                        </div>

                                        <!-- Homemade Demo -->
                                        <div
                                            @click="fillCredentials('admin@homemade.com')"
                                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors select-none hover:border-indigo-200 hover:bg-indigo-50/30"
                                        >
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white">
                                                <Home :size="14" class="text-gray-500" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold text-gray-800">
                                                    Dapur Coklat Rumahan <span class="font-normal text-gray-400">· Produksi Rumahan</span>
                                                </p>
                                                <p class="truncate font-mono text-[11px] text-indigo-600">admin@homemade.com</p>
                                            </div>
                                            <span class="shrink-0 text-[10px] text-gray-400">password</span>
                                        </div>

                                        <!-- Service Demo -->
                                        <div
                                            @click="fillCredentials('admin@bengkel.com')"
                                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors select-none hover:border-indigo-200 hover:bg-indigo-50/30"
                                        >
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white">
                                                <Wrench :size="14" class="text-gray-500" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold text-gray-800">
                                                    Bengkel Motor Maju Jaya <span class="font-normal text-gray-400">· Jasa</span>
                                                </p>
                                                <p class="truncate font-mono text-[11px] text-indigo-600">admin@bengkel.com</p>
                                            </div>
                                            <span class="shrink-0 text-[10px] text-gray-400">password</span>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- Registration Link -->
                        <div class="mt-4 border-t border-gray-200 pt-4 text-center">
                            <p class="text-gray-600">
                                Belum punya akun?
                                <Link href="/register" class="font-semibold text-indigo-600 transition-colors hover:text-indigo-500">
                                    Daftar di sini
                                </Link>
                            </p>
                            <p class="mt-3 text-xs text-gray-400">
                                <Link href="/privasi" class="hover:underline">Kebijakan Privasi</Link>
                                ·
                                <Link href="/syarat-ketentuan" class="hover:underline">Syarat & Ketentuan</Link>
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
