<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeft, User, Building2, Calendar, Activity } from 'lucide-vue-next'

const props = defineProps({
    log: Object,
})

const getEventColor = (eventType) => {
    const colors = {
        created: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200',
        updated: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200',
        deleted: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200',
    }
    return colors[eventType] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
}
</script>

<template>
    <Head title="Audit Log Details" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <a href="/admin/audit-logs" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
                <ArrowLeft class="w-4 h-4 mr-2" />
                Back to Audit Logs
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Log Details</h1>
        </div>

        <!-- Log Overview -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <Activity class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Event Information</h2>
                    </div>
                    <span
                        :class="[
                            'px-3 py-1 text-sm font-medium rounded-full capitalize',
                            getEventColor(log.event)
                        ]"
                    >
                        {{ log.event }}
                    </span>
                </div>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400">
                            <User class="w-4 h-4 mr-2" />
                            Performed By
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ log.user?.name || 'System' }}
                            <span v-if="log.user?.email" class="text-gray-600 dark:text-gray-400">({{ log.user.email }})</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400">
                            <Building2 class="w-4 h-4 mr-2" />
                            Tenant
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ log.tenant?.name || 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Model Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ log.auditable_type?.split('\\').pop() || 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Model ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ log.auditable_id || 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400">
                            <Calendar class="w-4 h-4 mr-2" />
                            Timestamp
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ new Date(log.created_at).toLocaleString() }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ log.ip_address || 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Old Values -->
        <div v-if="log.old_values && Object.keys(log.old_values).length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Old Values</h2>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm text-gray-900 dark:text-white">{{ JSON.stringify(log.old_values, null, 2) }}</pre>
                </div>
            </div>
        </div>

        <!-- New Values -->
        <div v-if="log.new_values && Object.keys(log.new_values).length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">New Values</h2>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm text-gray-900 dark:text-white">{{ JSON.stringify(log.new_values, null, 2) }}</pre>
                </div>
            </div>
        </div>

        <!-- Changes Comparison -->
        <div v-if="log.event === 'updated' && log.old_values && log.new_values" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Changes Made</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Field</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Old Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">New Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="(value, key) in log.new_values" :key="key">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ key }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ log.old_values[key] !== undefined ? log.old_values[key] : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ value }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
