<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingCart, Trash2, X } from 'lucide-vue-next';
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

interface CartItem {
    inventory_item_id: number;
    product_name: string;
    sku: string;
    quantity: number;
    unit_price: number;
    available: number;
    image_url?: string | null;
}

const props = defineProps<{
    inventoryItems: InventoryItem[];
}>();

const cart = ref<CartItem[]>([]);
const search = ref('');
const paymentMethod = ref('cash');
const submitting = ref(false);

const filteredItems = computed(() => {
    if (!search.value) return props.inventoryItems;
    const q = search.value.toLowerCase();
    return props.inventoryItems.filter(
        (i) => i.product_name.toLowerCase().includes(q) || i.sku.toLowerCase().includes(q)
    );
});

const totalAmount = computed(() =>
    cart.value.reduce((sum, item) => sum + item.quantity * item.unit_price, 0)
);

const totalItems = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0));

const availableQty = (item: InventoryItem) => item.current_quantity - item.reserved_quantity;

const addToCart = (item: InventoryItem) => {
    const existing = cart.value.find((c) => c.inventory_item_id === item.id);
    const avail = availableQty(item);
    if (existing) {
        if (existing.quantity < avail) existing.quantity++;
    } else if (avail > 0) {
        cart.value.push({
            inventory_item_id: item.id,
            product_name: item.product_name,
            sku: item.sku,
            quantity: 1,
            unit_price: item.selling_price,
            available: avail,
            image_url: item.image_url,
        });
    }
};

const removeFromCart = (index: number) => {
    cart.value.splice(index, 1);
};

const incrementQty = (item: CartItem) => {
    if (item.quantity < item.available) item.quantity++;
};

const decrementQty = (item: CartItem) => {
    if (item.quantity > 1) {
        item.quantity--;
    } else {
        const idx = cart.value.indexOf(item);
        cart.value.splice(idx, 1);
    }
};

const formatRupiah = (value: number) =>
    'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });

const checkout = () => {
    if (cart.value.length === 0) return;
    submitting.value = true;
    router.post(
        '/sales-orders/quick-checkout',
        {
            payment_method: paymentMethod.value,
            items: cart.value.map((c) => ({
                inventory_item_id: c.inventory_item_id,
                quantity: c.quantity,
                unit_price: c.unit_price,
            })),
        },
        {
            onError: () => { submitting.value = false; },
            onFinish: () => { submitting.value = false; },
        }
    );
};
</script>

<template>
    <AppLayout>
        <Head title="Quick Checkout" />

        <div class="flex h-[calc(100vh-4rem)] gap-0 overflow-hidden">
            <!-- Left: Product Grid -->
            <div class="flex flex-1 flex-col overflow-hidden bg-gray-100 dark:bg-gray-900">
                <div class="border-b border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                    <h1 class="mb-2 text-lg font-bold text-gray-900 dark:text-gray-100">Quick Checkout</h1>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari produk..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                    />
                </div>

                <div class="flex-1 overflow-y-auto p-3">
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                        <button
                            v-for="item in filteredItems"
                            :key="item.id"
                            type="button"
                            :disabled="availableQty(item) === 0"
                            @click="addToCart(item)"
                            :class="[
                                'rounded-lg border p-3 text-left transition-all',
                                availableQty(item) === 0
                                    ? 'cursor-not-allowed border-gray-200 bg-white opacity-50 dark:border-gray-700 dark:bg-gray-800'
                                    : 'cursor-pointer border-gray-200 bg-white shadow-sm hover:border-indigo-400 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-500',
                            ]"
                        >
                            <!-- Image container with premium aspect ratio and transition -->
                            <div class="mb-2.5 aspect-video w-full overflow-hidden rounded-md bg-gray-100 flex items-center justify-center border border-gray-100 dark:bg-gray-700 dark:border-gray-700">
                                <img
                                    v-if="item.image_url"
                                    :src="item.image_url"
                                    :alt="item.product_name"
                                    class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                                />
                                <div v-else class="flex items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>

                            <p class="text-xs font-semibold text-gray-900 line-clamp-2 dark:text-gray-100">{{ item.product_name }}</p>
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ item.sku }}</p>
                            <p class="mt-1 text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ formatRupiah(item.selling_price) }}</p>
                            <p class="mt-0.5 text-xs" :class="availableQty(item) <= 5 ? 'text-orange-500' : 'text-gray-400 dark:text-gray-500'">
                                Stok: {{ availableQty(item) }}
                            </p>
                        </button>

                        <div v-if="filteredItems.length === 0" class="col-span-full py-12 text-center text-gray-400">
                            Tidak ada produk ditemukan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Cart -->
            <div class="flex w-80 flex-col border-l border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <!-- Cart Header -->
                <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <ShoppingCart :size="18" class="text-indigo-600 dark:text-indigo-400" />
                        <span class="font-semibold text-gray-900 dark:text-gray-100">Keranjang</span>
                        <span
                            v-if="totalItems > 0"
                            class="rounded-full bg-indigo-600 px-1.5 py-0.5 text-xs font-bold text-white"
                        >{{ totalItems }}</span>
                    </div>
                    <button
                        v-if="cart.length > 0"
                        @click="cart = []"
                        class="text-xs text-red-400 hover:text-red-600"
                    >
                        Kosongkan
                    </button>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <div v-if="cart.length === 0" class="py-12 text-center text-gray-400 text-sm">
                        Pilih produk untuk ditambah
                    </div>

                    <div
                        v-for="(item, idx) in cart"
                        :key="item.inventory_item_id"
                        class="rounded-lg border border-gray-200 p-2 dark:border-gray-700 flex gap-2"
                    >
                        <!-- Cart item image thumbnail -->
                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-md bg-gray-100 flex items-center justify-center border border-gray-100 dark:bg-gray-700 dark:border-gray-700">
                            <img
                                v-if="item.image_url"
                                :src="item.image_url"
                                :alt="item.product_name"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex items-center justify-center text-gray-400 dark:text-gray-500">
                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-1">
                                <p class="flex-1 text-xs font-medium text-gray-900 dark:text-gray-100 line-clamp-2">{{ item.product_name }}</p>
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
                                        :disabled="item.quantity >= item.available"
                                        class="rounded border border-gray-200 p-0.5 hover:bg-gray-100 disabled:opacity-40 dark:border-gray-600 dark:hover:bg-gray-700"
                                    >
                                        <Plus :size="10" />
                                    </button>
                                </div>
                                <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ formatRupiah(item.quantity * item.unit_price) }}
                                </p>
                            </div>
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

                    <!-- Payment method -->
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Metode Pembayaran</label>
                        <select
                            v-model="paymentMethod"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
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
                        class="w-full rounded-lg bg-indigo-600 py-3 text-sm font-bold text-white hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                    >
                        {{ submitting ? 'Memproses...' : `Bayar ${formatRupiah(totalAmount)}` }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
