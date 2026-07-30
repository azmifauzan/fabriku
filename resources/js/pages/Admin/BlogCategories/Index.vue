<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    categories: Array<{ id: number; name: string; slug: string; posts_count: number }>;
}>();

const { confirmDelete } = useSweetAlert();

const showCreateForm = ref(false);
const createForm = useForm({ name: '' });
const editingId = ref<number | null>(null);
const editForm = useForm({ name: '' });

function submitCreate() {
    createForm.post('/admin/blog-categories', {
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
        },
    });
}

function startEdit(category: { id: number; name: string }) {
    editingId.value = category.id;
    editForm.name = category.name;
}

function submitEdit(id: number) {
    editForm.put(`/admin/blog-categories/${id}`, {
        onSuccess: () => {
            editingId.value = null;
        },
    });
}

async function destroy(id: number, name: string) {
    const result = await confirmDelete('Hapus Kategori', `Apakah Anda yakin ingin menghapus kategori "${name}"?`);
    if (result.isConfirmed) {
        router.delete(`/admin/blog-categories/${id}`);
    }
}
</script>

<template>
    <Head title="Kategori Blog" />
    <AdminLayout>
        <div class="mx-auto max-w-3xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Kategori Blog</h1>
                <button
                    class="flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="showCreateForm = !showCreateForm"
                >
                    <Plus class="h-4 w-4" /> Tambah Kategori
                </button>
            </div>

            <form v-if="showCreateForm" class="mb-4 flex gap-2" @submit.prevent="submitCreate">
                <input
                    v-model="createForm.name"
                    type="text"
                    placeholder="Nama kategori"
                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                />
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm text-white">Simpan</button>
            </form>

            <ul class="divide-y divide-gray-200 rounded-md border border-gray-200 dark:divide-gray-700 dark:border-gray-700">
                <li v-for="category in categories" :key="category.id" class="flex items-center justify-between p-3">
                    <form v-if="editingId === category.id" class="flex flex-1 gap-2" @submit.prevent="submitEdit(category.id)">
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        />
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1 text-sm text-white">Simpan</button>
                    </form>
                    <template v-else>
                        <span class="text-gray-900 dark:text-white" @click="startEdit(category)">
                            {{ category.name }} <span class="text-sm text-gray-400">({{ category.posts_count }} post)</span>
                        </span>
                        <button class="text-red-600 hover:text-red-800" @click="destroy(category.id, category.name)">
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </template>
                </li>
            </ul>
        </div>
    </AdminLayout>
</template>
