<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ customer ? 'Edit Customer' : 'Tambah Customer Baru' }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ customer ? 'Ubah informasi customer' : 'Tambah data customer untuk penjualan produk' }}
                    </p>
                </div>
                <Link
                    :href="customer ? `/customers/${customer.id}` : '/customers'"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300"
                >
                    ← Kembali
                </Link>
            </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Kode Customer <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.code"
                            type="text"
                            required
                            placeholder="CUST-001"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            :class="{ 'border-red-500': form.errors.code }"
                        />
                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Customer <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tipe Customer <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.type"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            :class="{ 'border-red-500': form.errors.type }"
                        >
                            <option value="retail">Retail</option>
                            <option value="reseller">Reseller</option>
                            <option value="online">Online</option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.is_active"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                        >
                            <option :value="true">Aktif</option>
                            <option :value="false">Non-Aktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informasi Kontak</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Telepon
                        </label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            :class="{ 'border-red-500': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Alamat
                        </label>
                        <textarea
                            v-model="form.address"
                            rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            :class="{ 'border-red-500': form.errors.address }"
                        />
                        <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Kota
                        </label>
                        <input
                            v-model="form.city"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            :class="{ 'border-red-500': form.errors.city }"
                        />
                        <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Provinsi
                        </label>
                        <input
                            v-model="form.province"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            :class="{ 'border-red-500': form.errors.province }"
                        />
                        <p v-if="form.errors.province" class="mt-1 text-sm text-red-600">{{ form.errors.province }}</p>
                    </div>
                </div>
            </div>

            <!-- Pricing & Terms -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Harga & Term</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Diskon Default (%) <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model.number="form.discount_percentage"
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            :class="{ 'border-red-500': form.errors.discount_percentage }"
                        />
                        <p v-if="form.errors.discount_percentage" class="mt-1 text-sm text-red-600">{{ form.errors.discount_percentage }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Term Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.payment_term"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            :class="{ 'border-red-500': form.errors.payment_term }"
                        >
                            <option value="cash">Cash</option>
                            <option value="credit_7">Kredit 7 Hari</option>
                            <option value="credit_14">Kredit 14 Hari</option>
                            <option value="credit_30">Kredit 30 Hari</option>
                        </select>
                        <p v-if="form.errors.payment_term" class="mt-1 text-sm text-red-600">{{ form.errors.payment_term }}</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Catatan
                </label>
                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                    :class="{ 'border-red-500': form.errors.notes }"
                />
                <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <Link
                    :href="customer ? `/customers/${customer.id}` : '/customers'"
                    class="order-2 sm:order-1 px-4 py-2 text-sm font-medium text-center text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Batal
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="order-1 sm:order-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ form.processing ? 'Menyimpan...' : (customer ? 'Update Customer' : 'Simpan Customer') }}
                </button>
            </div>
        </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    customer: Object
});

const form = useForm({
    code: props.customer?.code || '',
    name: props.customer?.name || '',
    type: props.customer?.type || 'retail',
    phone: props.customer?.phone || '',
    email: props.customer?.email || '',
    address: props.customer?.address || '',
    city: props.customer?.city || '',
    province: props.customer?.province || '',
    discount_percentage: props.customer?.discount_percentage || 0,
    payment_term: props.customer?.payment_term || 'cash',
    notes: props.customer?.notes || '',
    is_active: props.customer?.is_active ?? true
});

function submit() {
    if (props.customer) {
        form.put(`/customers/${props.customer.id}`, {
            preserveScroll: true
        });
    } else {
        form.post('/customers', {
            preserveScroll: true
        });
    }
}
</script>
