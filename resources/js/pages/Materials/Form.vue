<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import FormField from '@/components/FormField.vue'
import FormSection from '@/components/FormSection.vue'

interface MaterialAttribute {
  id?: number
  attribute_name: string
  attribute_value: string
}

interface Material {
  id?: number
  code: string
  name: string
  type: string
  unit: string
  standard_price: string
  reorder_point: string
  is_active: boolean
  current_stock?: string
  attributes?: MaterialAttribute[]
}

const props = defineProps<{
  material?: Material
}>()

const form = useForm({
  code: props.material?.code || '',
  name: props.material?.name || '',
  type: props.material?.type || 'kain',
  quantity: props.material?.current_stock || '0',
  unit: props.material?.unit || 'meter',
  standard_price: props.material?.standard_price || '0',
  reorder_point: props.material?.reorder_point || '0',
  is_active: props.material?.is_active ?? true,
  attributes: props.material?.attributes?.map(attr => ({
    name: attr.attribute_name,
    value: attr.attribute_value
  })) || [],
})

const addAttribute = () => {
  form.attributes.push({ name: '', value: '' })
}

const removeAttribute = (index: number) => {
  form.attributes.splice(index, 1)
}

const submit = () => {
  if (props.material?.id) {
    form.put(`/materials/${props.material.id}`, {
      preserveScroll: true,
    })
  } else {
    form.post('/materials', {
      preserveScroll: true,
    })
  }
}

const isEditing = !!props.material?.id
</script>

<template>
  <AppLayout>
    <Head :title="isEditing ? 'Edit Bahan Baku' : 'Tambah Bahan Baku'" />

    <div class="py-6 px-6">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
              {{ isEditing ? 'Edit Bahan Baku' : 'Tambah Bahan Baku Baru' }}
            </h1>
            <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">
              {{ isEditing ? 'Perbarui informasi bahan baku' : 'Tambahkan bahan baku baru ke inventory' }}
            </p>
          </div>
          <Link
            href="/materials"
            class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Basic Information -->
          <FormSection title="Informasi Dasar" description="Data identifikasi bahan baku">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.code"
                label="Kode Bahan"
                type="text"
                placeholder="Contoh: KA-001"
                :required="true"
                :error="form.errors.code"
                hint="Kode unik untuk identifikasi bahan baku"
              />

              <FormField
                v-model="form.name"
                label="Nama Bahan"
                type="text"
                placeholder="Contoh: Kain Katun Rayon Premium"
                :required="true"
                :error="form.errors.name"
              />

              <FormField
                v-model="form.type"
                label="Jenis Bahan"
                type="select"
                :required="true"
                :error="form.errors.type"
                :options="[
                  { value: 'kain', label: 'Kain' },
                  { value: 'benang', label: 'Benang' },
                  { value: 'aksesoris', label: 'Aksesoris' },
                  { value: 'kemasan', label: 'Kemasan' },
                  { value: 'lainnya', label: 'Lainnya' }
                ]"
              />

              <FormField
                v-model="form.unit"
                label="Satuan"
                type="select"
                :required="true"
                :error="form.errors.unit"
                :options="[
                  { value: 'meter', label: 'Meter' },
                  { value: 'yard', label: 'Yard' },
                  { value: 'kg', label: 'Kilogram (kg)' },
                  { value: 'gram', label: 'Gram' },
                  { value: 'roll', label: 'Roll' },
                  { value: 'pcs', label: 'Pieces (pcs)' },
                  { value: 'lusin', label: 'Lusin' },
                  { value: 'pack', label: 'Pack' }
                ]"
              />
            </div>
          </FormSection>

          <!-- Stock and Pricing -->
          <FormSection title="Stok dan Harga" description="Informasi stok dan pricing">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.quantity"
                label="Jumlah Stok Awal"
                type="number"
                placeholder="0"
                :error="form.errors.quantity"
                hint="Stok awal bahan baku (opsional)"
              />

              <FormField
                v-model="form.standard_price"
                label="Harga Standar (Rp)"
                type="number"
                placeholder="0"
                :error="form.errors.standard_price"
                hint="Harga per satuan (opsional)"
              />

              <FormField
                v-model="form.reorder_point"
                label="Minimum Stok"
                type="number"
                placeholder="0"
                :error="form.errors.reorder_point"
                hint="Peringatan stok rendah (opsional)"
              />

              <div class="flex items-center pt-8">
                <FormField
                  v-model="form.is_active"
                  label="Bahan baku aktif (dapat digunakan untuk produksi)"
                  type="checkbox"
                />
              </div>
            </div>
          </FormSection>

          <!-- Dynamic Attributes -->
          <FormSection title="Atribut Tambahan" description="Informasi dinamis seperti corak, gramasi, lebar, dll">
            <div class="space-y-4">
              <div class="flex justify-end">
                <button
                  type="button"
                  @click="addAttribute"
                  class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-all"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Tambah Atribut
                </button>
              </div>

              <div v-if="form.attributes.length > 0" class="space-y-3">
                <div
                  v-for="(attr, index) in form.attributes"
                  :key="index"
                  class="flex gap-3 items-start p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                >
                  <div class="flex-1">
                    <input
                      v-model="attr.name"
                      type="text"
                      placeholder="Nama atribut (misal: Corak)"
                      class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                    />
                  </div>
                  <div class="flex-1">
                    <input
                      v-model="attr.value"
                      type="text"
                      placeholder="Nilai atribut (misal: Polos)"
                      class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                    />
                  </div>
                  <button
                    type="button"
                    @click="removeAttribute(index)"
                    class="p-2.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                    title="Hapus atribut"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <div v-else class="text-center py-8 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                Belum ada atribut. Klik "Tambah Atribut" untuk menambahkan.
              </div>
            </div>
          </FormSection>

          <!-- Submit Button -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4">
            <Link
              href="/materials"
              class="order-2 sm:order-1 px-6 py-2.5 text-sm font-semibold text-center text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="order-1 sm:order-2 inline-flex justify-center items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm hover:shadow-md"
            >
              <svg
                v-if="form.processing"
                class="animate-spin h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Bahan Baku') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
