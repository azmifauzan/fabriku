<template>
    <Modal :show="show" @close="close" max-width="md">
        <div class="px-6 py-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tambah Customer Baru</h3>
            
            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Customer <span class="text-red-600">*</span></label>
                    <input
                        id="code"
                        v-model="form.code"
                        type="text"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.code }"
                    />
                    <p v-if="form.errors.code" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.code }}</p>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama <span class="text-red-600">*</span></label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. Telepon / WA</label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.phone }"
                    />
                    <p v-if="form.errors.phone" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone }}</p>
                </div>

                <div>
                     <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                     <input
                         id="email"
                         v-model="form.email"
                         type="email"
                         class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                         :class="{ 'border-red-500': form.errors.email }"
                     />
                     <p v-if="form.errors.email" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
                 </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe</label>
                    <select
                        id="type"
                        v-model="form.type"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.type }"
                    >
                        <option value="retail">Retail</option>
                        <option value="reseller">Reseller</option>
                        <option value="distributor">Distributor</option>
                    </select>
                     <p v-if="form.errors.type" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.type }}</p>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="2"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.address }"
                    ></textarea>
                    <p v-if="form.errors.address" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.address }}</p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="close"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['close', 'created']);

// We use manual axios here to get JSON response without Inertia redirecting
const form = reactive({
    code: '',
    name: '',
    phone: '',
    email: '',
    type: 'retail',
    address: '',
    processing: false,
    errors: {} as Record<string, string>,
});

const close = () => {
    form.code = '';
    form.name = '';
    form.phone = '';
    form.email = '';
    form.type = 'retail';
    form.address = '';
    form.errors = {};
    emit('close');
};

const submit = () => {
    form.processing = true;
    form.errors = {};

    axios.post('/customers', {
        code: form.code,
        name: form.name,
        phone: form.phone,
        email: form.email,
        type: form.type,
        address: form.address,
    })
    .then(response => {
        form.processing = false;
        // The response structure based on our controller update
        if (response.data.customer) {
            emit('created', response.data.customer);
            close();
        }
    })
    .catch(error => {
        form.processing = false;
        if (error.response && error.response.status === 422) {
            // Validation errors
            form.errors = error.response.data.errors;
            // Map array errors to single string if needed, mostly Laravel returns array of strings
            for (const key in form.errors) {
                 if (Array.isArray(form.errors[key])) {
                     form.errors[key] = form.errors[key][0];
                 }
            }
        } else {
            console.error('Error creating customer:', error);
        }
    });
};
</script>
