<template>
    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
        <form @submit.prevent="submit" class="space-y-8 p-6">
            <!-- Order Information Section -->
            <div>
                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Pesanan</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Customer <span class="text-red-600">*</span>
                        </label>
                        <select
                            id="customer_id"
                            v-model="form.customer_id"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': form.errors.customer_id }"
                        >
                            <option value="">Pilih Customer</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.code }} - {{ customer.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.customer_id" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.customer_id }}
                        </p>
                    </div>

                    <div>
                        <label for="order_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Order <span class="text-red-600">*</span>
                        </label>
                        <input
                            id="order_date"
                            v-model="form.order_date"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': form.errors.order_date }"
                        />
                        <p v-if="form.errors.order_date" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.order_date }}
                        </p>
                    </div>

                    <div>
                        <label for="channel" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Channel <span class="text-red-600">*</span>
                        </label>
                        <select
                            id="channel"
                            v-model="form.channel"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': form.errors.channel }"
                        >
                            <option value="">Pilih Channel</option>
                            <option value="offline">Offline</option>
                            <option value="online">Online</option>
                            <option value="reseller">Reseller</option>
                            <option value="marketplace">Marketplace</option>
                        </select>
                        <p v-if="form.errors.channel" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.channel }}
                        </p>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Status </label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="draft">Draft</option>
                            <option value="confirmed">Dikonfirmasi</option>
                            <option value="processing">Proses</option>
                            <option value="shipped">Dikirim</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Item Pesanan <span class="text-red-600">*</span></h3>
                    <button
                        type="button"
                        @click="addItem"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm leading-4 font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                    >
                        + Tambah Item
                    </button>
                </div>

                <p v-if="form.errors.items" class="mb-2 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.items }}
                </p>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                    style="width: 35%"
                                >
                                    Produk
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                    style="width: 10%"
                                >
                                    Qty
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                    style="width: 15%"
                                >
                                    Harga
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                    style="width: 15%"
                                >
                                    Diskon
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                    style="width: 20%"
                                >
                                    Subtotal
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                    style="width: 5%"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-for="(item, index) in form.items" :key="index">
                                <td class="px-4 py-3">
                                    <select
                                        v-model="item.inventory_item_id"
                                        @change="onInventoryItemChange(index)"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': form.errors[`items.${index}.inventory_item_id`] }"
                                    >
                                        <option value="">Pilih Produk</option>
                                        <option v-for="invItem in inventoryItems" :key="invItem.id" :value="invItem.id">
                                            {{ invItem.sku }} - {{ invItem.pattern?.name }} ({{ invItem.current_stock - invItem.reserved_stock }}
                                            available)
                                        </option>
                                    </select>
                                    <p v-if="form.errors[`items.${index}.inventory_item_id`]" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        {{ form.errors[`items.${index}.inventory_item_id`] }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        v-model.number="item.quantity"
                                        @input="calculateItemSubtotal(index)"
                                        type="number"
                                        min="1"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-right text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': form.errors[`items.${index}.quantity`] }"
                                    />
                                    <p v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        {{ form.errors[`items.${index}.quantity`] }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        v-model.number="item.unit_price"
                                        @input="calculateItemSubtotal(index)"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-right text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': form.errors[`items.${index}.unit_price`] }"
                                    />
                                    <p v-if="form.errors[`items.${index}.unit_price`]" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        {{ form.errors[`items.${index}.unit_price`] }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        v-model.number="item.discount_amount"
                                        @input="calculateItemSubtotal(index)"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-right text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                    Rp {{ Number(item.subtotal || 0).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        :disabled="form.items.length === 1"
                                        class="text-red-600 hover:text-red-900 disabled:cursor-not-allowed disabled:text-gray-400 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        ×
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Calculations Section -->
            <div class="rounded-lg bg-gray-50 p-6 dark:bg-gray-900">
                <div class="ml-auto max-w-md space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700 dark:text-gray-300">Subtotal</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100"> Rp {{ calculatedSubtotal.toLocaleString('id-ID') }} </span>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center gap-4">
                            <label class="text-sm text-gray-700 dark:text-gray-300">Diskon</label>
                            <div class="flex gap-2">
                                <input
                                    v-model.number="form.discount_percentage"
                                    @input="calculateTotals"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    placeholder="0"
                                    class="w-20 rounded-lg border border-gray-300 px-3 py-2.5 text-right text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <span class="text-sm text-gray-700 dark:text-gray-300">%</span>
                            </div>
                        </div>
                        <div class="flex justify-between text-sm text-red-600 dark:text-red-400">
                            <span></span>
                            <span>- Rp {{ calculatedDiscountAmount.toLocaleString('id-ID') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label for="tax_amount" class="text-sm text-gray-700 dark:text-gray-300">Pajak</label>
                        <input
                            id="tax_amount"
                            v-model.number="form.tax_amount"
                            @input="calculateTotals"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-40 rounded-lg border border-gray-300 px-4 py-2.5 text-right text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <div class="flex justify-between border-t border-gray-300 pt-3 text-base font-bold dark:border-gray-600">
                        <span class="text-gray-900 dark:text-gray-100">Total</span>
                        <span class="text-gray-900 dark:text-gray-100"> Rp {{ calculatedTotal.toLocaleString('id-ID') }} </span>
                    </div>
                </div>
            </div>

            <!-- Payment Information Section -->
            <div>
                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Pembayaran</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Metode Pembayaran <span class="text-red-600">*</span>
                        </label>
                        <select
                            id="payment_method"
                            v-model="form.payment_method"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            :class="{ 'border-red-500': form.errors.payment_method }"
                        >
                            <option value="">Pilih Metode</option>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="credit_card">Kartu Kredit</option>
                            <option value="qris">QRIS</option>
                            <option value="cod">COD</option>
                        </select>
                        <p v-if="form.errors.payment_method" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.payment_method }}
                        </p>
                    </div>

                    <div>
                        <label for="payment_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Status Pembayaran </label>
                        <select
                            id="payment_status"
                            v-model="form.payment_status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                        >
                            <option value="unpaid">Belum Dibayar</option>
                            <option value="partial">Dibayar Sebagian</option>
                            <option value="paid">Lunas</option>
                        </select>
                    </div>

                    <div>
                        <label for="paid_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Jumlah Dibayar </label>
                        <input
                            id="paid_amount"
                            v-model.number="form.paid_amount"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>

                    <div>
                        <label for="shipping_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Alamat Pengiriman </label>
                        <textarea
                            id="shipping_address"
                            v-model="form.shipping_address"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Catatan </label>
                <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                ></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                <Link
                    :href="salesOrder ? `/sales-orders/${salesOrder.id}` : '/sales-orders'"
                    class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-25 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Batal
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900"
                    :class="{ 'cursor-not-allowed opacity-50': form.processing }"
                >
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>{{ salesOrder ? 'Update' : 'Simpan' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    salesOrder: {
        type: Object,
        default: null,
    },
    customers: {
        type: Array,
        required: true,
    },
    inventoryItems: {
        type: Array,
        required: true,
    },
});

// Get today's date in YYYY-MM-DD format
const today = new Date().toISOString().split('T')[0];

const form = useForm({
    customer_id: props.salesOrder?.customer_id || '',
    order_date: props.salesOrder?.order_date || today,
    channel: props.salesOrder?.channel || 'offline',
    status: props.salesOrder?.status || 'draft',
    payment_method: props.salesOrder?.payment_method || 'cash',
    payment_status: props.salesOrder?.payment_status || 'unpaid',
    paid_amount: props.salesOrder?.paid_amount || 0,
    discount_percentage: props.salesOrder?.discount_percentage || 0,
    tax_amount: props.salesOrder?.tax_amount || 0,
    shipping_address: props.salesOrder?.shipping_address || '',
    notes: props.salesOrder?.notes || '',
    items: props.salesOrder?.items
        ? props.salesOrder.items.map((item) => ({
              inventory_item_id: item.inventory_item_id,
              quantity: item.quantity,
              unit_price: item.unit_price,
              discount_amount: item.discount_amount || 0,
              subtotal: item.subtotal,
          }))
        : [
              {
                  inventory_item_id: '',
                  quantity: 1,
                  unit_price: 0,
                  discount_amount: 0,
                  subtotal: 0,
              },
          ],
});

const calculatedSubtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.subtotal) || 0), 0);
});

const calculatedDiscountAmount = computed(() => {
    if (!form.discount_percentage) return 0;
    return Math.round(calculatedSubtotal.value * (form.discount_percentage / 100));
});

const calculatedTotal = computed(() => {
    return calculatedSubtotal.value - calculatedDiscountAmount.value + (Number(form.tax_amount) || 0);
});

function calculateItemSubtotal(index) {
    const item = form.items[index];
    const quantity = Number(item.quantity) || 0;
    const unitPrice = Number(item.unit_price) || 0;
    const discount = Number(item.discount_amount) || 0;

    item.subtotal = quantity * unitPrice - discount;
}

function calculateTotals() {
    // This function is called when discount or tax changes
    // Computed properties automatically handle the recalculation
}

function onInventoryItemChange(index) {
    const item = form.items[index];
    const selectedItem = props.inventoryItems.find((inv) => inv.id === item.inventory_item_id);

    if (selectedItem) {
        item.unit_price = selectedItem.selling_price || 0;
        calculateItemSubtotal(index);
    }
}

function addItem() {
    form.items.push({
        inventory_item_id: '',
        quantity: 1,
        unit_price: 0,
        discount_amount: 0,
        subtotal: 0,
    });
}

function removeItem(index) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

function submit() {
    const url = props.salesOrder ? `/sales-orders/${props.salesOrder.id}` : '/sales-orders';

    const method = props.salesOrder ? 'put' : 'post';

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            // Form will redirect automatically on success
        },
    });
}
</script>
