<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingCart, UserPlus, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface InventoryItem {
    id: number;
    sku: string;
    product_name: string;
    current_quantity: number;
    reserved_quantity: number;
    selling_price: number;
    unit_cost: number;
    image_url?: string | null;
}

interface Service {
    id: number;
    code: string;
    name: string;
    category: string | null;
    price: number;
}

interface CartItem {
    id: number;
    type: 'inventory' | 'service';
    product_name: string;
    sku: string;
    quantity: number;
    unit_price: number;
    available: number | null; // null means unlimited
    image_url?: string | null;
    served_by?: number | null;
}

interface Customer {
    id: number;
    name: string;
    code: string;
}

interface Staff {
    id: number;
    name: string;
}

const props = defineProps<{
    inventoryItems: InventoryItem[];
    services: Service[];
    customers: Customer[];
    staff?: Staff[];
}>();

const cart = ref<CartItem[]>([]);
const customerList = ref<Customer[]>([...props.customers]);
const search = ref('');
const paymentMethod = ref('cash');
const customerId = ref<number | null>(null);
const submitting = ref(false);

// New customer modal
const showNewCustomerModal = ref(false);
const newCustomerName = ref('');
const newCustomerPhone = ref('');
const newCustomerError = ref('');
const savingCustomer = ref(false);

const openNewCustomerModal = () => {
    newCustomerName.value = '';
    newCustomerPhone.value = '';
    newCustomerError.value = '';
    showNewCustomerModal.value = true;
};

const saveNewCustomer = async () => {
    if (!newCustomerName.value.trim()) {
        newCustomerError.value = 'Nama customer wajib diisi.';
        return;
    }
    savingCustomer.value = true;
    newCustomerError.value = '';

    try {
        const res = await fetch('/customers/quick-store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                name: newCustomerName.value.trim(),
                phone: newCustomerPhone.value.trim() || null,
            }),
        });

        if (!res.ok) {
            const data = await res.json();
            newCustomerError.value = data.message ?? 'Gagal menyimpan customer.';
            return;
        }

        const newCustomer: Customer = await res.json();
        customerList.value.push(newCustomer);
        customerId.value = newCustomer.id;
        showNewCustomerModal.value = false;
    } catch {
        newCustomerError.value = 'Terjadi kesalahan. Coba lagi.';
    } finally {
        savingCustomer.value = false;
    }
};

const filteredItems = computed(() => {
    let result: any[] = [];

    // Add inventory items
    result = [
        ...result,
        ...props.inventoryItems.map((i) => ({
            ...i,
            _type: 'inventory',
            _display_name: i.product_name,
            _display_sku: i.sku,
            _price: i.selling_price,
        })),
    ];

    // Add services
    if (props.services) {
        result = [
            ...result,
            ...props.services.map((s) => ({
                ...s,
                _type: 'service',
                _display_name: s.name,
                _display_sku: s.code,
                _price: s.price,
            })),
        ];
    }

    if (!search.value) return result;
    const q = search.value.toLowerCase();
    return result.filter((i) => i._display_name.toLowerCase().includes(q) || i._display_sku.toLowerCase().includes(q));
});

const totalAmount = computed(() => cart.value.reduce((sum, item) => sum + item.quantity * item.unit_price, 0));

const totalItems = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0));

const availableQty = (item: any) => {
    if (item._type === 'service') return null; // Unlimited
    return item.current_quantity - item.reserved_quantity;
};

const addToCart = (item: any) => {
    const existing = cart.value.find((c) => c.id === item.id && c.type === item._type);
    const avail = availableQty(item);

    if (existing) {
        if (avail === null || existing.quantity < avail) existing.quantity++;
    } else if (avail === null || avail > 0) {
        cart.value.push({
            id: item.id,
            type: item._type,
            product_name: item._display_name,
            sku: item._display_sku,
            quantity: 1,
            unit_price: item._price,
            available: avail,
            image_url: item.image_url,
            served_by: null,
        });
    }
};

const removeFromCart = (index: number) => {
    cart.value.splice(index, 1);
};

const incrementQty = (item: CartItem) => {
    if (item.available === null || item.quantity < item.available) item.quantity++;
};

const decrementQty = (item: CartItem) => {
    if (item.quantity > 1) {
        item.quantity--;
    } else {
        const idx = cart.value.indexOf(item);
        cart.value.splice(idx, 1);
    }
};

const formatRupiah = (value: number) => 'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });

const checkout = () => {
    if (cart.value.length === 0) return;
    submitting.value = true;
    router.post(
        '/sales-orders/quick-checkout',
        {
            payment_method: paymentMethod.value,
            customer_id: customerId.value || null,
            items: cart.value.map((c) => ({
                inventory_item_id: c.type === 'inventory' ? c.id : null,
                service_id: c.type === 'service' ? c.id : null,
                served_by: c.type === 'service' ? c.served_by || null : null,
                quantity: c.quantity,
                unit_price: c.unit_price,
            })),
        },
        {
            onError: () => {
                submitting.value = false;
            },
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
};
</script>

<template>
    <AppLayout>
        <Head title="Quick Checkout" />

        <div class="flex h-[calc(100vh-4rem)] flex-col overflow-hidden md:flex-row">
            <!-- Left: Product Grid -->
            <div class="flex min-h-0 flex-1 flex-col overflow-hidden bg-gray-100 dark:bg-gray-900">
                <div class="border-b border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                    <h1 class="mb-2 text-lg font-bold text-gray-900 dark:text-gray-100">Quick Checkout</h1>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari produk..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                    />
                </div>

                <div class="flex-1 overflow-y-auto p-3">
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                        <button
                            v-for="item in filteredItems"
                            :key="`${item._type}-${item.id}`"
                            type="button"
                            :disabled="availableQty(item) === 0"
                            @click="addToCart(item)"
                            :class="[
                                'relative overflow-hidden rounded-lg border p-3 text-left transition-all',
                                availableQty(item) === 0
                                    ? 'cursor-not-allowed border-gray-200 bg-white opacity-50 dark:border-gray-700 dark:bg-gray-800'
                                    : 'cursor-pointer border-gray-200 bg-white shadow-sm hover:border-indigo-400 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-500',
                            ]"
                        >
                            <div
                                v-if="item._type === 'service'"
                                class="absolute top-0 right-0 rounded-bl-lg bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400"
                            >
                                JASA
                            </div>
                            <!-- Image container with premium aspect ratio and transition -->
                            <div
                                class="mb-2.5 flex aspect-video w-full items-center justify-center overflow-hidden rounded-md border border-gray-100 bg-gray-100 dark:border-gray-700 dark:bg-gray-700"
                            >
                                <img
                                    v-if="item.image_url"
                                    :src="item.image_url"
                                    :alt="item._display_name"
                                    class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                                />
                                <div v-else class="flex items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg
                                        v-if="item._type === 'inventory'"
                                        class="h-6 w-6 text-gray-400 dark:text-gray-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="h-6 w-6 text-gray-400 dark:text-gray-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <p class="line-clamp-2 text-xs font-semibold text-gray-900 dark:text-gray-100">{{ item._display_name }}</p>
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ item._display_sku }}</p>
                            <p class="mt-1 text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ formatRupiah(item._price) }}</p>
                            <p
                                class="mt-0.5 text-xs"
                                :class="
                                    availableQty(item) !== null && availableQty(item) <= 5 ? 'text-orange-500' : 'text-gray-400 dark:text-gray-500'
                                "
                            >
                                {{ availableQty(item) !== null ? `Stok: ${availableQty(item)}` : 'Unlimited' }}
                            </p>
                        </button>

                        <div v-if="filteredItems.length === 0" class="col-span-full py-12 text-center text-gray-400">Tidak ada produk ditemukan</div>
                    </div>
                </div>
            </div>

            <!-- Right: Cart -->
            <div
                class="flex h-[45vh] flex-col border-t border-gray-200 bg-white md:h-auto md:w-80 md:border-t-0 md:border-l dark:border-gray-700 dark:bg-gray-800"
            >
                <!-- Cart Header -->
                <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <ShoppingCart :size="18" class="text-indigo-600 dark:text-indigo-400" />
                        <span class="font-semibold text-gray-900 dark:text-gray-100">Keranjang</span>
                        <span v-if="totalItems > 0" class="rounded-full bg-indigo-600 px-1.5 py-0.5 text-xs font-bold text-white">{{
                            totalItems
                        }}</span>
                    </div>
                    <button v-if="cart.length > 0" @click="cart = []" class="text-xs text-red-400 hover:text-red-600">Kosongkan</button>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 space-y-2 overflow-y-auto p-3">
                    <div v-if="cart.length === 0" class="py-12 text-center text-sm text-gray-400">Pilih produk untuk ditambah</div>

                    <div
                        v-for="(item, idx) in cart"
                        :key="`${item.type}-${item.id}`"
                        class="flex gap-2 rounded-lg border border-gray-200 p-2 dark:border-gray-700"
                    >
                        <!-- Cart item image thumbnail -->
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-md border border-gray-100 bg-gray-100 dark:border-gray-700 dark:bg-gray-700"
                        >
                            <img v-if="item.image_url" :src="item.image_url" :alt="item.product_name" class="h-full w-full object-cover" />
                            <div v-else class="flex items-center justify-center text-gray-400 dark:text-gray-500">
                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-1">
                                <p class="line-clamp-2 flex-1 text-xs font-medium text-gray-900 dark:text-gray-100">{{ item.product_name }}</p>
                                <button @click="removeFromCart(idx)" class="text-gray-300 hover:text-red-400 dark:text-gray-600">
                                    <X :size="12" />
                                </button>
                            </div>
                            <div class="mt-1.5 flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <button
                                        @click="decrementQty(item)"
                                        class="rounded border border-gray-200 p-0.5 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700"
                                    >
                                        <Minus :size="10" />
                                    </button>
                                    <span class="w-8 text-center text-sm font-bold text-gray-900 dark:text-gray-100">{{ item.quantity }}</span>
                                    <button
                                        @click="incrementQty(item)"
                                        :disabled="item.available !== null && item.quantity >= item.available"
                                        class="rounded border border-gray-200 p-0.5 hover:bg-gray-100 disabled:opacity-40 dark:border-gray-600 dark:hover:bg-gray-700"
                                    >
                                        <Plus :size="10" />
                                    </button>
                                </div>
                                <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ formatRupiah(item.quantity * item.unit_price) }}
                                </p>
                            </div>

                            <!-- Staff selector untuk layanan -->
                            <select
                                v-if="item.type === 'service' && staff && staff.length > 0"
                                v-model="item.served_by"
                                class="mt-1.5 w-full rounded border-gray-200 py-1 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option :value="null">— Pilih staff (opsional) —</option>
                                <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Cart Footer -->
                <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <!-- Total -->
                    <div class="mb-3 flex justify-between">
                        <span class="font-semibold text-gray-700 dark:text-gray-300">Total</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatRupiah(totalAmount) }}</span>
                    </div>

                    <!-- Customer (optional) -->
                    <div class="mb-3">
                        <div class="mb-1 flex items-center justify-between">
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                Customer <span class="font-normal text-gray-400">(opsional)</span>
                            </label>
                            <button
                                type="button"
                                @click="openNewCustomerModal"
                                class="flex cursor-pointer items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                            >
                                <UserPlus :size="12" />
                                Baru
                            </button>
                        </div>
                        <select
                            v-model="customerId"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                        >
                            <option :value="null">Walk-in Customer</option>
                            <option v-for="c in customerList" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <!-- Payment method -->
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Metode Pembayaran</label>
                        <select
                            v-model="paymentMethod"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                        >
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">Debit/Kredit</option>
                        </select>
                    </div>

                    <!-- Checkout button -->
                    <button
                        @click="checkout"
                        :disabled="cart.length === 0 || submitting"
                        class="w-full rounded-lg bg-indigo-600 py-3 text-sm font-bold text-white transition-colors hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ submitting ? 'Memproses...' : `Bayar ${formatRupiah(totalAmount)}` }}
                    </button>
                </div>
            </div>
        </div>
        <!-- New Customer Modal -->
        <Teleport to="body">
            <div
                v-if="showNewCustomerModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="showNewCustomerModal = false"
            >
                <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Customer Baru</h3>
                        <button @click="showNewCustomerModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <X :size="18" />
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Nama <span class="text-red-500">*</span></label
                            >
                            <input
                                v-model="newCustomerName"
                                type="text"
                                placeholder="Nama customer"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                @keyup.enter="saveNewCustomer"
                                autofocus
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >No. HP <span class="font-normal text-gray-400">(opsional)</span></label
                            >
                            <input
                                v-model="newCustomerPhone"
                                type="tel"
                                placeholder="08xx..."
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                @keyup.enter="saveNewCustomer"
                            />
                        </div>
                        <p v-if="newCustomerError" class="text-sm text-red-500">{{ newCustomerError }}</p>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <button
                            type="button"
                            @click="showNewCustomerModal = false"
                            class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="saveNewCustomer"
                            :disabled="savingCustomer"
                            class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ savingCustomer ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
