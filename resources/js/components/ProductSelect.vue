<script setup lang="ts">
import { ChevronDown, Search } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ProductThumbnail from '@/components/ProductThumbnail.vue';

interface ProductOption {
    id: number;
    product_name?: string | null;
    sku: string;
    current_stock: number;
    reserved_stock: number;
    image_url?: string | null;
    pattern?: { name?: string | null } | null;
}

const props = defineProps<{
    modelValue: number | string;
    items: ProductOption[];
    error?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | string): void;
}>();

const open = ref(false);
const search = ref('');
const rootRef = ref<HTMLElement | null>(null);

const selectedItem = computed(() => props.items.find((it) => it.id === props.modelValue) ?? null);

const availableStock = (it: ProductOption) => it.current_stock - it.reserved_stock;

const labelFor = (it: ProductOption) => it.product_name || it.pattern?.name || it.sku;

const filteredItems = computed(() => {
    if (!search.value) return props.items;
    const q = search.value.toLowerCase();
    return props.items.filter((it) => labelFor(it).toLowerCase().includes(q) || it.sku.toLowerCase().includes(q));
});

const select = (it: ProductOption) => {
    emit('update:modelValue', it.id);
    open.value = false;
    search.value = '';
};

const handleClickOutside = (event: MouseEvent) => {
    if (!rootRef.value?.contains(event.target as Node)) {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="rootRef" class="relative">
        <button
            type="button"
            @click="open = !open"
            class="flex w-full items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-left text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            :class="{ 'border-red-500': error }"
        >
            <ProductThumbnail :image-url="selectedItem?.image_url" class="h-8 w-8 shrink-0" />
            <span class="min-w-0 flex-1 truncate" :class="{ 'text-gray-400 dark:text-gray-500': !selectedItem }">
                {{ selectedItem ? `${labelFor(selectedItem)} - ${selectedItem.sku} (${availableStock(selectedItem)} available)` : 'Pilih Produk' }}
            </span>
            <ChevronDown :size="16" class="shrink-0 text-gray-400" />
        </button>

        <div
            v-if="open"
            class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
        >
            <div class="relative border-b border-gray-100 p-2 dark:border-gray-600">
                <Search :size="14" class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-400" />
                <input
                    v-model="search"
                    type="text"
                    autofocus
                    placeholder="Cari produk..."
                    class="w-full rounded-md border border-gray-200 py-1.5 pr-2 pl-7 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                />
            </div>
            <ul class="max-h-64 overflow-y-auto py-1">
                <li
                    v-for="it in filteredItems"
                    :key="it.id"
                    @click="select(it)"
                    class="flex cursor-pointer items-center gap-2 px-3 py-2 hover:bg-indigo-50 dark:hover:bg-gray-600"
                >
                    <ProductThumbnail :image-url="it.image_url" class="h-9 w-9 shrink-0" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ labelFor(it) }}</p>
                        <p class="truncate text-xs text-gray-400">{{ it.sku }} · {{ availableStock(it) }} tersedia</p>
                    </div>
                </li>
                <li v-if="filteredItems.length === 0" class="px-3 py-4 text-center text-sm text-gray-400">Produk tidak ditemukan</li>
            </ul>
        </div>
    </div>
</template>
