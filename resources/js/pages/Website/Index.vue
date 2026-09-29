<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Product = { id: number; product_code: string; slug: string; title: string; description: string | null; is_visible: boolean };
type Service = { id: number; name: string; slug: string | null; is_public: boolean; is_active: boolean };
type ThemeVersion = {
    id: number;
    version: number;
    created_at: string;
    theme: { preset?: string; css_variables?: Record<string, string> };
    sections: Array<{ type: string; variables?: Record<string, string> }>;
};
type Site = {
    id: number;
    slug: string;
    mode: string;
    status: string;
    domain_status: string;
    custom_domain: string | null;
    setup_completed_at: string | null;
    profile: Record<string, string>;
    seo: Record<string, string> | null;
    active_theme_version: ThemeVersion | null;
    theme_versions: ThemeVersion[];
    recipients: Array<{ id: number; name: string }>;
};
type Tab = 'permintaan' | 'identitas' | 'katalog' | 'desain' | 'pengaturan';
type Lead = {
    id: number;
    name: string;
    phone: string;
    message: string | null;
    status: string;
    service_id: number | null;
    sales_order_id: number | null;
    created_at: string;
    service?: { name: string };
};
type Order = {
    id: number;
    order_number: string;
    status: string;
    payment_status: string;
    total_amount: string;
    created_at: string;
    customer: { name: string; phone: string };
};
const props = defineProps<{
    site: Site | null;
    canManage: boolean;
    domainRecords: Array<{ type: string; name: string; value: string }>;
    cnameTarget: string;
    catalog: Array<{ product_code: string; product_name: string }>;
    products: Product[];
    services: Service[];
    users: Array<{ id: number; name: string; email: string }>;
    leads: Lead[];
    orders: Order[];
}>();
const page = usePage();
const success = computed(() => (page.props.flash as { success?: string } | undefined)?.success);
const siteForm = useForm({
    slug: props.site?.slug ?? '',
    mode: props.site?.mode ?? 'produk',
    name: props.site?.profile?.name ?? '',
    description: props.site?.profile?.description ?? '',
    whatsapp: props.site?.profile?.whatsapp ?? '',
    address: props.site?.profile?.address ?? '',
    seo_title: props.site?.seo?.title ?? '',
    seo_description: props.site?.seo?.description ?? '',
});
const activeTab = ref<Tab>('permintaan');
const setupActive = ref(!props.site || !props.site.setup_completed_at);
const setupStep = ref<1 | 2 | 3>(!props.site ? 1 : props.site.active_theme_version ? 3 : 2);
const tabs: Array<{ id: Tab; label: string }> = [
    { id: 'permintaan', label: 'Permintaan masuk' },
    { id: 'identitas', label: 'Identitas' },
    { id: 'katalog', label: 'Katalog' },
    { id: 'desain', label: 'Desain' },
    { id: 'pengaturan', label: 'Pengaturan' },
];
const hero = props.site?.active_theme_version?.sections?.find((s) => s.type === 'hero')?.variables;
const colors = props.site?.active_theme_version?.theme?.css_variables ?? {};
const themeForm = useForm({
    primary: colors['--fb-primary'] ?? '#2563eb',
    accent: colors['--fb-accent'] ?? '#3b82f6',
    background: colors['--fb-bg'] ?? '#ffffff',
    text: colors['--fb-text'] ?? '#0f172a',
    hero_title: hero?.['hero-title'] ?? props.site?.profile?.name ?? '',
    hero_subtitle: hero?.['hero-subtitle'] ?? props.site?.profile?.description ?? '',
    preset: props.site?.active_theme_version?.theme?.preset ?? 'ruang',
});
const productForm = useForm({ product_code: '', slug: '', title: '', description: '', is_visible: true });
const recipientForm = useForm({ user_ids: props.site?.recipients?.map((r) => r.id) ?? ([] as number[]) });
const domainForm = useForm({ domain: props.site?.custom_domain ?? '' });
const selectedCode = ref('');
function selectProduct(code: string) {
    selectedCode.value = code;
    const existing = props.products.find((p) => p.product_code === code);
    const source = props.catalog.find((p) => p.product_code === code);
    productForm.product_code = code;
    productForm.title = existing?.title ?? source?.product_name ?? '';
    productForm.slug =
        existing?.slug ??
        (source?.product_name ?? code)
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
    productForm.description = existing?.description ?? '';
    productForm.is_visible = existing?.is_visible ?? true;
}
function saveService(service: Service) {
    router.post(
        `/website/services/${service.id}`,
        { slug: service.slug || service.name.toLowerCase().replace(/[^a-z0-9]+/g, '-'), is_public: service.is_public ? 1 : 0 },
        { preserveScroll: true },
    );
}
function publish(status: 'published' | 'paused') {
    router.post('/website/publish', { status }, { preserveScroll: true });
}
function saveIdentity() {
    const creating = !props.site;
    siteForm.post('/website', {
        preserveScroll: true,
        onSuccess: () => {
            if (creating) {
                themeForm.hero_title = siteForm.name;
                themeForm.hero_subtitle = siteForm.description;
                setupStep.value = 2;
                activeTab.value = 'desain';
            }
        },
    });
}
function saveTheme() {
    themeForm.post('/website/theme', {
        preserveScroll: true,
        onSuccess: () => {
            if (setupActive.value) {
                setupStep.value = 3;
                activeTab.value = 'katalog';
            }
        },
    });
}
function completeSetup() {
    router.post(
        '/website/setup/complete',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                setupActive.value = false;
                activeTab.value = 'permintaan';
            },
        },
    );
}
function closeLead(lead: Lead) {
    const reason = window.prompt('Alasan menutup prospek ini:');
    if (reason?.trim()) router.post(`/website/leads/${lead.id}/status`, { status: 'closed', closed_reason: reason.trim() }, { preserveScroll: true });
}
const money = (n: string | number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(n));
const whatsappLink = (phone: string, message: string) =>
    `https://wa.me/${phone.replace(/\D/g, '').replace(/^0/, '62')}?text=${encodeURIComponent(message)}`;
</script>

<template>
    <AppLayout>
        <Head title="Website Usaha" />
        <main class="mx-auto max-w-6xl space-y-8 px-4 py-6 sm:px-6 sm:py-9">
            <header class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold tracking-wider text-teal-700 uppercase">Website Usaha</p>
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Satu alamat untuk usaha Anda</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-300">
                        Tampilkan produk atau layanan, terima permintaan, lalu tindak lanjuti langsung dari Fabriku.
                    </p>
                </div>
                <a
                    v-if="site?.status === 'published'"
                    :href="`https://${site.slug}.fabriku.biz.id`"
                    target="_blank"
                    rel="noopener"
                    class="rounded-lg border border-slate-400 px-4 py-2 text-sm font-semibold text-slate-900 dark:text-white"
                    >Lihat website ↗</a
                >
            </header>
            <p v-if="success" role="status" class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ success }}
            </p>
            <nav
                v-if="site && !setupActive"
                aria-label="Bagian halaman"
                class="flex flex-wrap gap-2 border-b border-slate-200 pb-4 text-sm dark:border-slate-700"
            >
                <button
                    v-for="section in tabs"
                    :key="section.id"
                    type="button"
                    :class="[
                        'rounded-full border px-3 py-1.5 dark:text-white',
                        activeTab === section.id
                            ? 'border-teal-700 bg-teal-50 text-teal-900 dark:bg-teal-950 dark:text-teal-200'
                            : 'border-slate-300 hover:border-teal-700',
                    ]"
                    @click="activeTab = section.id"
                    >{{ section.label }}</button
                >
            </nav>

            <section v-if="setupActive" class="rounded-xl border border-teal-200 bg-teal-50 p-5 sm:p-7 dark:border-teal-900 dark:bg-teal-950/40">
                <p class="text-sm font-semibold tracking-wider text-teal-800 uppercase dark:text-teal-200">Siapkan website</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950 dark:text-white">
                    {{ setupStep === 1 ? 'Mulai dari identitas usaha' : setupStep === 2 ? 'Pilih tampilan halaman' : 'Pilih yang ingin ditampilkan' }}
                </h2>
                <div class="mt-5 grid gap-2 sm:grid-cols-3" aria-label="Tahap pembuatan website">
                    <div
                        v-for="step in [
                            { id: 1, label: 'Identitas' },
                            { id: 2, label: 'Desain' },
                            { id: 3, label: 'Produk atau layanan' },
                        ]"
                        :key="step.id"
                        class="flex items-center gap-2 text-sm"
                    >
                        <span
                            :class="[
                                'grid h-7 w-7 place-items-center rounded-full font-semibold',
                                setupStep >= step.id ? 'bg-teal-800 text-white' : 'bg-white text-slate-500',
                            ]"
                            >{{ step.id }}</span
                        >
                        <span
                            :class="setupStep >= step.id ? 'font-semibold text-teal-900 dark:text-teal-100' : 'text-slate-600 dark:text-slate-300'"
                            >{{ step.label }}</span
                        >
                    </div>
                </div>
            </section>

            <section v-if="site && !setupActive && activeTab === 'permintaan'" id="permintaan" class="space-y-4">
                <div class="flex items-end justify-between">
                    <div>
                        <h2 class="text-xl font-semibold dark:text-white">Permintaan masuk</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Pesanan dan prospek terbaru dari website.</p>
                    </div>
                </div>
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
                        <h3 class="mb-3 font-semibold dark:text-white">Pesanan produk</h3>
                        <p v-if="!orders.length" class="text-sm text-slate-600">Belum ada pesanan.</p>
                        <ul class="divide-y divide-slate-200 dark:divide-slate-700">
                            <li v-for="order in orders" :key="order.id" class="flex flex-wrap justify-between gap-3 py-3 text-sm">
                                <div>
                                    <Link :href="`/sales-orders/${order.id}`" class="font-semibold text-teal-800 underline dark:text-teal-300">{{
                                        order.order_number
                                    }}</Link>
                                    <p class="text-slate-600 dark:text-slate-300">{{ order.customer?.name }} · {{ order.customer?.phone }}</p>
                                    <a
                                        v-if="order.customer?.phone"
                                        :href="
                                            whatsappLink(
                                                order.customer.phone,
                                                `Halo ${order.customer.name}, kami ingin mengonfirmasi pesanan ${order.order_number}.`,
                                            )
                                        "
                                        target="_blank"
                                        rel="noopener"
                                        class="mt-1 inline-block font-medium text-teal-800 underline dark:text-teal-300"
                                        >Konfirmasi via WhatsApp ↗</a
                                    >
                                </div>
                                <div class="text-right">
                                    <p class="font-medium dark:text-white">{{ money(order.total_amount) }}</p>
                                    <p class="text-slate-600 dark:text-slate-300">{{ order.status }} · {{ order.payment_status }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
                        <h3 class="mb-3 font-semibold dark:text-white">Prospek jasa</h3>
                        <p v-if="!leads.length" class="text-sm text-slate-600">Belum ada prospek.</p>
                        <ul class="divide-y divide-slate-200 dark:divide-slate-700">
                            <li v-for="lead in leads" :key="lead.id" class="py-3 text-sm">
                                <div class="flex justify-between gap-3">
                                    <strong class="dark:text-white">{{ lead.name }}</strong
                                    ><span class="text-slate-600 dark:text-slate-300">{{ lead.status }}</span>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300">{{ lead.service?.name || 'Pertanyaan umum' }} · {{ lead.phone }}</p>
                                <p v-if="lead.message" class="mt-1 text-slate-700 dark:text-slate-200">{{ lead.message }}</p>
                                <a
                                    :href="whatsappLink(lead.phone, `Halo ${lead.name}, kami ingin menindaklanjuti permintaan Anda.`)"
                                    target="_blank"
                                    rel="noopener"
                                    class="mt-2 inline-block font-semibold text-teal-800 underline dark:text-teal-300"
                                    >Hubungi lewat WhatsApp ↗</a
                                >
                                <div v-if="lead.status !== 'converted'" class="mt-3 flex flex-wrap gap-2">
                                    <button
                                        v-if="lead.status === 'new'"
                                        type="button"
                                        class="rounded-lg border border-slate-400 px-2 py-1 text-xs font-medium dark:text-white"
                                        @click="router.post(`/website/leads/${lead.id}/status`, { status: 'contacted' }, { preserveScroll: true })"
                                    >
                                        Tandai dihubungi
                                    </button>
                                    <button
                                        v-if="lead.service_id"
                                        type="button"
                                        class="rounded-lg border border-teal-700 px-2 py-1 text-xs font-medium text-teal-800 dark:text-teal-300"
                                        @click="router.post(`/website/leads/${lead.id}/convert`, {}, { preserveScroll: true })"
                                    >
                                        Buat pesanan draft
                                    </button>
                                    <button
                                        v-if="lead.status !== 'closed'"
                                        type="button"
                                        class="rounded-lg border border-slate-400 px-2 py-1 text-xs font-medium dark:text-white"
                                        @click="closeLead(lead)"
                                    >
                                        Tutup prospek
                                    </button>
                                </div>
                                <Link
                                    v-else-if="lead.sales_order_id"
                                    :href="`/sales-orders/${lead.sales_order_id}`"
                                    class="mt-2 inline-block font-medium text-teal-800 underline dark:text-teal-300"
                                    >Lihat pesanan</Link
                                >
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section
                v-if="canManage && (setupActive ? setupStep === 1 : activeTab === 'identitas')"
                id="identitas"
                class="rounded-xl border border-slate-200 bg-white p-5 sm:p-7 dark:border-slate-700 dark:bg-slate-900"
            >
                <div class="mb-6 flex flex-wrap justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold dark:text-white">Identitas dan alamat</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Alamat gratis: nama-usaha.fabriku.biz.id</p>
                    </div>
                    <span v-if="site" class="h-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ site.status }}</span>
                </div>
                <form class="grid gap-4" @submit.prevent="saveIdentity">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="grid gap-1 text-sm font-medium dark:text-white"
                            >Nama usaha<input
                                v-model="siteForm.name"
                                required
                                maxlength="120"
                                class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                            /><small v-if="siteForm.errors.name" class="text-rose-700">{{ siteForm.errors.name }}</small></label
                        >
                        <label class="grid gap-1 text-sm font-medium dark:text-white">
                            Alamat website
                            <div class="flex min-w-0">
                                <input
                                    v-model="siteForm.slug"
                                    required
                                    pattern="[a-z0-9-]{3,50}"
                                    maxlength="50"
                                    class="min-w-0 flex-1 rounded-l-lg border border-r-0 border-slate-400 bg-white p-3 text-slate-950"
                                    aria-label="Nama alamat website"
                                />
                                <input
                                    value=".fabriku.biz.id"
                                    disabled
                                    aria-label="Domain Fabriku"
                                    class="w-36 rounded-r-lg border border-slate-400 bg-slate-100 px-3 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                />
                            </div>
                            <small v-if="siteForm.errors.slug" class="text-rose-700">{{ siteForm.errors.slug }}</small>
                        </label>
                        <label class="grid gap-1 text-sm font-medium dark:text-white"
                            >Jenis usaha<select v-model="siteForm.mode" class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950">
                                <option value="produk">Produk</option>
                                <option value="jasa">Jasa</option>
                                <option value="gabungan">Produk dan jasa</option>
                            </select></label
                        >
                        <label class="grid gap-1 text-sm font-medium dark:text-white"
                            >WhatsApp toko<input
                                v-model="siteForm.whatsapp"
                                type="tel"
                                maxlength="32"
                                class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                        /></label>
                    </div>
                    <label class="grid gap-1 text-sm font-medium dark:text-white"
                        >Deskripsi singkat<textarea
                            v-model="siteForm.description"
                            rows="2"
                            maxlength="300"
                            class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                        ></textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-medium dark:text-white"
                        >Alamat usaha<textarea
                            v-model="siteForm.address"
                            rows="2"
                            maxlength="500"
                            class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                        ></textarea>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="submit"
                            :disabled="siteForm.processing"
                            class="rounded-lg bg-teal-800 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            {{ setupActive && setupStep === 1 ? 'Lanjut ke desain' : site ? 'Simpan identitas' : 'Buat website' }}</button
                        ><button
                            v-if="site && !setupActive && site.status !== 'published'"
                            type="button"
                            class="rounded-lg border border-teal-800 px-4 py-2.5 text-sm font-semibold text-teal-800 dark:text-teal-300"
                            @click="publish('published')"
                        >
                            Terbitkan</button
                        ><button
                            v-if="site?.status === 'published' && !setupActive"
                            type="button"
                            class="rounded-lg border border-slate-400 px-4 py-2.5 text-sm font-semibold dark:text-white"
                            @click="publish('paused')"
                        >
                            Jeda website
                        </button>
                    </div>
                </form>
            </section>

            <section
                v-if="site && canManage && (setupActive ? setupStep === 3 : activeTab === 'katalog')"
                id="katalog"
                class="grid gap-6 lg:grid-cols-2"
            >
                <div class="rounded-xl border border-slate-200 bg-white p-5 sm:p-7 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-xl font-semibold dark:text-white">Produk yang tampil</h2>
                    <p class="mb-5 text-sm text-slate-600 dark:text-slate-300">
                        Pilih dari stok yang sudah ada. Harga dan ketersediaan mengikuti inventori.
                    </p>
                    <form class="grid gap-3" @submit.prevent="productForm.post('/website/products', { preserveScroll: true })">
                        <label class="grid gap-1 text-sm font-medium dark:text-white"
                            >Produk<select
                                :value="selectedCode"
                                required
                                class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                                @change="selectProduct(($event.target as HTMLSelectElement).value)"
                            >
                                <option value="">Pilih produk</option>
                                <option v-for="item in catalog" :key="item.product_code" :value="item.product_code">
                                    {{ item.product_name }} · {{ item.product_code }}
                                </option>
                            </select></label
                        >
                        <label class="grid gap-1 text-sm font-medium dark:text-white"
                            >Nama tampil<input
                                v-model="productForm.title"
                                required
                                class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                        /></label>
                        <label class="grid gap-1 text-sm font-medium dark:text-white"
                            >Alamat produk<input
                                v-model="productForm.slug"
                                required
                                class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                        /></label>
                        <label class="grid gap-1 text-sm font-medium dark:text-white"
                            >Deskripsi<textarea
                                v-model="productForm.description"
                                rows="2"
                                class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                            ></textarea>
                        </label>
                        <label class="flex items-center gap-2 text-sm dark:text-white"
                            ><input v-model="productForm.is_visible" type="checkbox" /> Tampilkan di website</label
                        >
                        <p v-if="Object.keys(productForm.errors).length" role="alert" class="text-sm text-rose-700">
                            {{ Object.values(productForm.errors)[0] }}
                        </p>
                        <button
                            type="submit"
                            :disabled="productForm.processing"
                            class="rounded-lg bg-teal-800 px-4 py-2.5 text-sm font-semibold text-white"
                        >
                            Simpan produk
                        </button>
                    </form>
                    <p v-if="products.length" class="mt-4 text-xs text-slate-600">{{ products.length }} produk sudah disiapkan untuk website.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 sm:p-7 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-xl font-semibold dark:text-white">Layanan yang tampil</h2>
                    <p class="mb-5 text-sm text-slate-600 dark:text-slate-300">Calon pelanggan mengirim pertanyaan, bukan membayar di website.</p>
                    <p v-if="!services.length" class="text-sm text-slate-600">
                        Belum ada layanan. <Link href="/services/create" class="underline">Tambah layanan</Link> dulu.
                    </p>
                    <div v-for="service in services" :key="service.id" class="grid gap-2 border-b border-slate-200 py-3 text-sm">
                        <strong class="dark:text-white">{{ service.name }}</strong>
                        <div class="flex flex-wrap gap-2">
                            <label class="flex items-center gap-2 dark:text-white"
                                ><input v-model="service.is_public" type="checkbox" /> Tampilkan</label
                            ><input
                                v-model="service.slug"
                                :aria-label="`Alamat ${service.name}`"
                                class="min-w-0 flex-1 rounded-lg border border-slate-400 bg-white px-2 py-1 text-slate-950"
                                placeholder="alamat-layanan"
                            /><button
                                type="button"
                                class="rounded-lg border border-slate-400 px-3 py-1 font-medium dark:text-white"
                                @click="saveService(service)"
                            >
                                Simpan
                            </button>
                        </div>
                    </div>
                    <button
                        v-if="setupActive"
                        type="button"
                        class="mt-5 w-full rounded-lg bg-teal-800 px-4 py-2.5 text-sm font-semibold text-white"
                        @click="completeSetup"
                    >
                        Simpan dan buka pengelolaan website
                    </button>
                </div>
            </section>

            <section
                v-if="site && canManage && (setupActive ? setupStep === 2 : activeTab === 'desain')"
                id="desain"
                class="rounded-xl border border-slate-200 bg-white p-5 sm:p-7 dark:border-slate-700 dark:bg-slate-900"
            >
                <h2 class="text-xl font-semibold dark:text-white">Desain halaman depan</h2>
                <p class="mb-5 text-sm text-slate-600 dark:text-slate-300">
                    Ubah tulisan dan warna. Setiap simpan membuat versi yang bisa dipulihkan.
                </p>
                <form class="grid gap-4" @submit.prevent="saveTheme">
                    <fieldset class="grid gap-2 sm:grid-cols-3">
                        <legend class="mb-2 text-sm font-semibold dark:text-white">Tata letak awal</legend>
                        <label
                            v-for="preset in [
                                { id: 'ruang', name: 'Ruang', hint: 'Sederhana, rapi, langsung ke inti' },
                                { id: 'etalase', name: 'Etalase', hint: 'Warna kuat untuk produk unggulan' },
                                { id: 'studio', name: 'Studio', hint: 'Tipografi besar untuk jasa dan karya' },
                            ]"
                            :key="preset.id"
                            class="flex cursor-pointer gap-3 rounded-lg border border-slate-300 p-3 text-sm dark:text-white"
                            ><input v-model="themeForm.preset" type="radio" :value="preset.id" /><span
                                ><strong class="block">{{ preset.name }}</strong
                                ><small class="text-slate-600 dark:text-slate-300">{{ preset.hint }}</small></span
                            ></label
                        >
                    </fieldset>
                    <label class="grid gap-1 text-sm font-medium dark:text-white"
                        >Judul utama<input
                            v-model="themeForm.hero_title"
                            required
                            maxlength="120"
                            class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                    /></label>
                    <label class="grid gap-1 text-sm font-medium dark:text-white"
                        >Kalimat pembuka<textarea
                            v-model="themeForm.hero_subtitle"
                            rows="2"
                            maxlength="300"
                            class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                        ></textarea>
                    </label>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <label
                            v-for="color in [
                                { key: 'primary', label: 'Utama' },
                                { key: 'accent', label: 'Aksen' },
                                { key: 'background', label: 'Latar' },
                                { key: 'text', label: 'Teks' },
                            ]"
                            :key="color.key"
                            class="grid gap-2 text-sm font-medium dark:text-white"
                            >{{ color.label
                            }}<input
                                v-model="(themeForm as any)[color.key]"
                                type="color"
                                class="h-11 w-full cursor-pointer rounded-lg border border-slate-400"
                        /></label>
                    </div>
                    <p v-if="Object.keys(themeForm.errors).length" role="alert" class="text-sm text-rose-700">
                        {{ Object.values(themeForm.errors)[0] }}
                    </p>
                    <button
                        type="submit"
                        :disabled="themeForm.processing"
                        class="w-fit rounded-lg bg-teal-800 px-4 py-2.5 text-sm font-semibold text-white"
                    >
                        Simpan desain
                    </button>
                </form>
                <div v-if="site.theme_versions.length" class="mt-7 border-t border-slate-200 pt-5">
                    <h3 class="mb-2 font-semibold dark:text-white">Riwayat desain</h3>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="version in site.theme_versions"
                            :key="version.id"
                            type="button"
                            :disabled="site.active_theme_version?.id === version.id"
                            class="rounded-lg border border-slate-400 px-3 py-2 text-sm disabled:opacity-50 dark:text-white"
                            @click="router.post(`/website/theme/${version.id}/restore`)"
                        >
                            Versi {{ version.version }}{{ site.active_theme_version?.id === version.id ? ' · aktif' : '' }}
                        </button>
                    </div>
                </div>
            </section>

            <section
                v-if="site && canManage && !setupActive && activeTab === 'pengaturan'"
                id="domain"
                class="rounded-xl border border-slate-200 bg-white p-5 sm:p-7 dark:border-slate-700 dark:bg-slate-900"
            >
                <h2 class="text-xl font-semibold dark:text-white">Domain sendiri</h2>
                <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">
                    Alamat Fabriku tetap tersedia. Untuk domain pribadi, gunakan nama seperti www.usahaanda.com dan arahkan CNAME-nya ke
                    {{ cnameTarget }}. Jika DNS domain Anda memakai Cloudflare, setel CNAME ke <em>DNS only</em> agar Fabriku bisa memverifikasinya.
                </p>
                <form class="flex flex-wrap gap-2" @submit.prevent="domainForm.post('/website/domain', { preserveScroll: true })">
                    <label class="grid min-w-0 flex-1 gap-1 text-sm font-medium dark:text-white"
                        >Domain<input
                            v-model="domainForm.domain"
                            required
                            :disabled="!!site.custom_domain"
                            placeholder="www.usahaanda.com"
                            class="w-full rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                    /></label>
                    <button
                        v-if="!site.custom_domain"
                        type="submit"
                        :disabled="domainForm.processing"
                        class="self-end rounded-lg bg-teal-800 px-4 py-3 text-sm font-semibold text-white"
                    >
                        Hubungkan domain
                    </button>
                </form>
                <p v-if="domainForm.errors.domain" role="alert" class="mt-2 text-sm text-rose-700">{{ domainForm.errors.domain }}</p>
                <div v-if="site.custom_domain" class="mt-5 space-y-3 text-sm dark:text-white">
                    <p>
                        Status: <strong>{{ site.domain_status === 'active' ? 'Aktif dengan HTTPS' : 'Menunggu DNS dan sertifikat' }}</strong>
                    </p>
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-slate-100 text-slate-700">
                                    <th class="p-3">Jenis</th>
                                    <th class="p-3">Nama</th>
                                    <th class="p-3">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-t">
                                    <td class="p-3">CNAME</td>
                                    <td class="p-3">{{ site.custom_domain }}</td>
                                    <td class="p-3 break-all">{{ cnameTarget }}</td>
                                </tr>
                                <tr v-for="record in domainRecords" :key="record.name" class="border-t">
                                    <td class="p-3">{{ record.type }}</td>
                                    <td class="p-3 break-all">{{ record.name }}</td>
                                    <td class="p-3 break-all">{{ record.value }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg border border-slate-400 px-4 py-2.5 font-semibold"
                        @click="router.post('/website/domain/sync', {}, { preserveScroll: true })"
                    >
                        Periksa status domain
                    </button>
                </div>
            </section>

            <section
                v-if="site && canManage && !setupActive && activeTab === 'pengaturan'"
                class="rounded-xl border border-slate-200 bg-white p-5 sm:p-7 dark:border-slate-700 dark:bg-slate-900"
            >
                <h2 class="text-xl font-semibold dark:text-white">SEO</h2>
                <p class="mb-5 text-sm text-slate-600 dark:text-slate-300">Atur judul dan ringkasan yang dibaca mesin pencari.</p>
                <form class="grid gap-4" @submit.prevent="saveIdentity">
                    <label class="grid gap-1 text-sm font-medium dark:text-white"
                        >Judul di Google<input
                            v-model="siteForm.seo_title"
                            maxlength="70"
                            class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                            placeholder="Nama usaha — produk atau layanan" /></label
                    ><label class="grid gap-1 text-sm font-medium dark:text-white"
                        >Ringkasan di Google<textarea
                            v-model="siteForm.seo_description"
                            maxlength="160"
                            rows="3"
                            class="rounded-lg border border-slate-400 bg-white p-3 text-slate-950"
                            placeholder="Apa yang Anda tawarkan dan di mana"
                        ></textarea>
                    </label>
                    <button
                        type="submit"
                        :disabled="siteForm.processing"
                        class="w-fit rounded-lg bg-teal-800 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-50"
                    >
                        Simpan SEO
                    </button>
                </form>
            </section>

            <section
                v-if="site && canManage && !setupActive && activeTab === 'pengaturan'"
                id="tim"
                class="rounded-xl border border-slate-200 bg-white p-5 sm:p-7 dark:border-slate-700 dark:bg-slate-900"
            >
                <h2 class="text-xl font-semibold dark:text-white">Penerima permintaan</h2>
                <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">
                    Pilih staf yang menerima email dan notifikasi Telegram bila akun Telegram mereka terhubung.
                </p>
                <form @submit.prevent="recipientForm.post('/website/recipients', { preserveScroll: true })">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label
                            v-for="user in users"
                            :key="user.id"
                            class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 text-sm dark:border-slate-700 dark:text-white"
                            ><input v-model="recipientForm.user_ids" type="checkbox" :value="user.id" />
                            <span
                                >{{ user.name }}<small class="block text-slate-600 dark:text-slate-300">{{ user.email }}</small></span
                            ></label
                        >
                    </div>
                    <button type="submit" class="mt-4 rounded-lg bg-teal-800 px-4 py-2.5 text-sm font-semibold text-white">Simpan penerima</button>
                </form>
            </section>
        </main>
    </AppLayout>
</template>
