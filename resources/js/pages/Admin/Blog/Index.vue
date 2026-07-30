<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';
import { Head, Link, router } from '@inertiajs/vue3';
import { FolderCog, Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineProps<{
    posts: {
        data: Array<{ id: number; title: string; status: string; published_at: string | null; category: { name: string } | null }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

const { confirmDelete } = useSweetAlert();

async function destroy(id: number, title: string) {
    const result = await confirmDelete('Hapus Post', `Apakah Anda yakin ingin menghapus post "${title}"?`);
    if (result.isConfirmed) {
        router.delete(`/admin/blog/${id}`);
    }
}
</script>

<template>
    <Head title="Blog" />
    <AdminLayout>
        <div class="mx-auto max-w-5xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Blog</h1>
                <div class="flex gap-2">
                    <Link
                        href="/admin/blog-categories"
                        class="flex items-center gap-1 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <FolderCog class="h-4 w-4" /> Kelola Kategori
                    </Link>
                    <Link
                        href="/admin/blog/create"
                        class="flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        <Plus class="h-4 w-4" /> Tulis Post
                    </Link>
                </div>
            </div>

            <table class="w-full divide-y divide-gray-200 rounded-md border border-gray-200 text-left text-sm dark:divide-gray-700 dark:border-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2">Judul</th>
                        <th class="px-4 py-2">Kategori</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Tanggal Publish</th>
                        <th class="px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="post in posts.data" :key="post.id">
                        <td class="px-4 py-2 text-gray-900 dark:text-white">{{ post.title }}</td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ post.category?.name ?? '-' }}</td>
                        <td class="px-4 py-2">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs"
                                :class="post.status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                            >
                                {{ post.status === 'published' ? 'Terbit' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                            {{ post.published_at ? new Date(post.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex gap-2">
                                <Link :href="`/admin/blog/${post.id}/edit`" class="text-indigo-600 hover:text-indigo-800">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button class="text-red-600 hover:text-red-800" @click="destroy(post.id, post.title)">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
