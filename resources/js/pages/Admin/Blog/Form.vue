<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps<{
    post?: {
        id: number;
        title: string;
        excerpt: string | null;
        content: string;
        status: string;
        blog_category_id: number | null;
        meta_title: string | null;
        meta_description: string | null;
        featured_image_url: string | null;
        tags: string;
    };
    categories: Array<{ id: number; name: string }>;
}>();

const isEdit = !!props.post;

const form = useForm({
    title: props.post?.title ?? '',
    excerpt: props.post?.excerpt ?? '',
    content: props.post?.content ?? '',
    status: props.post?.status ?? 'draft',
    blog_category_id: props.post?.blog_category_id ?? null,
    tags: props.post?.tags ?? '',
    meta_title: props.post?.meta_title ?? '',
    meta_description: props.post?.meta_description ?? '',
    featured_image: null as File | null,
});

const previewHtml = ref('');
const showPreview = ref(false);

async function loadPreview() {
    const response = await axios.post('/admin/blog-preview', { content: form.content });
    previewHtml.value = response.data.html;
    showPreview.value = true;
}

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    form.featured_image = target.files?.[0] ?? null;
}

function submit() {
    const url = isEdit ? `/admin/blog/${props.post!.id}` : '/admin/blog';
    form.post(url, {
        forceFormData: true,
        onSuccess: () => form.reset('featured_image'),
    });
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Post' : 'Tulis Post'" />
    <AdminLayout>
        <form class="mx-auto max-w-3xl space-y-4 p-6" @submit.prevent="submit">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ isEdit ? 'Edit Post' : 'Tulis Post' }}</h1>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul</label>
                <input v-model="form.title" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ringkasan (Excerpt)</label>
                <textarea v-model="form.excerpt" rows="2" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konten (Markdown)</label>
                    <button type="button" class="text-sm text-indigo-600" @click="loadPreview">Lihat Preview</button>
                </div>
                <textarea v-model="form.content" rows="12" class="mt-1 w-full rounded-md border-gray-300 font-mono dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                <div v-if="showPreview" class="prose mt-2 max-w-none rounded-md border border-gray-200 p-4 dark:border-gray-700 dark:prose-invert" v-html="previewHtml" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gambar Utama</label>
                <input type="file" accept="image/*" class="mt-1" @change="handleFileChange" />
                <img v-if="post?.featured_image_url" :src="post.featured_image_url" class="mt-2 h-32 w-32 rounded-md object-cover" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                    <select v-model="form.blog_category_id" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option :value="null">- Tanpa kategori -</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select v-model="form.status" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="draft">Draft</option>
                        <option value="published">Terbit</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag (pisahkan dengan koma)</label>
                <input v-model="form.tags" type="text" placeholder="retail, tips, stok" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Title (SEO)</label>
                <input v-model="form.meta_title" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Description (SEO)</label>
                <textarea v-model="form.meta_description" rows="2" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                Simpan
            </button>
        </form>
    </AdminLayout>
</template>
