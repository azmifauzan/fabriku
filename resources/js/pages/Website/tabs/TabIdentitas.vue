<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import { useForm } from '@inertiajs/vue3';
import { ExternalLink, Globe, Layers, MapPin, MessageSquare, Phone, Store } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Site = {
    id: number;
    slug: string;
    mode: string;
    status: string;
    domain_status: string;
    custom_domain: string | null;
    profile: Record<string, string>;
    seo: Record<string, string> | null;
};

const props = defineProps<{
    site: Site | null;
    canManage: boolean;
}>();

const siteForm = useForm({
    slug: props.site?.slug ?? '',
    mode: (props.site?.mode ?? 'produk') as 'produk' | 'jasa' | 'gabungan',
    name: props.site?.profile?.name ?? '',
    description: props.site?.profile?.description ?? '',
    whatsapp: props.site?.profile?.whatsapp ?? '',
    address: props.site?.profile?.address ?? '',
    seo_title: props.site?.seo?.title ?? '',
    seo_description: props.site?.seo?.description ?? '',
});

const showModeChangeConfirm = ref(false);

const fullUrlPreview = computed(() => {
    const slug = siteForm.slug || 'nama-usaha';
    return `https://${slug}.fabriku.biz.id`;
});

function handleSubmit() {
    // Check if mode was changed from existing site
    if (props.site && props.site.mode && siteForm.mode !== props.site.mode) {
        showModeChangeConfirm.value = true;
        return;
    }
    submitSave();
}

function submitSave() {
    siteForm.post('/website', {
        preserveScroll: true,
        onSuccess: () => {
            showModeChangeConfirm.value = false;
        },
    });
}
</script>

<template>
    <div class="max-w-3xl space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Identitas Website</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Atur alamat, profil usaha, dan jenis katalog yang ditampilkan di website Anda.
            </p>
        </div>

        <!-- URL Preview Card -->
        <div class="rounded-xl border border-teal-200 bg-teal-50/50 p-4 dark:border-teal-900 dark:bg-teal-950/30">
            <div class="flex items-start gap-3">
                <Globe class="mt-0.5 h-5 w-5 text-teal-700 dark:text-teal-400" />
                <div class="flex-1">
                    <p class="text-xs font-semibold text-teal-900 uppercase dark:text-teal-200">Pratinjau Alamat Website</p>
                    <a
                        :href="fullUrlPreview"
                        target="_blank"
                        rel="noopener"
                        class="mt-1 inline-flex items-center gap-1 font-mono text-sm font-semibold text-teal-800 underline hover:text-teal-900 dark:text-teal-300 dark:hover:text-teal-200"
                    >
                        {{ fullUrlPreview }}
                        <ExternalLink class="h-3.5 w-3.5" />
                    </a>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                        Alamat ini dapat diakses oleh publik setelah website diterbitkan. Anda juga dapat menghubungkan domain sendiri di tab Pengaturan.
                    </p>
                </div>
            </div>
        </div>

        <form class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900" @submit.prevent="handleSubmit">
            <!-- Mode Usaha -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-900 dark:text-white">
                    Mode Usaha <span class="text-rose-500">*</span>
                </label>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Menentukan halaman katalog dan alur checkout yang aktif di website Anda.
                </p>

                <div class="grid gap-3 sm:grid-cols-3">
                    <label
                        :class="[
                            'flex cursor-pointer flex-col gap-2 rounded-xl border p-4 transition-all',
                            siteForm.mode === 'produk'
                                ? 'border-teal-600 bg-teal-50/50 ring-1 ring-teal-600 dark:border-teal-500 dark:bg-teal-950/40 dark:ring-teal-500'
                                : 'border-slate-200 hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <Store class="h-5 w-5 text-teal-700 dark:text-teal-400" />
                            <input v-model="siteForm.mode" type="radio" value="produk" class="text-teal-600 focus:ring-teal-500" />
                        </div>
                        <div>
                            <strong class="block text-sm font-semibold text-slate-900 dark:text-white">Produk Fisik</strong>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Toko online dengan keranjang & checkout pesanan.</p>
                        </div>
                    </label>

                    <label
                        :class="[
                            'flex cursor-pointer flex-col gap-2 rounded-xl border p-4 transition-all',
                            siteForm.mode === 'jasa'
                                ? 'border-teal-600 bg-teal-50/50 ring-1 ring-teal-600 dark:border-teal-500 dark:bg-teal-950/40 dark:ring-teal-500'
                                : 'border-slate-200 hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <MessageSquare class="h-5 w-5 text-teal-700 dark:text-teal-400" />
                            <input v-model="siteForm.mode" type="radio" value="jasa" class="text-teal-600 focus:ring-teal-500" />
                        </div>
                        <div>
                            <strong class="block text-sm font-semibold text-slate-900 dark:text-white">Layanan / Jasa</strong>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Katalog jasa dengan formulir prospek langsung.</p>
                        </div>
                    </label>

                    <label
                        :class="[
                            'flex cursor-pointer flex-col gap-2 rounded-xl border p-4 transition-all',
                            siteForm.mode === 'gabungan'
                                ? 'border-teal-600 bg-teal-50/50 ring-1 ring-teal-600 dark:border-teal-500 dark:bg-teal-950/40 dark:ring-teal-500'
                                : 'border-slate-200 hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <Layers class="h-5 w-5 text-teal-700 dark:text-teal-400" />
                            <input v-model="siteForm.mode" type="radio" value="gabungan" class="text-teal-600 focus:ring-teal-500" />
                        </div>
                        <div>
                            <strong class="block text-sm font-semibold text-slate-900 dark:text-white">Gabungan</strong>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Menampilkan produk dan layanan sekaligus.</p>
                        </div>
                    </label>
                </div>
                <p v-if="siteForm.errors.mode" class="text-xs text-rose-600">{{ siteForm.errors.mode }}</p>
            </div>

            <!-- Alamat Situs / Slug -->
            <div>
                <label for="slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                    Alamat Website (Subdomain) <span class="text-rose-500">*</span>
                </label>
                <div class="mt-1 flex rounded-lg shadow-2xs">
                    <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                        https://
                    </span>
                    <input
                        id="slug"
                        v-model="siteForm.slug"
                        type="text"
                        required
                        maxlength="50"
                        placeholder="nama-toko"
                        class="block w-full min-w-0 flex-1 border border-slate-300 p-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                    <span class="inline-flex items-center rounded-r-lg border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                        .fabriku.biz.id
                    </span>
                </div>
                <p v-if="siteForm.errors.slug" class="mt-1 text-xs text-rose-600">{{ siteForm.errors.slug }}</p>
            </div>

            <!-- Nama Usaha -->
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                    Nama Usaha <span class="text-rose-500">*</span>
                </label>
                <input
                    id="name"
                    v-model="siteForm.name"
                    type="text"
                    required
                    maxlength="120"
                    placeholder="Contoh: D'Garment Studio"
                    class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                />
                <p v-if="siteForm.errors.name" class="mt-1 text-xs text-rose-600">{{ siteForm.errors.name }}</p>
            </div>

            <!-- Deskripsi Singkat -->
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                    Deskripsi Singkat
                </label>
                <textarea
                    id="description"
                    v-model="siteForm.description"
                    rows="2"
                    maxlength="300"
                    placeholder="Jelaskan secara ringkas usaha Anda..."
                    class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                ></textarea>
                <p v-if="siteForm.errors.description" class="mt-1 text-xs text-rose-600">{{ siteForm.errors.description }}</p>
            </div>

            <!-- WhatsApp & Alamat -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="whatsapp" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Nomor WhatsApp Usaha
                    </label>
                    <div class="relative mt-1">
                        <Phone class="pointer-events-none absolute top-3 left-3 h-4 w-4 text-slate-400" />
                        <input
                            id="whatsapp"
                            v-model="siteForm.whatsapp"
                            type="text"
                            maxlength="32"
                            placeholder="08123456789"
                            class="block w-full rounded-lg border border-slate-300 py-2.5 pr-3 pl-9 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Digunakan untuk tombol chat WhatsApp di website.</p>
                    <p v-if="siteForm.errors.whatsapp" class="mt-1 text-xs text-rose-600">{{ siteForm.errors.whatsapp }}</p>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Alamat Fisik Usaha
                    </label>
                    <div class="relative mt-1">
                        <MapPin class="pointer-events-none absolute top-3 left-3 h-4 w-4 text-slate-400" />
                        <input
                            id="address"
                            v-model="siteForm.address"
                            type="text"
                            maxlength="500"
                            placeholder="Kota / Alamat toko fisik..."
                            class="block w-full rounded-lg border border-slate-300 py-2.5 pr-3 pl-9 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Tampil di bagian footer website.</p>
                    <p v-if="siteForm.errors.address" class="mt-1 text-xs text-rose-600">{{ siteForm.errors.address }}</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <button
                    type="submit"
                    :disabled="siteForm.processing"
                    class="rounded-lg bg-teal-800 px-5 py-2.5 text-sm font-semibold text-white shadow-2xs hover:bg-teal-700 disabled:opacity-50 dark:bg-teal-700 dark:hover:bg-teal-600"
                >
                    {{ siteForm.processing ? 'Menyimpan…' : site ? 'Simpan Identitas' : 'Buat Website Usaha' }}
                </button>
            </div>
        </form>

        <!-- Konfirmasi Ganti Mode Usaha Modal -->
        <ConfirmationModal
            :show="showModeChangeConfirm"
            title="Konfirmasi Ganti Mode Usaha"
            message="Mengganti mode usaha akan mereset desain aktif saat ini. Anda perlu memilih atau mengatur ulang desain setelahnya. Apakah Anda yakin ingin mengganti mode usaha?"
            confirm-text="Ya, Ganti Mode Usaha"
            cancel-text="Batal"
            variant="warning"
            @confirm="submitSave"
            @cancel="showModeChangeConfirm = false"
        />
    </div>
</template>
