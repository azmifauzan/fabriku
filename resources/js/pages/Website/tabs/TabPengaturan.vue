<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import {
    Copy,
    FileText,
    Globe,
    RefreshCw,
    Search,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
import { ref } from 'vue';

type Site = {
    id: number;
    slug: string;
    mode: string;
    status: string;
    domain_status: string;
    custom_domain: string | null;
    ga_measurement_id: string | null;
    profile: Record<string, string>;
    seo: Record<string, string> | null;
    recipients: Array<{ id: number; name: string }>;
};

type ContentPage = {
    id: number;
    slug: string;
    title: string;
    seo_title: string | null;
    seo_description: string | null;
    show_in_footer: boolean;
};

type User = {
    id: number;
    name: string;
    email: string;
};

type DomainRecord = {
    type: string;
    name: string;
    value: string;
};

const props = defineProps<{
    site: Site;
    domainRecords: DomainRecord[];
    cnameTarget: string;
    users: User[];
    contentPages: ContentPage[];
}>();

const activeSubSection = ref<'domain' | 'seo' | 'pages' | 'team'>('domain');

// Domain Form
const domainForm = useForm({
    domain: props.site.custom_domain ?? '',
});

function submitDomain() {
    domainForm.post('/website/domain', { preserveScroll: true });
}

function syncDomain() {
    router.post('/website/domain/sync', {}, { preserveScroll: true });
}

// SEO Form (using save endpoint on site)
const seoForm = useForm({
    slug: props.site.slug,
    mode: props.site.mode,
    name: props.site.profile?.name ?? '',
    description: props.site.profile?.description ?? '',
    whatsapp: props.site.profile?.whatsapp ?? '',
    address: props.site.profile?.address ?? '',
    seo_title: props.site.seo?.title ?? '',
    seo_description: props.site.seo?.description ?? '',
});

function submitSeo() {
    seoForm.post('/website', { preserveScroll: true });
}

const analyticsForm = useForm({
    ga_measurement_id: props.site.ga_measurement_id ?? '',
});

function submitAnalytics() {
    analyticsForm.post('/website/analytics', { preserveScroll: true });
}

// Content Pages Forms
const pageForms = ref<Record<number, { seo_title: string; seo_description: string; show_in_footer: boolean }>>(
    Object.fromEntries(
        props.contentPages.map((cp) => [
            cp.id,
            {
                seo_title: cp.seo_title ?? '',
                seo_description: cp.seo_description ?? '',
                show_in_footer: cp.show_in_footer,
            },
        ]),
    ),
);

function savePage(cp: ContentPage) {
    router.patch(`/website/pages/${cp.id}`, pageForms.value[cp.id], { preserveScroll: true });
}

// Team Recipients Form
const recipientForm = useForm({
    user_ids: props.site.recipients?.map((r) => r.id) ?? ([] as number[]),
});

function submitRecipients() {
    recipientForm.post('/website/recipients', { preserveScroll: true });
}

function copyToClipboard(text: string) {
    navigator.clipboard.writeText(text);
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Pengaturan Website</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Kelola domain kustom, konfigurasi SEO mesin pencari, halaman tambahan, dan penerima notifikasi.
            </p>
        </div>

        <!-- Sub-menu Navigasi -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-slate-800">
            <button
                v-for="sub in [
                    { id: 'domain', label: 'Domain Kustom', icon: Globe },
                    { id: 'seo', label: 'SEO Google', icon: Search },
                    { id: 'pages', label: 'Halaman Tambahan', icon: FileText },
                    { id: 'team', label: 'Tim & Notifikasi', icon: Users },
                ]"
                :key="sub.id"
                type="button"
                :class="[
                    'inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-semibold transition-all',
                    activeSubSection === sub.id
                        ? 'bg-teal-800 text-white shadow-2xs dark:bg-teal-700'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white',
                ]"
                @click="activeSubSection = sub.id as any"
            >
                <component :is="sub.icon" class="h-4 w-4" />
                {{ sub.label }}
                <span
                    v-if="sub.id === 'pages' && contentPages.length > 0"
                    class="ml-1 rounded-full bg-slate-200 px-1.5 py-0.2 text-[10px] text-slate-800 dark:bg-slate-700 dark:text-slate-200"
                >
                    {{ contentPages.length }}
                </span>
            </button>
        </div>

        <!-- Bagian 1: Domain Kustom -->
        <section v-if="activeSubSection === 'domain'" class="max-w-3xl space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Hubungkan Domain Sendiri</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Gunakan alamat domain milik Anda (contoh: <code class="font-mono">tokoanda.com</code>) alih-alih subdomain Fabriku.
                </p>

                <form class="mt-4 space-y-4" @submit.prevent="submitDomain">
                    <div>
                        <label for="custom_domain" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Nama Domain Anda
                        </label>
                        <div class="mt-1 flex gap-2">
                            <input
                                id="custom_domain"
                                v-model="domainForm.domain"
                                type="text"
                                placeholder="tokoanda.com"
                                class="block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                            <button
                                type="submit"
                                :disabled="domainForm.processing"
                                class="shrink-0 rounded-lg bg-teal-800 px-4 py-2.5 text-sm font-semibold text-white shadow-2xs hover:bg-teal-700 disabled:opacity-50 dark:bg-teal-700 dark:hover:bg-teal-600"
                            >
                                Simpan Domain
                            </button>
                        </div>
                        <p v-if="domainForm.errors.domain" class="mt-1 text-xs text-rose-600">{{ domainForm.errors.domain }}</p>
                    </div>
                </form>

                <!-- Status Domain Saat Ini -->
                <div v-if="site.custom_domain" class="mt-6 border-t border-slate-100 pt-5 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <strong class="font-mono text-sm text-slate-900 dark:text-white">{{ site.custom_domain }}</strong>
                            <span
                                :class="[
                                    'rounded-md px-2 py-0.5 text-xs font-semibold',
                                    site.domain_status === 'active'
                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                ]"
                            >
                                {{ site.domain_status === 'active' ? 'Aktif & Terhubung' : 'Menunggu Verifikasi DNS' }}
                            </span>
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="syncDomain"
                        >
                            <RefreshCw class="h-3.5 w-3.5 text-teal-700 dark:text-teal-400" />
                            Cek Status DNS
                        </button>
                    </div>

                    <!-- Instruksi DNS Records -->
                    <div class="mt-4 space-y-3">
                        <p class="text-xs font-medium text-slate-700 dark:text-slate-300">
                            Konfigurasikan DNS di penyedia domain Anda:
                        </p>

                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-left text-xs">
                                <thead class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-600 uppercase dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    <tr>
                                        <th class="p-2.5">Tipe</th>
                                        <th class="p-2.5">Nama / Host</th>
                                        <th class="p-2.5">Nilai / Target</th>
                                        <th class="p-2.5 text-right">Salin</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-mono text-slate-700 dark:divide-slate-800 dark:text-slate-300">
                                    <!-- CNAME Record -->
                                    <tr>
                                        <td class="p-2.5 font-bold text-teal-700">CNAME</td>
                                        <td class="p-2.5">@ atau {{ site.custom_domain }}</td>
                                        <td class="p-2.5">{{ cnameTarget }}</td>
                                        <td class="p-2.5 text-right">
                                            <button
                                                type="button"
                                                class="rounded p-1 hover:bg-slate-100 dark:hover:bg-slate-800"
                                                title="Salin target"
                                                @click="copyToClipboard(cnameTarget)"
                                            >
                                                <Copy class="h-3.5 w-3.5" />
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Verifikasi TXT Records -->
                                    <tr v-for="(rec, rIdx) in domainRecords" :key="rIdx">
                                        <td class="p-2.5 font-bold text-indigo-700">{{ rec.type }}</td>
                                        <td class="max-w-[150px] truncate p-2.5" :title="rec.name">{{ rec.name }}</td>
                                        <td class="max-w-[200px] truncate p-2.5" :title="rec.value">{{ rec.value }}</td>
                                        <td class="p-2.5 text-right">
                                            <button
                                                type="button"
                                                class="rounded p-1 hover:bg-slate-100 dark:hover:bg-slate-800"
                                                title="Salin nilai"
                                                @click="copyToClipboard(rec.value)"
                                            >
                                                <Copy class="h-3.5 w-3.5" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bagian 2: SEO Google -->
        <section v-if="activeSubSection === 'seo'" class="max-w-3xl space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Pengaturan SEO Website</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Bantu Google dan calon pelanggan menemukan usaha Anda melalui mesin pencari dan media sosial.
                </p>

                <!-- Pratinjau Google Search -->
                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <p class="text-xs font-semibold text-slate-500 uppercase dark:text-slate-400">Pratinjau di Google</p>
                    <div class="mt-2 space-y-1 font-sans">
                        <div class="text-xs text-slate-500">https://{{ site.slug }}.fabriku.biz.id</div>
                        <h4 class="text-base font-semibold text-blue-700 hover:underline dark:text-blue-400">
                            {{ seoForm.seo_title || site.profile?.name || 'Nama Toko Anda' }}
                        </h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            {{ seoForm.seo_description || site.profile?.description || 'Deskripsi singkat usaha Anda akan tampil di sini saat dicari di Google.' }}
                        </p>
                    </div>
                </div>

                <form class="mt-5 space-y-4" @submit.prevent="submitSeo">
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="seo_title" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Judul SEO Halaman Depan
                            </label>
                            <span class="text-xs text-slate-400">{{ seoForm.seo_title.length }}/70 karakter</span>
                        </div>
                        <input
                            id="seo_title"
                            v-model="seoForm.seo_title"
                            type="text"
                            maxlength="70"
                            placeholder="Contoh: Toko Mukena Cantik & Modis di Bandung"
                            class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <p v-if="seoForm.errors.seo_title" class="mt-1 text-xs text-rose-600">{{ seoForm.errors.seo_title }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="seo_description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Deskripsi SEO
                            </label>
                            <span class="text-xs text-slate-400">{{ seoForm.seo_description.length }}/160 karakter</span>
                        </div>
                        <textarea
                            id="seo_description"
                            v-model="seoForm.seo_description"
                            rows="3"
                            maxlength="160"
                            placeholder="Jelaskan produk unggulan, lokasi, atau penawaran menarik..."
                            class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                        <p v-if="seoForm.errors.seo_description" class="mt-1 text-xs text-rose-600">{{ seoForm.errors.seo_description }}</p>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            :disabled="seoForm.processing"
                            class="rounded-lg bg-teal-800 px-5 py-2 text-sm font-semibold text-white shadow-2xs hover:bg-teal-700 disabled:opacity-50 dark:bg-teal-700 dark:hover:bg-teal-600"
                        >
                            Simpan SEO
                        </button>
                    </div>
                </form>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Google Analytics 4</h3>
                <p id="ga-help" class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Masukkan ID pengukuran GA4 untuk melihat statistik kunjungan toko. Kosongkan untuk mematikan pelacakan.
                </p>
                <form class="mt-4 space-y-4" @submit.prevent="submitAnalytics">
                    <div>
                        <label for="ga_measurement_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">ID pengukuran GA4</label>
                        <input
                            id="ga_measurement_id"
                            v-model="analyticsForm.ga_measurement_id"
                            type="text"
                            maxlength="22"
                            placeholder="G-XXXXXXXXXX"
                            autocomplete="off"
                            spellcheck="false"
                            aria-describedby="ga-help ga-error"
                            :aria-invalid="!!analyticsForm.errors.ga_measurement_id"
                            class="mt-1 block min-h-11 w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <p id="ga-error" class="mt-1 text-sm text-rose-600" role="alert">{{ analyticsForm.errors.ga_measurement_id }}</p>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Gunakan ID dari Google Analytics → Admin → Aliran data → Web. Tidak perlu menempelkan kode skrip.</p>
                    <button
                        type="submit"
                        :disabled="analyticsForm.processing"
                        class="min-h-11 cursor-pointer rounded-lg bg-teal-800 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700 disabled:cursor-wait disabled:opacity-50 dark:bg-teal-700 dark:hover:bg-teal-600"
                    >
                        {{ analyticsForm.processing ? 'Menyimpan…' : 'Simpan Google Analytics' }}
                    </button>
                    <p v-if="analyticsForm.recentlySuccessful" class="text-sm text-teal-800 dark:text-teal-300" role="status">Pengaturan Google Analytics disimpan.</p>
                </form>
            </div>
        </section>

        <!-- Bagian 3: Halaman Tambahan -->
        <section v-if="activeSubSection === 'pages'" class="max-w-3xl space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Halaman Tambahan</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Halaman statis yang dibuat melalui wizard Satsetui (seperti Tentang Kami, Syarat & Ketentuan, dsb).
                </p>

                <div v-if="contentPages.length === 0" class="py-8 text-center text-slate-500">
                    <p class="text-sm">Belum ada halaman tambahan yang dibuat.</p>
                    <p class="mt-1 text-xs text-slate-400">Halaman tambahan dibuat otomatis saat Anda mengekspor template dari Satsetui.</p>
                </div>

                <div v-else class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <div v-for="cp in contentPages" :key="cp.id" class="space-y-3 py-4 first:pt-0 last:pb-0">
                        <div class="flex items-center justify-between">
                            <div>
                                <strong class="text-sm font-semibold text-slate-900 dark:text-white">{{ cp.title }}</strong>
                                <a
                                    :href="`https://${site.slug}.fabriku.biz.id/halaman/${cp.slug}`"
                                    target="_blank"
                                    rel="noopener"
                                    class="ml-2 font-mono text-xs text-teal-700 hover:underline dark:text-teal-400"
                                >
                                    /halaman/{{ cp.slug }} ↗
                                </a>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">Judul SEO Halaman</label>
                                <input
                                    v-model="pageForms[cp.id].seo_title"
                                    type="text"
                                    maxlength="70"
                                    class="mt-1 block w-full rounded-md border border-slate-300 p-2 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">Deskripsi SEO Halaman</label>
                                <input
                                    v-model="pageForms[cp.id].seo_description"
                                    type="text"
                                    maxlength="160"
                                    class="mt-1 block w-full rounded-md border border-slate-300 p-2 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                                <input
                                    v-model="pageForms[cp.id].show_in_footer"
                                    type="checkbox"
                                    class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                />
                                Tampilkan link di footer website
                            </label>

                            <button
                                type="button"
                                class="rounded-md border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                                @click="savePage(cp)"
                            >
                                Simpan Halaman
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bagian 4: Tim & Notifikasi -->
        <section v-if="activeSubSection === 'team'" class="max-w-3xl space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Penerima Notifikasi Permintaan</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Pilih anggota tim yang akan mendapatkan notifikasi email saat ada prospek baru yang masuk dari website.
                </p>

                <form class="mt-4 space-y-4" @submit.prevent="submitRecipients">
                    <div v-if="users.length === 0" class="py-4 text-sm text-slate-500">
                        Tidak ada pengguna aktif lain di tim Anda.
                    </div>

                    <div v-else class="space-y-2">
                        <label
                            v-for="u in users"
                            :key="u.id"
                            class="flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 p-3 text-sm hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50"
                        >
                            <div class="flex items-center gap-3">
                                <input
                                    v-model="recipientForm.user_ids"
                                    type="checkbox"
                                    :value="u.id"
                                    class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                />
                                <div>
                                    <strong class="text-slate-900 dark:text-white">{{ u.name }}</strong>
                                    <p class="text-xs text-slate-500">{{ u.email }}</p>
                                </div>
                            </div>
                            <ShieldCheck v-if="recipientForm.user_ids.includes(u.id)" class="h-4 w-4 text-teal-600" />
                        </label>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            :disabled="recipientForm.processing"
                            class="rounded-lg bg-teal-800 px-5 py-2 text-sm font-semibold text-white shadow-2xs hover:bg-teal-700 disabled:opacity-50 dark:bg-teal-700 dark:hover:bg-teal-600"
                        >
                            Simpan Penerima
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</template>
