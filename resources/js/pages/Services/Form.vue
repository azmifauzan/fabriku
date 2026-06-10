<script setup lang="ts">
import FormField from '@/components/FormField.vue';
import FormSection from '@/components/FormSection.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';

interface ConsumableRow {
    inventory_item_id: number | null;
    quantity: number;
}

interface ServiceConsumable {
    inventory_item_id: number;
    quantity: number;
}

interface Service {
    id?: number;
    code: string;
    name: string;
    category: string | null;
    description: string | null;
    price: number;
    duration_minutes: number | null;
    is_active: boolean;
    consumables?: ServiceConsumable[];
}

interface InventoryItemOption {
    id: number;
    sku: string;
    product_name: string;
}

const props = defineProps<{
    service?: Service;
    inventoryItems?: InventoryItemOption[];
}>();

const form = useForm({
    code: props.service?.code || '',
    name: props.service?.name || '',
    category: props.service?.category || '',
    description: props.service?.description || '',
    price: props.service?.price || 0,
    duration_minutes: props.service?.duration_minutes || null,
    is_active: props.service?.is_active ?? true,
    consumables: (props.service?.consumables || []).map((c) => ({
        inventory_item_id: c.inventory_item_id,
        quantity: c.quantity,
    })) as ConsumableRow[],
});

const addConsumable = () => {
    form.consumables.push({ inventory_item_id: null, quantity: 1 });
};

const removeConsumable = (index: number) => {
    form.consumables.splice(index, 1);
};

const submit = () => {
    if (props.service?.id) {
        form.put(`/services/${props.service.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/services', {
            preserveScroll: true,
        });
    }
};

const isEditing = !!props.service?.id;
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <FormSection title="Informasi Dasar" description="Data detail layanan">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <FormField
                    v-model="form.code"
                    label="Kode Layanan"
                    type="text"
                    placeholder="Contoh: SRV-001"
                    :required="true"
                    :error="form.errors.code"
                    hint="Kode unik untuk layanan"
                />

                <FormField
                    v-model="form.name"
                    label="Nama Layanan"
                    type="text"
                    placeholder="Contoh: Jasa Sablon"
                    :required="true"
                    :error="form.errors.name"
                />

                <FormField
                    v-model="form.category"
                    label="Kategori"
                    type="text"
                    placeholder="Contoh: Sablon, Jahit, Packing"
                    :error="form.errors.category"
                />

                <FormField
                    v-model="form.price"
                    label="Harga Dasar"
                    type="number"
                    placeholder="0"
                    :required="true"
                    :error="form.errors.price"
                    hint="Harga standar untuk layanan ini"
                />

                <FormField
                    v-model="form.duration_minutes"
                    label="Estimasi Durasi (Menit)"
                    type="number"
                    placeholder="60"
                    :error="form.errors.duration_minutes"
                    hint="Estimasi waktu yang dibutuhkan"
                />

                <div class="md:col-span-2">
                    <FormField
                        v-model="form.description"
                        label="Deskripsi"
                        type="textarea"
                        placeholder="Penjelasan layanan secara detail..."
                        :error="form.errors.description"
                        :rows="3"
                    />
                </div>

                <FormField v-model="form.is_active" label="Aktif (Dapat digunakan)" type="checkbox" />
            </div>
        </FormSection>

        <FormSection
            v-if="inventoryItems && inventoryItems.length > 0"
            title="Bahan Pendukung (Opsional)"
            description="Produk/sparepart yang otomatis berkurang dari stok saat layanan ini terjual"
        >
            <div class="space-y-3">
                <div v-for="(row, index) in form.consumables" :key="index" class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Produk/Sparepart</label>
                        <select
                            v-model="row.inventory_item_id"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option :value="null" disabled>Pilih produk...</option>
                            <option v-for="item in inventoryItems" :key="item.id" :value="item.id">{{ item.product_name }} ({{ item.sku }})</option>
                        </select>
                    </div>
                    <div class="w-28">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Qty</label>
                        <input
                            v-model.number="row.quantity"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>
                    <button
                        type="button"
                        @click="removeConsumable(index)"
                        class="mb-0.5 rounded-lg border border-gray-300 p-2.5 text-red-500 hover:bg-red-50 dark:border-gray-600 dark:hover:bg-gray-700"
                    >
                        <Trash2 :size="16" />
                    </button>
                </div>

                <button
                    type="button"
                    @click="addConsumable"
                    class="flex items-center gap-2 rounded-lg border border-dashed border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    <Plus :size="16" />
                    Tambah Bahan Pendukung
                </button>
            </div>
        </FormSection>

        <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
            <Link
                href="/services"
                class="rounded-lg border border-gray-300 px-6 py-2.5 font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Batal
            </Link>
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-indigo-600 px-6 py-2.5 font-medium text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600"
            >
                {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update' : 'Simpan' }}
            </button>
        </div>
    </form>
</template>
