<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ref } from 'vue'
import { Search, Filter } from 'lucide-vue-next'

const props = defineProps({
    logs: Object,
    filters: Object,
})

const date_from = ref(props.filters?.date_from || '')
const date_to = ref(props.filters?.date_to || '')
const event = ref(props.filters?.event || '')
const auditable_type = ref(props.filters?.auditable_type || '')

const applyFilters = () => {
    router.get('/admin/audit-logs', {
        date_from: date_from.value,
        date_to: date_to.value,
        event: event.value,
        auditable_type: auditable_type.value,
    }, {
        preserveState: true,
        replace: true,
    })
}

const clearFilters = () => {
    date_from.value = ''
    date_to.value = ''
    event.value = ''
    auditable_type.value = ''
    applyFilters()
}

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
    <Head title="Audit Logs" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Logs</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View system activity and changes</p>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                    <input
                        v-model="date_from"
                        @change="applyFilters"
                        type="date"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                    />
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                    <input
                        v-model="date_to"
                        @change="applyFilters"
                        type="date"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                    />
                </div>

                <!-- Event Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event</label>
                    <select
                        v-model="event"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="">All Events</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select
                        v-model="auditable_type"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="">All Types</option>
                        <option value="App\Models\Tenant">Tenant</option>
                        <option value="App\Models\User">User</option>
                        <option value="App\Models\Material">Material</option>
                        <option value="App\Models\Pattern">Pattern</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 flex justify-end">
                <button
                    @click="clearFilters"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                >
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full capitalize',
                                        getEventColor(log.event)
                                    ]"
                                >
                                    {{ log.event }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ log.auditable_type?.split('\\').pop() || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ log.user?.name || 'System' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ log.tenant?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ new Date(log.created_at).toLocaleString() }}
                            </td>
                            <td class="px-6 py-4">
                                <Link
                                    :href="`/admin/audit-logs/${log.id}`"
                                    class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 text-sm font-medium"
                                >
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.links" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ logs.from }} to {{ logs.to }} of {{ logs.total }} results
                    </div>
                    <div class="flex space-x-1">
                        <template v-for="(link, index) in logs.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 text-sm rounded',
                                    link.active
                                        ? 'bg-purple-600 text-white'
                                        : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                :class="[
                                    'px-3 py-1 text-sm rounded bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
