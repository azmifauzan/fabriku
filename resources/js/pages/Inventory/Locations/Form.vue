<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

interface Location {
    id?: number;
    code?: string;
    name: string;
    capacity?: number;
    is_active: boolean;
}

const props = defineProps<{
    location?: Location;
}>();

const form = useForm({
    code: props.location?.code || '',
    name: props.location?.name || '',
    capacity: props.location?.capacity || 100,
    is_active: props.location?.is_active ?? true,
});

const submit = () => {
    if (props.location?.id) {
        form.put(`/inventory/locations/${props.location.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/inventory/locations', {
            preserveScroll: true,
        });
    }
};

const isEditing = !!props.location?.id;
</script>

<template>
    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
        <div class="border-b border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ isEditing ? 'Edit Lokasi Inventory' : 'Tambah Lokasi Inventory' }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ isEditing ? 'Ubah informasi lokasi' : 'Tambahkan lokasi inventory baru' }}
                    </p>
                </div>
                <Link href="/inventory/locations" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
                    ← Kembali
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Code (optional, auto-generated if not provided) -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Kode Lokasi </label>
                    <input
                        id="code"
                        v-model="form.code"
                        type="text"
                        placeholder="Otomatis jika kosong"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-300': form.errors.code }"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Biarkan kosong untuk generate otomatis</p>
                    <p v-if="form.errors.code" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.code }}
                    </p>
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Lokasi <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Contoh: Rak A1 - Mukena Grade A"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-300': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Capacity -->
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Kapasitas </label>
                    <input
                        id="capacity"
                        v-model.number="form.capacity"
                        type="number"
                        min="1"
                        placeholder="100"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-300': form.errors.capacity }"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maksimal jumlah item yang dapat disimpan</p>
                    <p v-if="form.errors.capacity" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.capacity }}
                    </p>
                </div>

                <!-- Status (Checkbox) -->
                <div>
                    <label class="flex items-center space-x-3">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                        />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300"> Lokasi Aktif </span>
                    </label>
                    <p class="mt-1 ml-7 text-xs text-gray-500 dark:text-gray-400">Hanya lokasi aktif yang dapat digunakan untuk menyimpan item</p>
                </div>

                <!-- Submit Button -->
                <div
                    class="flex flex-col items-stretch justify-end gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:items-center dark:border-gray-700"
                >
                    <Link
                        href="/inventory/locations"
                        class="order-2 rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-center text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 sm:order-1 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="order-1 rounded-lg border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50 sm:order-2"
                    >
                        {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
