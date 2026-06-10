<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    role: Object,
    permissions: Object,
});

const form = useForm({
    name: props.role.name,
    slug: props.role.slug,
    description: props.role.description || '',
    permission_ids: props.role.permissions?.map((p) => p.id) || [],
});

const submit = () => {
    form.put(`/admin/roles/${props.role.id}`);
};
</script>

<template>
    <Head title="Edit Role" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <a
                :href="`/admin/roles/${role.id}`"
                class="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Role
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Role</h1>
        </div>

        <!-- Form -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Name -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            :disabled="role.is_system_role"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            :disabled="role.is_system_role"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Description </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Permissions</h3>

                    <div v-for="(perms, module) in permissions" :key="module" class="mb-6 last:mb-0">
                        <h4 class="mb-3 text-sm font-medium text-gray-700 capitalize dark:text-gray-300">{{ module }}</h4>
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                            <label
                                v-for="permission in perms"
                                :key="permission.id"
                                class="flex cursor-pointer items-center rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                            >
                                <input
                                    v-model="form.permission_ids"
                                    type="checkbox"
                                    :value="permission.id"
                                    class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ permission.name }}</span>
                            </label>
                        </div>
                    </div>
                    <p v-if="form.errors.permission_ids" class="mt-2 text-sm text-red-600">{{ form.errors.permission_ids }}</p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <a
                        :href="`/admin/roles/${role.id}`"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Updating...' : 'Update Role' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
