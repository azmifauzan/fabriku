<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
    permissions: Object,
})

const form = useForm({
    name: '',
    slug: '',
    description: '',
    permission_ids: [],
})

const submit = () => {
    form.post('/admin/roles', {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <Head title="Create Role" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <a href="/admin/roles" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
                <ArrowLeft class="w-4 h-4 mr-2" />
                Back to Roles
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Role</h1>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g., Manager"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g., manager"
                        />
                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            placeholder="Describe this role..."
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Permissions</h3>
                    
                    <div v-for="(perms, module) in permissions" :key="module" class="mb-6 last:mb-0">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 capitalize">{{ module }}</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label
                                v-for="permission in perms"
                                :key="permission.id"
                                class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                            >
                                <input
                                    v-model="form.permission_ids"
                                    type="checkbox"
                                    :value="permission.id"
                                    class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                                />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ permission.name }}</span>
                            </label>
                        </div>
                    </div>
                    <p v-if="form.errors.permission_ids" class="mt-2 text-sm text-red-600">{{ form.errors.permission_ids }}</p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a
                        href="/admin/roles"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Role' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
