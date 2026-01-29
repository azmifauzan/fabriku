<template>
    <AppLayout>
        <Head title="Pengaturan" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    title="Pengaturan"
                    description="Kelola pengaturan aplikasi"
                />

                <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Invoice Settings</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atur informasi yang akan tampil pada invoice.</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                             <!-- Logo Upload -->
                             <div class="col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Logo Perusahaan</label>
                                <div class="flex items-center gap-6">
                                    <div v-if="previewUrl || form.settings.company_logo" class="h-24 w-24 shrink-0 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                        <img :src="previewUrl || form.settings.company_logo" alt="Logo Preview" class="h-full w-full object-contain p-2" />
                                    </div>
                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            @change="handleFileChange"
                                            accept="image/*"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-400 dark:file:bg-indigo-900/20 dark:file:text-indigo-400"
                                        />
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF up to 2MB</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Company Name -->
                            <div class="col-span-2 md:col-span-1">
                                <label for="company_name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Perusahaan</label>
                                <input
                                    id="company_name"
                                    v-model="form.settings.company_name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Contoh: PT. Fabriku Sukses"
                                />
                            </div>

                             <!-- Company Email -->
                             <div class="col-span-2 md:col-span-1">
                                <label for="company_email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Email Perusahaan</label>
                                <input
                                    id="company_email"
                                    v-model="form.settings.company_email"
                                    type="email"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="email@perusahaan.com"
                                />
                            </div>

                            <!-- Company Address -->
                            <div class="col-span-2">
                                <label for="company_address" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Perusahaan</label>
                                <textarea
                                    id="company_address"
                                    v-model="form.settings.company_address"
                                    rows="3"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Alamat lengkap perusahaan..."
                                ></textarea>
                            </div>

                            <!-- Company Phone -->
                            <div class="col-span-2 md:col-span-1">
                                <label for="company_phone" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Telepon Perusahaan</label>
                                <input
                                    id="company_phone"
                                    v-model="form.settings.company_phone"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="081234567890"
                                />
                            </div>

                            <!-- Invoice Footer Text -->
                            <div class="col-span-2">
                                <label for="invoice_footer_text" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Kaki Invoice (Footer)</label>
                                <textarea
                                    id="invoice_footer_text"
                                    v-model="form.settings.invoice_footer_text"
                                    rows="2"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Contoh: Thank you for your business!"
                                ></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:opacity-50"
                            >
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useSweetAlert } from '@/composables/useSweetAlert';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
});

const { showSuccess } = useSweetAlert();
const previewUrl = ref<string | null>(null);

const form = useForm({
    settings: {
        company_name: props.settings?.company_name || '',
        company_address: props.settings?.company_address || '',
        company_phone: props.settings?.company_phone || '',
        company_email: props.settings?.company_email || '',
        invoice_footer_text: props.settings?.invoice_footer_text || '',
        company_logo: props.settings?.company_logo || null,
    },
    company_logo: null as File | null,
});

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.company_logo = target.files[0];
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrl.value = e.target?.result as string;
        };
        reader.readAsDataURL(target.files[0]);
    }
}

function submit() {
    form.post('/settings', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
             showSuccess('Berhasil!', 'Pengaturan berhasil disimpan.');
             // Clear file input needed? handled by Inertia normally
        },
    });
}
</script>
