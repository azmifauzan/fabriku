<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Site = { id: number; slug: string; status: string; custom_domain: string | null; tenant: { name: string } };
type Report = {
    id: number;
    category: string;
    details: string;
    contact_email: string | null;
    status: string;
    created_at: string;
    business_site: { slug: string } | null;
};
defineProps<{ sites: { data: Site[]; links: Array<{ url: string | null; label: string; active: boolean }> }; reports: Report[] }>();
const page = usePage();
const success = computed(() => (page.props.flash as { success?: string } | undefined)?.success);
function changeSite(site: Site) {
    const action = site.status === 'suspended' ? 'restore' : 'suspend';
    if (action === 'suspend' && !window.confirm(`Nonaktifkan ${site.slug}.fabriku.biz.id?`)) return;
    router.post(`/admin/websites/${site.id}/status`, { action }, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout>
        <Head title="Website Usaha · Admin" />
        <main class="mx-auto max-w-6xl space-y-8 p-5 sm:p-8">
            <div>
                <h1 class="text-3xl font-semibold text-slate-950 dark:text-white">Website Usaha</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Tinjau situs dan laporan pengunjung sebelum mengambil tindakan.</p>
            </div>
            <p v-if="success" role="status" class="rounded-lg bg-emerald-50 p-3 text-emerald-900">{{ success }}</p>
            <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="mb-4 text-xl font-semibold dark:text-white">Situs</h2>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px] text-left text-sm">
                        <thead class="border-b text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="p-3">Usaha</th>
                                <th class="p-3">Alamat</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="site in sites.data" :key="site.id" class="border-b border-slate-100 dark:border-slate-700">
                                <td class="p-3 font-medium dark:text-white">{{ site.tenant?.name }}</td>
                                <td class="p-3 dark:text-white">{{ site.custom_domain || `${site.slug}.fabriku.biz.id` }}</td>
                                <td class="p-3 dark:text-white">{{ site.status }}</td>
                                <td class="p-3">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-slate-400 px-3 py-1.5 font-medium text-slate-900 dark:text-white"
                                        @click="changeSite(site)"
                                    >
                                        {{ site.status === 'suspended' ? 'Pulihkan ke jeda' : 'Nonaktifkan' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!sites.data.length">
                                <td colspan="4" class="p-5 text-slate-600">Belum ada website.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <nav aria-label="Halaman situs" class="mt-4 flex flex-wrap gap-2">
                    <template v-for="link in sites.links" :key="link.label"
                        ><Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded border border-slate-300 px-2 py-1 text-sm dark:text-white"
                            :aria-current="link.active ? 'page' : undefined"
                            >{{ link.label.replace('&laquo;', '‹').replace('&raquo;', '›') }}</Link
                        ></template
                    >
                </nav>
            </section>
            <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="mb-4 text-xl font-semibold dark:text-white">Laporan pengunjung</h2>
                <p v-if="!reports.length" class="text-sm text-slate-600">Belum ada laporan.</p>
                <ul class="divide-y divide-slate-200 dark:divide-slate-700">
                    <li v-for="report in reports" :key="report.id" class="grid gap-2 py-4 text-sm">
                        <div class="flex flex-wrap justify-between gap-2">
                            <strong class="dark:text-white">{{ report.business_site?.slug }} · {{ report.category }}</strong
                            ><span class="text-slate-600 dark:text-slate-300">{{ report.status }}</span>
                        </div>
                        <p class="whitespace-pre-wrap text-slate-700 dark:text-slate-200">{{ report.details }}</p>
                        <p v-if="report.contact_email" class="text-slate-600 dark:text-slate-300">Kontak: {{ report.contact_email }}</p>
                        <div v-if="report.status !== 'resolved'" class="flex gap-2">
                            <button
                                v-if="report.status === 'new'"
                                type="button"
                                class="rounded-lg border border-slate-400 px-3 py-1.5 dark:text-white"
                                @click="router.post(`/admin/website-reports/${report.id}/status`, { status: 'reviewed' }, { preserveScroll: true })"
                            >
                                Sedang ditinjau</button
                            ><button
                                type="button"
                                class="rounded-lg border border-slate-400 px-3 py-1.5 dark:text-white"
                                @click="router.post(`/admin/website-reports/${report.id}/status`, { status: 'resolved' }, { preserveScroll: true })"
                            >
                                Selesai
                            </button>
                        </div>
                    </li>
                </ul>
            </section>
        </main>
    </AdminLayout>
</template>
