<script setup>
import {
    deleteJob as deleteJobAction,
    flushJobs as flushJobsAction,
    index as monitoringIndex,
    retryAllJobs as retryAllJobsAction,
    retryJob as retryJobAction,
    runCommand as runCommandAction,
    testTelegram as testTelegramAction,
} from '@/actions/App/Http/Controllers/Admin/AdminMonitoringController';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Activity, Clock, Cpu, Database, HardDrive, Mail, MemoryStick, Play, RefreshCw, Send, Server, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    tab: String,
    overview: Object,
    schedules: Object,
    emails: Object,
    jobs: Object,
    server: Object,
});

const activeTab = ref(props.tab || 'overview');

const tabs = [
    { id: 'overview', name: 'Overview', icon: Activity },
    { id: 'schedules', name: 'Schedules', icon: Clock },
    { id: 'emails', name: 'Emails', icon: Mail },
    { id: 'jobs', name: 'Queue Jobs', icon: RefreshCw },
    { id: 'server', name: 'Server', icon: Server },
];

const switchTab = (tabId) => {
    activeTab.value = tabId;
    router.get(
        monitoringIndex.url(),
        { tab: tabId },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const retryJob = (uuid) => {
    if (confirm('Retry this failed job?')) {
        router.post(retryJobAction.url(uuid));
    }
};

const deleteJob = (uuid) => {
    if (confirm('Delete this failed job?')) {
        router.delete(deleteJobAction.url(uuid));
    }
};

const retryAllJobs = () => {
    if (confirm('Retry all failed jobs?')) {
        router.post(retryAllJobsAction.url());
    }
};

const flushJobs = () => {
    if (confirm('Delete ALL failed jobs? This cannot be undone.')) {
        router.post(flushJobsAction.url());
    }
};

const runCommand = (command) => {
    if (confirm(`Run "${command}" command now?`)) {
        router.post(runCommandAction.url(), { command });
    }
};

const testTelegram = () => {
    router.post(testTelegramAction.url());
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const statusColor = (status) => {
    switch (status) {
        case 'sent':
        case 'completed':
        case 'connected':
        case 'working':
            return 'text-green-600 bg-green-100 dark:text-green-400 dark:bg-green-900/30';
        case 'failed':
        case 'error':
            return 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900/30';
        case 'running':
        case 'sending':
            return 'text-blue-600 bg-blue-100 dark:text-blue-400 dark:bg-blue-900/30';
        default:
            return 'text-gray-600 bg-gray-100 dark:text-gray-400 dark:bg-gray-900/30';
    }
};
</script>

<template>
    <Head title="System Monitoring" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">System Monitoring</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Monitor schedules, emails, queues, and server resources</p>
            </div>
            <button @click="testTelegram" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                <Send class="h-4 w-4" />
                Test Telegram
            </button>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav class="flex gap-4">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="switchTab(tab.id)"
                    :class="[
                        'flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-medium transition-colors',
                        activeTab === tab.id
                            ? 'border-purple-600 text-purple-600 dark:text-purple-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                    ]"
                >
                    <component :is="tab.icon" class="h-4 w-4" />
                    {{ tab.name }}
                </button>
            </nav>
        </div>

        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Queue Jobs -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Queue Jobs</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ overview.queue_jobs }}</p>
                            <p class="mt-1 text-sm text-red-600" v-if="overview.failed_jobs > 0">{{ overview.failed_jobs }} failed</p>
                            <p class="mt-1 text-sm text-green-600" v-else>All good</p>
                        </div>
                        <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/30">
                            <RefreshCw class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <!-- Emails Today -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Emails Today</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ overview.emails_today }}</p>
                            <p class="mt-1 text-sm text-red-600" v-if="overview.emails_failed > 0">{{ overview.emails_failed }} failed</p>
                            <p class="mt-1 text-sm text-green-600" v-else>All sent</p>
                        </div>
                        <div class="rounded-lg bg-green-100 p-3 dark:bg-green-900/30">
                            <Mail class="h-8 w-8 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <!-- Scheduled Tasks -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Schedule Status</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ overview.schedules_running }}</p>
                            <p class="mt-1 text-sm text-red-600" v-if="overview.schedules_failed_today > 0">
                                {{ overview.schedules_failed_today }} failed today
                            </p>
                            <p class="mt-1 text-sm text-green-600" v-else>Running smoothly</p>
                        </div>
                        <div class="rounded-lg bg-purple-100 p-3 dark:bg-purple-900/30">
                            <Clock class="h-8 w-8 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedules Tab -->
        <div v-if="activeTab === 'schedules'" class="space-y-6">
            <!-- Configured Tasks -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Configured Scheduled Tasks</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="task in schedules?.scheduled_tasks" :key="task.command" class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ task.command }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ task.description }} • {{ task.schedule }}</p>
                        </div>
                        <button
                            @click="runCommand(task.command)"
                            class="inline-flex items-center gap-2 rounded-lg bg-purple-100 px-3 py-1.5 text-sm text-purple-700 hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-400"
                        >
                            <Play class="h-4 w-4" />
                            Run Now
                        </button>
                    </div>
                </div>
            </div>

            <!-- Schedule Logs -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Schedule Logs</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Command</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="log in schedules?.logs?.data" :key="log.id">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ log.command }}</td>
                                <td class="px-6 py-4">
                                    <span :class="['rounded-full px-2 py-1 text-xs font-medium', statusColor(log.status)]">
                                        {{ log.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ log.duration_seconds ? `${log.duration_seconds}s` : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(log.started_at) }}
                                </td>
                            </tr>
                            <tr v-if="!schedules?.logs?.data?.length">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No schedule logs yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Emails Tab -->
        <div v-if="activeTab === 'emails'" class="space-y-6">
            <!-- Email Stats -->
            <div class="grid grid-cols-4 gap-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ emails?.stats?.total || 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sent</p>
                    <p class="text-2xl font-bold text-green-600">{{ emails?.stats?.sent || 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sending</p>
                    <p class="text-2xl font-bold text-blue-600">{{ emails?.stats?.sending || 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Failed</p>
                    <p class="text-2xl font-bold text-red-600">{{ emails?.stats?.failed || 0 }}</p>
                </div>
            </div>

            <!-- Email Logs -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Email Logs</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sent At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="log in emails?.logs?.data" :key="log.id">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ log.to_email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ log.subject }}</td>
                                <td class="px-6 py-4">
                                    <span :class="['rounded-full px-2 py-1 text-xs font-medium', statusColor(log.status)]">
                                        {{ log.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(log.sent_at || log.created_at) }}
                                </td>
                            </tr>
                            <tr v-if="!emails?.logs?.data?.length">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No email logs yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Jobs Tab -->
        <div v-if="activeTab === 'jobs'" class="space-y-6">
            <!-- Pending Jobs Summary -->
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pending Jobs</p>
                    <p class="text-2xl font-bold text-blue-600">{{ jobs?.total_pending || 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Failed Jobs</p>
                    <p class="text-2xl font-bold text-red-600">{{ jobs?.total_failed || 0 }}</p>
                </div>
            </div>

            <!-- Failed Jobs -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Failed Jobs</h2>
                    <div class="flex gap-2" v-if="jobs?.failed?.length">
                        <button
                            @click="retryAllJobs"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-100 px-3 py-1.5 text-sm text-blue-700 hover:bg-blue-200"
                        >
                            <RefreshCw class="h-4 w-4" />
                            Retry All
                        </button>
                        <button
                            @click="flushJobs"
                            class="inline-flex items-center gap-2 rounded-lg bg-red-100 px-3 py-1.5 text-sm text-red-700 hover:bg-red-200"
                        >
                            <Trash2 class="h-4 w-4" />
                            Flush All
                        </button>
                    </div>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="job in jobs?.failed" :key="job.uuid" class="px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">{{ job.job_name }}</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Queue: {{ job.queue }} • Failed: {{ formatDate(job.failed_at) }}
                                </p>
                                <p class="mt-2 truncate font-mono text-xs text-red-600 dark:text-red-400">
                                    {{ job.exception }}
                                </p>
                            </div>
                            <div class="ml-4 flex gap-2">
                                <button @click="retryJob(job.uuid)" class="rounded-lg p-2 text-blue-600 hover:bg-blue-100" title="Retry">
                                    <RefreshCw class="h-4 w-4" />
                                </button>
                                <button @click="deleteJob(job.uuid)" class="rounded-lg p-2 text-red-600 hover:bg-red-100" title="Delete">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="!jobs?.failed?.length" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No failed jobs 🎉</div>
                </div>
            </div>
        </div>

        <!-- Server Tab -->
        <div v-if="activeTab === 'server'" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- System Info -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                            <Cpu class="h-5 w-5" />
                            System Information
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">PHP Version</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ server?.php_version }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Laravel Version</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ server?.laravel_version }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Environment</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ server?.environment }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Debug Mode</span>
                            <span :class="server?.debug_mode ? 'text-yellow-600' : 'text-green-600'" class="font-medium">
                                {{ server?.debug_mode ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Timezone</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ server?.timezone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Drivers -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                            <Database class="h-5 w-5" />
                            Configuration
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Cache Driver</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ server?.cache_driver }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Queue Driver</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ server?.queue_driver }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Session Driver</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ server?.session_driver }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Database</span>
                            <span :class="statusColor(server?.database?.status)" class="rounded-full px-2 py-1 text-xs font-medium">
                                {{ server?.database?.status }}
                            </span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Cache Status</span>
                            <span :class="statusColor(server?.cache_status)" class="rounded-full px-2 py-1 text-xs font-medium">
                                {{ server?.cache_status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Memory -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                            <MemoryStick class="h-5 w-5" />
                            Memory Usage
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Current</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ server?.memory?.current }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Peak</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ server?.memory?.peak }}</span>
                        </div>
                        <div class="flex justify-between px-6 py-3">
                            <span class="text-gray-500 dark:text-gray-400">Limit</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ server?.memory?.limit }}</span>
                        </div>
                    </div>
                </div>

                <!-- Disk -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800" v-if="server?.disk">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                            <HardDrive class="h-5 w-5" />
                            Disk Space
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Used</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ server?.disk?.used_percent }}%</span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    class="h-2.5 rounded-full"
                                    :class="server?.disk?.used_percent > 80 ? 'bg-red-600' : 'bg-green-600'"
                                    :style="{ width: `${server?.disk?.used_percent}%` }"
                                ></div>
                            </div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Free: {{ server?.disk?.free }}</span>
                            <span class="text-gray-500 dark:text-gray-400">Total: {{ server?.disk?.total }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
