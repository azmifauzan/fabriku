<script setup lang="ts">
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { AlertTriangle, Edit, Eye, Plus, Search, Trash2, Users } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Role {
    id: number;
    name: string;
    slug: string;
}

interface StaffItem {
    id: number;
    code: string;
    name: string;
    position: string | null;
    phone: string | null;
    email: string | null;
    is_active: boolean;
    role: Role | null;
    user: { id: number; email: string } | null;
    created_at: string;
}

interface PaginatedData {
    data: StaffItem[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    staff: PaginatedData;
    filters: {
        search?: string;
        is_active?: string;
    };
    maxStaff: number;
    currentStaffCount: number;
}>();

const { confirm } = useSweetAlert();

const search = ref(props.filters.search || '');
const isActive = ref(props.filters.is_active ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/staff', { search: value, is_active: isActive.value }, { preserveState: true, replace: true });
    }, 300);
});

watch(isActive, (value) => {
    router.get('/staff', { search: search.value, is_active: value }, { preserveState: true, replace: true });
});

const deleteStaff = async (staff: StaffItem) => {
    const result = await confirm(
        'Hapus Staff',
        `Apakah Anda yakin ingin menghapus ${staff.name}? Akun login staff juga akan dinonaktifkan.`,
        'Ya, Hapus',
        'warning',
        '#dc2626',
    );

    if (result.isConfirmed) {
        router.delete(`/staff/${staff.id}`);
    }
};

const isAtLimit = computed(() => props.currentStaffCount >= props.maxStaff);

const quotaPercentage = computed(() => {
    if (props.maxStaff === 0) return 0;
    return Math.min((props.currentStaffCount / props.maxStaff) * 100, 100);
});

const page = usePage();
const isAdmin = computed(() => (page.props.auth as any)?.user?.role === 'admin');
</script>

<template>
    <AppLayout>
        <Head title="Daftar Staff" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <!-- Header -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">Daftar Staff</h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">Kelola data staff / karyawan</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Staff Quota Badge -->
                        <div
                            class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm"
                            :class="
                                isAtLimit
                                    ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400'
                                    : 'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300'
                            "
                        >
                            <Users :size="16" />
                            <span class="font-medium">{{ currentStaffCount }}/{{ maxStaff }}</span>
                        </div>

                        <Link
                            v-if="isAdmin"
                            href="/staff/create"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                            :class="{ 'pointer-events-none opacity-50': isAtLimit }"
                        >
                            <Plus :size="16" />
                            Tambah Staff
                        </Link>
                    </div>
                </div>

                <!-- Staff quota progress bar -->
                <div v-if="isAdmin" class="mb-6 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Kuota Staff</span>
                        <span class="font-medium" :class="isAtLimit ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100'">
                            {{ currentStaffCount }} dari {{ maxStaff }}
                        </span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div
                            class="h-full rounded-full transition-all duration-500"
                            :class="isAtLimit ? 'bg-red-500' : quotaPercentage > 80 ? 'bg-amber-500' : 'bg-indigo-500'"
                            :style="{ width: `${quotaPercentage}%` }"
                        />
                    </div>
                    <p v-if="isAtLimit" class="mt-2 flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400">
                        <AlertTriangle :size="14" />
                        Batas maksimal tercapai. Hubungi admin platform untuk menambah kuota.
                    </p>
                </div>

                <!-- Filters -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <Search :size="18" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari staff berdasarkan nama, kode, atau role..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-4 pl-10 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-800"
                        />
                    </div>
                    <select
                        v-model="isActive"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-800"
                    >
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Kode
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Telepon
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="item in staff.data" :key="item.id" class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ item.code }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ item.name }}</div>
                                        <div v-if="item.user" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Memiliki akun login</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="item.role"
                                            class="inline-flex rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300"
                                        >
                                            {{ item.role.name }}
                                        </span>
                                        <span v-else class="text-sm text-gray-400 dark:text-gray-500">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">
                                        {{ item.email || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">
                                        {{ item.phone || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                            :class="
                                                item.is_active
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
                                            "
                                        >
                                            {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="`/staff/${item.id}`"
                                                class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                                title="Lihat Detail"
                                            >
                                                <Eye :size="16" />
                                            </Link>
                                            <Link
                                                v-if="isAdmin"
                                                :href="`/staff/${item.id}/edit`"
                                                class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-blue-600 dark:hover:bg-gray-700 dark:hover:text-blue-400"
                                                title="Edit"
                                            >
                                                <Edit :size="16" />
                                            </Link>
                                            <button
                                                v-if="isAdmin"
                                                @click="deleteStaff(item)"
                                                class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-700 dark:hover:text-red-400"
                                                title="Hapus"
                                            >
                                                <Trash2 :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="staff.data.length === 0" class="p-12 text-center">
                        <Users :size="48" class="mx-auto mb-4 text-gray-300 dark:text-gray-600" />
                        <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">Belum ada staff</h3>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Mulai tambahkan staff untuk mengelola karyawan Anda</p>
                        <Link
                            v-if="isAdmin && !isAtLimit"
                            href="/staff/create"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                        >
                            <Plus :size="16" />
                            Tambah Staff Pertama
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="staff.last_page > 1" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Menampilkan {{ (staff.current_page - 1) * staff.per_page + 1 }} -
                                {{ Math.min(staff.current_page * staff.per_page, staff.total) }} dari {{ staff.total }} staff
                            </p>
                            <nav class="flex gap-1">
                                <Link
                                    v-for="link in staff.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'rounded-md px-3 py-1 text-sm',
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : link.url
                                              ? 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                                              : 'cursor-not-allowed text-gray-400 dark:text-gray-600',
                                    ]"
                                >
                                    <span v-html="link.label" />
                                </Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
