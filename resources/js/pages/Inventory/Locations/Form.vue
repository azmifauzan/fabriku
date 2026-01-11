<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

interface Location {
    id?: number;
    name: string;
    zone: string;
    rack: string;
    description?: string;
    capacity?: number;
    status: string;
}

const props = defineProps<{
    location?: Location;
    zones: string[];
    statuses: Record<string, string>;
}>();

const form = useForm({
    name: props.location?.name || '',
    zone: props.location?.zone || '',
    rack: props.location?.rack || '',
    description: props.location?.description || '',
    capacity: props.location?.capacity || 100,
    status: props.location?.status || 'active',
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
    <div>
        <!-- Header -->
        <div class="mb-6">
            <Link
                href="/inventory/locations"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mb-4"
            >
                <svg
                    class="w-4 h-4 mr-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
                Kembali ke Daftar
            </Link>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ isEditing ? 'Edit Lokasi Inventory' : 'Tambah Lokasi Inventory' }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ isEditing ? 'Ubah informasi lokasi' : 'Tambahkan lokasi inventory baru' }}
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <form @submit.prevent="submit" class="space-y-6 p-6">
                <!-- Name -->
                <div>
                    <label
                        for="name"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Nama Lokasi <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Contoh: Rak A1"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        :class="{ 'border-red-300': form.errors.name }"
                    />
                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Zone & Rack (Row) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            for="zone"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Zone <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="zone"
                            v-model="form.zone"
                            required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            :class="{ 'border-red-300': form.errors.zone }"
                        >
                            <option value="">Pilih Zone</option>
                            <option v-for="zone in zones" :key="zone" :value="zone">
                                Zone {{ zone }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.zone"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.zone }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="rack"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Rak <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="rack"
                            v-model="form.rack"
                            type="text"
                            required
                            placeholder="Contoh: 1"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            :class="{ 'border-red-300': form.errors.rack }"
                        />
                        <p
                            v-if="form.errors.rack"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.rack }}
                        </p>
                    </div>
                </div>

                <!-- Capacity & Status (Row) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            for="capacity"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Kapasitas <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="capacity"
                            v-model.number="form.capacity"
                            type="number"
                            required
                            min="1"
                            placeholder="100"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            :class="{ 'border-red-300': form.errors.capacity }"
                        />
                        <p
                            v-if="form.errors.capacity"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.capacity }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Jumlah maksimal item yang bisa disimpan
                        </p>
                    </div>

                    <div>
                        <label
                            for="status"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status"
                            v-model="form.status"
                            required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            :class="{ 'border-red-300': form.errors.status }"
                        >
                            <option
                                v-for="(label, value) in statuses"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.status"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.status }}
                        </p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label
                        for="description"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Deskripsi
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        placeholder="Deskripsi lokasi (opsional)"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        :class="{ 'border-red-300': form.errors.description }"
                    />
                    <p
                        v-if="form.errors.description"
                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <Link
                        href="/inventory/locations"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update Lokasi' : 'Tambah Lokasi' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
