<script setup lang="ts">
import { useSweetAlert } from '@/composables/useSweetAlert';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const { showSuccess } = useSweetAlert();

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put('/admin/account/password', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showSuccess('Password berhasil diubah.');
        },
        onError: () => form.reset('password', 'password_confirmation'),
    });
};

const inputClass =
    'w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white';
</script>

<template>
    <AdminLayout>
        <Head title="Ubah Password" />

        <div class="max-w-xl">
            <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">Ubah Password</h1>

            <form
                @submit.prevent="submit"
                class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
                <div>
                    <label for="current_password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Password Saat Ini</label>
                    <input
                        id="current_password"
                        v-model="form.current_password"
                        type="password"
                        autocomplete="current-password"
                        :class="inputClass"
                    />
                    <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-600">{{ form.errors.current_password }}</p>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
                    <input id="password" v-model="form.password" type="password" autocomplete="new-password" :class="inputClass" />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Minimal 12 karakter.</p>
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Konfirmasi Password Baru</label
                    >
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        :class="inputClass"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-purple-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-purple-700 disabled:opacity-50"
                >
                    Simpan Password
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
