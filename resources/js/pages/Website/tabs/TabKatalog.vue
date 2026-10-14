<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import Modal from '@/components/Modal.vue';
import ProductSelect from '@/components/ProductSelect.vue';
import ProductThumbnail from '@/components/ProductThumbnail.vue';
import { formatCurrency, formatNumber } from '@/lib/formatters';
import { Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    Edit2,
    Eye,
    EyeOff,
    Layers,
    Plus,
    Save,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

type Product = {
    id: number;
    inventory_item_id: number;
    slug: string;
    title: string;
    description: string | null;
    is_visible: boolean;
    sort: number;
    image_url: string | null;
    product_name: string;
    sku: string;
    selling_price: number;
    available_stock: number;
    status_key: 'visible' | 'hidden' | 'out_of_stock' | 'unavailable';
    status_label: string;
};

type CatalogItem = {
    id: number;
    sku: string;
    product_name: string;
    current_stock: number;
    reserved_stock: number;
    selling_price: number;
    image_url: string | null;
};

type Service = {
    id: number;
    name: string;
    slug: string | null;
    is_public: boolean;
    is_active: boolean;
    sort?: number;
    price: number | string;
    price_label?: string | null;
    image_path?: string | null;
};

const props = defineProps<{
    products: Product[];
    catalog: CatalogItem[];
    services: Service[];
    mode?: string;
}>();

// Modals
const showAddProductModal = ref(false);
const editingProduct = ref<Product | null>(null);
const deletingProduct = ref<Product | null>(null);

// Form for adding product
const addForm = useForm({
    inventory_item_id: 0,
    slug: '',
    title: '',
    description: '',
    is_visible: true,
});

// Form for editing product
const editForm = useForm({
    id: 0,
    slug: '',
    title: '',
    description: '',
    is_visible: true,
});

// Watch item selection to autofill title and slug
watch(
    () => addForm.inventory_item_id,
    (newId) => {
        if (!newId) return;
        const selected = props.catalog.find((c) => c.id === newId);
        if (selected) {
            addForm.title = selected.product_name;
            addForm.slug = selected.product_name
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
        }
    },
);

function openAddModal() {
    addForm.reset();
    addForm.clearErrors();
    addForm.inventory_item_id = 0;
    addForm.is_visible = true;
    showAddProductModal.value = true;
}

function submitAddProduct() {
    addForm.post('/website/products', {
        preserveScroll: true,
        onSuccess: () => {
            showAddProductModal.value = false;
            addForm.reset();
        },
    });
}

function openEditModal(prod: Product) {
    editingProduct.value = prod;
    editForm.clearErrors();
    editForm.id = prod.id;
    editForm.title = prod.title;
    editForm.slug = prod.slug;
    editForm.description = prod.description ?? '';
    editForm.is_visible = prod.is_visible;
}

function submitEditProduct() {
    if (!editingProduct.value) return;
    editForm.post('/website/products', {
        preserveScroll: true,
        onSuccess: () => {
            editingProduct.value = null;
        },
    });
}

function toggleProductVisibility(prod: Product) {
    router.post(`/website/products/${prod.id}/toggle`, {}, { preserveScroll: true });
}

function moveProduct(prod: Product, direction: 'up' | 'down') {
    router.post(`/website/products/${prod.id}/move`, { direction }, { preserveScroll: true });
}

function confirmDeleteProduct(prod: Product) {
    deletingProduct.value = prod;
}

function executeDeleteProduct() {
    if (!deletingProduct.value) return;
    router.delete(`/website/products/${deletingProduct.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deletingProduct.value = null;
        },
    });
}

// Service actions
const serviceSlugs = ref<Record<number, string>>(
    Object.fromEntries(props.services.map((s) => [s.id, s.slug ?? s.name.toLowerCase().replace(/[^a-z0-9]+/g, '-')])),
);
const servicePublic = ref<Record<number, boolean>>(
    Object.fromEntries(props.services.map((s) => [s.id, s.is_public])),
);

// Keep in sync with prop updates
watch(
    () => props.services,
    (newServices) => {
        serviceSlugs.value = Object.fromEntries(newServices.map((s) => [s.id, s.slug ?? s.name.toLowerCase().replace(/[^a-z0-9]+/g, '-')]));
        servicePublic.value = Object.fromEntries(newServices.map((s) => [s.id, s.is_public]));
    },
    { deep: true },
);

function saveService(service: Service) {
    router.post(
        `/website/services/${service.id}`,
        {
            slug: serviceSlugs.value[service.id] || service.name.toLowerCase().replace(/[^a-z0-9]+/g, '-'),
            is_public: servicePublic.value[service.id] ? 1 : 0,
        },
        { preserveScroll: true },
    );
}

function moveService(service: Service, direction: 'up' | 'down') {
    router.post(`/website/services/${service.id}/move`, { direction }, { preserveScroll: true });
}

const statusBadgeClasses: Record<string, string> = {
    visible: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    hidden: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    out_of_stock: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    unavailable: 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
};

const showProductsSection = computed(() => props.mode !== 'jasa');
const showServicesSection = computed(() => props.mode !== 'produk');
</script>

<template>
    <div class="space-y-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Katalog Website</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Pilih dan atur produk atau layanan yang ingin Anda tampilkan kepada pengunjung website.
            </p>
        </div>

        <!-- Section Produk -->
        <section v-if="showProductsSection" class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Produk yang Tampil di Website</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Harga dan stok diambil secara langsung dari inventori aktif.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-teal-800 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-teal-700 dark:bg-teal-700 dark:hover:bg-teal-600"
                    @click="openAddModal"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Produk ke Website
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="products.length === 0" class="rounded-xl border border-dashed border-slate-300 py-12 text-center dark:border-slate-700">
                <Layers class="mx-auto mb-2 h-8 w-8 text-slate-400" />
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Belum ada produk yang dipilih untuk website</p>
                <p class="mt-1 text-xs text-slate-500">Pilih dari inventori Anda agar pembeli dapat memesan secara online.</p>
                <button
                    type="button"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg border border-teal-700 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-800 hover:bg-teal-100 dark:border-teal-600 dark:bg-teal-950 dark:text-teal-200"
                    @click="openAddModal"
                >
                    <Plus class="h-4 w-4" />
                    Pilih Produk Sekarang
                </button>
            </div>

            <!-- Products List / Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase dark:border-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="py-3 pr-4 pl-1">Produk</th>
                            <th class="px-4 py-3">Barang Inventori</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Stok</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-3 py-3 text-center">Urutan</th>
                            <th class="py-3 pr-1 pl-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="(prod, index) in products" :key="prod.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                            <!-- Foto & Judul -->
                            <td class="py-3.5 pr-4 pl-1">
                                <div class="flex items-center gap-3">
                                    <div class="h-11 w-11 shrink-0 overflow-hidden rounded-lg">
                                        <ProductThumbnail :image-url="prod.image_url" />
                                    </div>
                                    <div>
                                        <strong class="font-semibold text-slate-900 dark:text-white">{{ prod.title }}</strong>
                                        <p class="font-mono text-xs text-slate-400">/{{ prod.slug }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Barang Inventori -->
                            <td class="px-4 py-3.5 text-xs">
                                <div class="font-medium text-slate-900 dark:text-white">{{ prod.product_name }}</div>
                                <div class="font-mono text-slate-400">{{ prod.sku }}</div>
                            </td>

                            <!-- Harga -->
                            <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-white">
                                {{ formatCurrency(prod.selling_price) }}
                            </td>

                            <!-- Stok -->
                            <td class="px-4 py-3.5 font-medium text-slate-700 dark:text-slate-300">
                                {{ formatNumber(prod.available_stock) }}
                            </td>

                            <!-- Status Badge -->
                            <td class="px-4 py-3.5">
                                <span :class="['inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold', statusBadgeClasses[prod.status_key]]">
                                    {{ prod.status_label }}
                                </span>
                            </td>

                            <!-- Urutan (Tombol Naik / Turun) -->
                            <td class="px-3 py-3.5 text-center">
                                <div class="inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700">
                                    <button
                                        type="button"
                                        :disabled="index === 0"
                                        title="Pindah ke atas"
                                        class="p-1 text-slate-600 hover:bg-slate-100 disabled:opacity-30 dark:text-slate-300 dark:hover:bg-slate-800"
                                        @click="moveProduct(prod, 'up')"
                                    >
                                        <ArrowUp class="h-3.5 w-3.5" />
                                    </button>
                                    <div class="h-4 w-px bg-slate-200 dark:bg-slate-700"></div>
                                    <button
                                        type="button"
                                        :disabled="index === products.length - 1"
                                        title="Pindah ke bawah"
                                        class="p-1 text-slate-600 hover:bg-slate-100 disabled:opacity-30 dark:text-slate-300 dark:hover:bg-slate-800"
                                        @click="moveProduct(prod, 'down')"
                                    >
                                        <ArrowDown class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 pr-1 pl-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        type="button"
                                        :title="prod.is_visible ? 'Sembunyikan dari website' : 'Tampilkan di website'"
                                        class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-white"
                                        @click="toggleProductVisibility(prod)"
                                    >
                                        <Eye v-if="prod.is_visible" class="h-4 w-4 text-emerald-600" />
                                        <EyeOff v-else class="h-4 w-4 text-slate-400" />
                                    </button>

                                    <button
                                        type="button"
                                        title="Edit info produk website"
                                        class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-white"
                                        @click="openEditModal(prod)"
                                    >
                                        <Edit2 class="h-4 w-4" />
                                    </button>

                                    <button
                                        type="button"
                                        title="Hapus dari website"
                                        class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"
                                        @click="confirmDeleteProduct(prod)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Section Layanan -->
        <section v-if="showServicesSection" class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Layanan yang Tampil di Website</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Pengunjung dapat mengirimkan prospek dan pertanyaan untuk layanan ini.
                </p>
            </div>

            <div v-if="services.length === 0" class="py-8 text-center text-slate-500">
                <p class="text-sm">Belum ada layanan yang dibuat.</p>
                <Link href="/services/create" class="mt-2 inline-block text-xs font-semibold text-teal-700 underline dark:text-teal-400">
                    Tambah Layanan di Modul Layanan ↗
                </Link>
            </div>

            <div v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                <div
                    v-for="(service, sIndex) in services"
                    :key="service.id"
                    class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <!-- Urutan -->
                        <div class="inline-flex shrink-0 items-center rounded-md border border-slate-200 dark:border-slate-700">
                            <button
                                type="button"
                                :disabled="sIndex === 0"
                                title="Pindah ke atas"
                                class="p-1 text-slate-600 hover:bg-slate-100 disabled:opacity-30 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="moveService(service, 'up')"
                            >
                                <ArrowUp class="h-3.5 w-3.5" />
                            </button>
                            <div class="h-4 w-px bg-slate-200 dark:bg-slate-700"></div>
                            <button
                                type="button"
                                :disabled="sIndex === services.length - 1"
                                title="Pindah ke bawah"
                                class="p-1 text-slate-600 hover:bg-slate-100 disabled:opacity-30 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="moveService(service, 'down')"
                            >
                                <ArrowDown class="h-3.5 w-3.5" />
                            </button>
                        </div>

                        <div>
                            <strong class="font-semibold text-slate-900 dark:text-white">{{ service.name }}</strong>
                            <p class="text-xs text-slate-500">{{ formatCurrency(service.price) }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                            <input
                                v-model="servicePublic[service.id]"
                                type="checkbox"
                                class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                            />
                            Tampilkan
                        </label>

                        <div class="flex items-center gap-1">
                            <span class="text-xs text-slate-400">/</span>
                            <input
                                v-model="serviceSlugs[service.id]"
                                :aria-label="`Alamat ${service.name}`"
                                type="text"
                                placeholder="alamat-layanan"
                                class="w-40 rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            @click="saveService(service)"
                        >
                            <Save class="h-3.5 w-3.5 text-teal-700 dark:text-teal-400" />
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal Tambah Produk -->
        <Modal :show="showAddProductModal" max-width="lg" @close="showAddProductModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Tambah Produk ke Website</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Pilih barang dari stok inventori aktif. Produk yang sudah terhubung tidak muncul lagi di pilihan.
                </p>

                <form class="mt-5 space-y-4" @submit.prevent="submitAddProduct">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Pilih Barang Inventori <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-1">
                            <ProductSelect
                                v-model="addForm.inventory_item_id"
                                :items="catalog"
                                :error="!!addForm.errors.inventory_item_id"
                            />
                        </div>
                        <p v-if="addForm.errors.inventory_item_id" class="mt-1 text-xs text-rose-600">
                            {{ addForm.errors.inventory_item_id }}
                        </p>
                    </div>

                    <div>
                        <label for="add_title" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Judul Tampil di Website <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="add_title"
                            v-model="addForm.title"
                            type="text"
                            required
                            maxlength="150"
                            placeholder="Judul produk untuk pembeli"
                            class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <p v-if="addForm.errors.title" class="mt-1 text-xs text-rose-600">{{ addForm.errors.title }}</p>
                    </div>

                    <div>
                        <label for="add_slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Alamat Produk (URL Slug) <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-1 flex rounded-lg shadow-2xs">
                            <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                /produk/
                            </span>
                            <input
                                id="add_slug"
                                v-model="addForm.slug"
                                type="text"
                                required
                                maxlength="100"
                                placeholder="nama-produk"
                                class="block w-full min-w-0 flex-1 rounded-r-lg border border-slate-300 p-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                        <p v-if="addForm.errors.slug" class="mt-1 text-xs text-rose-600">{{ addForm.errors.slug }}</p>
                    </div>

                    <div>
                        <label for="add_description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Deskripsi Produk
                        </label>
                        <textarea
                            id="add_description"
                            v-model="addForm.description"
                            rows="3"
                            maxlength="2000"
                            placeholder="Jelaskan detail bahan, ukuran, atau keunggulan produk..."
                            class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                        <p v-if="addForm.errors.description" class="mt-1 text-xs text-rose-600">{{ addForm.errors.description }}</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input
                                v-model="addForm.is_visible"
                                type="checkbox"
                                class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                            />
                            Langsung tampilkan di website
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="showAddProductModal = false"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="addForm.processing || !addForm.inventory_item_id"
                            class="rounded-lg bg-teal-800 px-5 py-2 text-sm font-semibold text-white shadow-2xs hover:bg-teal-700 disabled:opacity-50 dark:bg-teal-700 dark:hover:bg-teal-600"
                        >
                            Tambahkan ke Website
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Edit Produk -->
        <Modal :show="editingProduct !== null" max-width="lg" @close="editingProduct = null">
            <div v-if="editingProduct" class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Edit Produk Website</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Sesuaikan informasi tampilan produk di website.
                </p>

                <!-- Info Barang Inventori Terhubung -->
                <div class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-600 dark:bg-slate-800/60 dark:text-slate-400">
                    <p class="font-medium text-slate-800 dark:text-slate-200">
                        Barang terhubung: {{ editingProduct.product_name }} ({{ editingProduct.sku }})
                    </p>
                    <p class="mt-0.5 text-slate-500">
                        Barang yang terhubung tidak bisa diganti. Untuk mengganti barang, hapus produk ini lalu tambahkan produk baru.
                    </p>
                </div>

                <form class="mt-4 space-y-4" @submit.prevent="submitEditProduct">
                    <div>
                        <label for="edit_title" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Judul Tampil di Website <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="edit_title"
                            v-model="editForm.title"
                            type="text"
                            required
                            maxlength="150"
                            class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <p v-if="editForm.errors.title" class="mt-1 text-xs text-rose-600">{{ editForm.errors.title }}</p>
                    </div>

                    <div>
                        <label for="edit_slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Alamat Produk (URL Slug) <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-1 flex rounded-lg shadow-2xs">
                            <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                /produk/
                            </span>
                            <input
                                id="edit_slug"
                                v-model="editForm.slug"
                                type="text"
                                required
                                maxlength="100"
                                class="block w-full min-w-0 flex-1 rounded-r-lg border border-slate-300 p-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                        <p v-if="editForm.errors.slug" class="mt-1 text-xs text-rose-600">{{ editForm.errors.slug }}</p>
                    </div>

                    <div>
                        <label for="edit_description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Deskripsi Produk
                        </label>
                        <textarea
                            id="edit_description"
                            v-model="editForm.description"
                            rows="3"
                            maxlength="2000"
                            class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                        <p v-if="editForm.errors.description" class="mt-1 text-xs text-rose-600">{{ editForm.errors.description }}</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input
                                v-model="editForm.is_visible"
                                type="checkbox"
                                class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                            />
                            Tampilkan di website
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="editingProduct = null"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="rounded-lg bg-teal-800 px-5 py-2 text-sm font-semibold text-white shadow-2xs hover:bg-teal-700 disabled:opacity-50 dark:bg-teal-700 dark:hover:bg-teal-600"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Konfirmasi Hapus Produk Modal -->
        <ConfirmationModal
            :show="deletingProduct !== null"
            title="Hapus Produk dari Website"
            :message="`Apakah Anda yakin ingin menghapus '${deletingProduct?.title}' dari website? Produk ini tidak akan tampil lagi di toko online, namun data dan stok barang di inventori tetap aman.`"
            confirm-text="Ya, Hapus dari Website"
            cancel-text="Batal"
            variant="danger"
            @confirm="executeDeleteProduct"
            @cancel="deletingProduct = null"
        />
    </div>
</template>
