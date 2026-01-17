<script setup lang="ts">
import { useBusinessContext } from '@/composables/useBusinessContext';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Pattern {
    id?: number;
    code: string;
    name: string;
    description?: string;
    is_active?: boolean;
}

interface Props {
    pattern?: Pattern;
    isEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false,
});

const { term } = useBusinessContext();

const patternLabel = computed(() => term('pattern', 'Pattern'));

const form = useForm({
    code: props.pattern?.code || '',
    name: props.pattern?.name || '',
    description: props.pattern?.description || '',
    is_active: props.pattern?.is_active !== undefined ? props.pattern.is_active : true,
});

const submit = () => {
    if (props.isEdit && props.pattern?.id) {
        form.put(`/patterns/${props.pattern.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/patterns', {
            preserveScroll: true,
        });
    }
};

const goBack = () => {
    router.visit('/patterns');
};
</script>

<template>
    <AppLayout>
        <Head :title="isEdit ? `Edit ${patternLabel}` : `Buat ${patternLabel} Baru`" />

        <!-- Page Content -->
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="border-b border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ isEdit ? `Edit ${patternLabel}` : `Buat ${patternLabel} Baru` }}
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ isEdit ? `Ubah informasi ${patternLabel.toLowerCase()}` : `Tambahkan ${patternLabel.toLowerCase()} baru` }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="goBack"
                                class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                            >
                                ← Kembali
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Pattern Information -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Informasi {{ patternLabel }}</h3>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <!-- Code -->
                                    <div>
                                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Kode {{ patternLabel }} <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            id="code"
                                            v-model="form.code"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            required
                                        />
                                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.code }}
                                        </p>
                                    </div>

                                    <!-- Name -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nama {{ patternLabel }} <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            required
                                        />
                                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.name }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Deskripsi </label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    ></textarea>
                                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.description }}
                                    </p>
                                </div>

                                <!-- Status Aktif -->
                                <div class="flex items-center gap-3">
                                    <input
                                        id="is_active"
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ patternLabel }} Aktif
                                        <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">(Dapat digunakan untuk preparation order)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-6 flex flex-col items-stretch justify-end gap-3 sm:flex-row sm:items-center">
                                <button
                                    type="button"
                                    @click="goBack"
                                    class="order-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 sm:order-1 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-600"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="order-1 inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 sm:order-2"
                                >
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
