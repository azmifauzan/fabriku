<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import { Form, Link } from '@inertiajs/vue3';
import { ArrowLeft, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const form = {
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
};

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-6">
        <SeoHead title="Atur Ulang Kata Sandi - Fabriku" :noindex="true" />

        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <Link href="/" class="inline-flex min-h-11 items-center justify-center gap-2">
                        <div class="mb-2 flex items-center justify-center gap-2">
                            <img src="/images/fabriku-logo-only.png?v=3" alt="" class="h-8 w-8 object-contain" />
                            <img src="/images/fabriku-word.png?v=3" alt="Fabriku" class="h-6 object-contain" />
                        </div>
                    </Link>
                    <h1 class="mt-4 mb-2 text-xl font-bold text-gray-900">Atur ulang kata sandi</h1>
                    <p class="text-sm text-gray-600">Buat kata sandi baru untuk akun Anda.</p>
                </div>

                <Form action="/reset-password" method="post" class="space-y-4" v-slot="{ processing, errors }">
                    <input type="hidden" name="token" :value="form.token" />
                    <input type="hidden" name="email" :value="form.email" />

                    <!-- Email (Read-only) -->
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700"> Email </label>
                        <input
                            id="email"
                            type="email"
                            :value="form.email"
                            readonly
                            class="w-full cursor-not-allowed rounded-xl border border-[#858A94] bg-gray-50 px-4 py-2.5 text-gray-600"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700">Kata sandi baru</label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                v-model="form.password"
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-xl border border-[#858A94] px-4 py-2.5 pr-12 text-gray-900 placeholder-gray-500 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                :class="{ 'border-red-500': errors.password }"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute top-1/2 right-1 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                :aria-pressed="showPassword"
                            >
                                <Eye v-if="!showPassword" :size="20" />
                                <EyeOff v-else :size="20" />
                            </button>
                        </div>
                        <div v-if="errors.password" class="mt-2 text-sm text-red-700">
                            {{ errors.password }}
                        </div>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700">Ulangi kata sandi baru</label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                required
                                v-model="form.password_confirmation"
                                placeholder="Ulangi kata sandi baru"
                                class="w-full rounded-xl border border-[#858A94] px-4 py-2.5 pr-12 text-gray-900 placeholder-gray-500 transition-colors focus:border-transparent focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                            <button
                                type="button"
                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                                class="absolute top-1/2 right-1 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                :aria-label="showPasswordConfirmation ? 'Sembunyikan kata sandi konfirmasi' : 'Tampilkan kata sandi konfirmasi'"
                                :aria-pressed="showPasswordConfirmation"
                            >
                                <Eye v-if="!showPasswordConfirmation" :size="20" />
                                <EyeOff v-else :size="20" />
                            </button>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errors.email" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ errors.email }}
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="min-h-12 w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white transition-colors hover:bg-indigo-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ processing ? 'Menyimpan...' : 'Simpan kata sandi baru' }}
                    </button>
                </Form>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <Link href="/login" class="inline-flex min-h-11 items-center gap-2 text-sm text-gray-600 transition-colors hover:text-indigo-700">
                        <ArrowLeft :size="16" />
                        Kembali ke halaman masuk
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
