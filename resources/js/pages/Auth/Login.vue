<script setup lang="ts">
import { useSweetAlert } from '@/composables/useSweetAlert';
import { Form, Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { ChefHat, Eye, EyeOff, Palette, Scissors, Sparkles, Store } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const form = {
    email: '',
    password: '',
    remember: false,
};

const showPassword = ref(false);
const showDemoCredentials = ref(false);

const page = usePage();
const { showError } = useSweetAlert();
const flash = computed(() => page.props.flash as { success?: string; error?: string; warning?: string } | null);

watch(flash, (newFlash) => {
    if (newFlash?.error) {
        showError('Sesi Berakhir', newFlash.error);
    }
}, { immediate: true, deep: true });
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center px-4 py-6">
        <Head title="Masuk - Fabriku" />

        <div class="w-full max-w-md">
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
                    <p class="text-gray-600">Masuk ke akun Anda</p>
                </div>

                <Form action="/login" method="post" class="space-y-4" v-slot="{ processing, errors }">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            v-model="form.email"
                            placeholder="email@contoh.com"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            :class="{ 'border-red-500': errors.email }"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                v-model="form.password"
                                placeholder="Masukkan password"
                                class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                :class="{ 'border-red-500': errors.password || errors.email }"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                tabindex="-1"
                            >
                                <Eye v-if="!showPassword" :size="20" />
                                <EyeOff v-else :size="20" />
                            </button>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errors.email" class="text-sm text-red-500 bg-red-50 px-4 py-3 rounded-lg">
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
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Ingat saya
                            </label>
                        </div>
                        <Link
                            href="/forgot-password"
                            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium"
                        >
                            Lupa Password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="processing">Memproses...</span>
                        <span v-else>Masuk</span>
                    </button>
                </Form>

                <!-- Demo Credentials -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <button 
                        type="button"
                        @click="showDemoCredentials = !showDemoCredentials"
                        class="w-full flex flex-col items-center justify-center gap-1 cursor-pointer transition-all duration-300 py-2 focus:outline-none"
                    >
                        <div class="flex items-center gap-2 text-gray-500 hover:text-indigo-600 transition-colors" :class="{ 'text-indigo-600': showDemoCredentials }">
                            <p class="text-sm font-semibold">🎯 Demo Credentials</p>
                             <svg 
                                class="w-4 h-4 transition-transform duration-300" 
                                :class="{ 'rotate-180': showDemoCredentials }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
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
                        <div class="bg-indigo-50 text-indigo-700 text-xs p-2 rounded-lg mb-3 text-center border border-indigo-100">
                            🔄 Data demo akan direset otomatis ke kondisi awal setiap 1 jam.
                        </div>

                        <div class="space-y-1.5">
                            <!-- Retail Demo -->
                            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 hover:border-indigo-200 hover:bg-indigo-50/30 transition-colors cursor-default">
                                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-white border border-gray-200 shrink-0">
                                    <Store :size="14" class="text-gray-500" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-gray-800">Toko Serba Ada <span class="font-normal text-gray-400">· Retail</span></p>
                                    <p class="font-mono text-[11px] text-indigo-600 truncate cursor-pointer" onclick="navigator.clipboard.writeText('admin@tokoserbaada.com')">admin@tokoserbaada.com</p>
                                </div>
                                <span class="text-[10px] text-gray-400 shrink-0">password</span>
                            </div>

                            <!-- Garment Demo -->
                            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 hover:border-indigo-200 hover:bg-indigo-50/30 transition-colors cursor-default">
                                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-white border border-gray-200 shrink-0">
                                    <Scissors :size="14" class="text-gray-500" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-gray-800">Konveksi Fabriku <span class="font-normal text-gray-400">· Garment</span></p>
                                    <p class="font-mono text-[11px] text-indigo-600 truncate cursor-pointer" onclick="navigator.clipboard.writeText('admin@konveksi.com')">admin@konveksi.com</p>
                                </div>
                                <span class="text-[10px] text-gray-400 shrink-0">password</span>
                            </div>

                            <!-- Food Demo -->
                            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 hover:border-indigo-200 hover:bg-indigo-50/30 transition-colors cursor-default">
                                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-white border border-gray-200 shrink-0">
                                    <ChefHat :size="14" class="text-gray-500" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-gray-800">Kue Mama Homemade <span class="font-normal text-gray-400">· Makanan</span></p>
                                    <p class="font-mono text-[11px] text-indigo-600 truncate cursor-pointer" onclick="navigator.clipboard.writeText('admin@kuemama.com')">admin@kuemama.com</p>
                                </div>
                                <span class="text-[10px] text-gray-400 shrink-0">password</span>
                            </div>

                            <!-- Craft Demo -->
                            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 hover:border-indigo-200 hover:bg-indigo-50/30 transition-colors cursor-default">
                                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-white border border-gray-200 shrink-0">
                                    <Palette :size="14" class="text-gray-500" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-gray-800">Crafty Handmade <span class="font-normal text-gray-400">· Craft</span></p>
                                    <p class="font-mono text-[11px] text-indigo-600 truncate cursor-pointer" onclick="navigator.clipboard.writeText('admin@crafty.com')">admin@crafty.com</p>
                                </div>
                                <span class="text-[10px] text-gray-400 shrink-0">password</span>
                            </div>

                            <!-- Cosmetic Demo -->
                            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 hover:border-indigo-200 hover:bg-indigo-50/30 transition-colors cursor-default">
                                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-white border border-gray-200 shrink-0">
                                    <Sparkles :size="14" class="text-gray-500" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-gray-800">Glow Beauty Lab <span class="font-normal text-gray-400">· Kosmetik</span></p>
                                    <p class="font-mono text-[11px] text-indigo-600 truncate cursor-pointer" onclick="navigator.clipboard.writeText('admin@glowbeauty.com')">admin@glowbeauty.com</p>
                                </div>
                                <span class="text-[10px] text-gray-400 shrink-0">password</span>
                            </div>
                        </div>
                        </div>
                    </transition>
                </div>

                <!-- Registration Link -->
                <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                    <p class="text-gray-600">
                        Belum punya akun?
                        <Link href="/register" class="text-indigo-600 font-semibold hover:text-indigo-500 transition-colors">
                            Daftar di sini
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
