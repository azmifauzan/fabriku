<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

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
  attributes?: MaterialAttribute[]
}

const props = defineProps<{
  material?: Material
}>()

const form = useForm({
  code: props.material?.code || '',
  name: props.material?.name || '',
  type: props.material?.type || 'kain',
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

    <!-- Main Content -->
    <div class="py-6">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <Link
            href="/materials"
            class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Daftar
          </Link>
          <h2 class="text-2xl font-bold text-gray-900">
            {{ isEditing ? 'Edit Bahan Baku' : 'Tambah Bahan Baku' }}
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            {{ isEditing ? 'Ubah informasi bahan baku' : 'Tambahkan bahan baku baru ke inventory' }}
          </p>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
          <form @submit.prevent="submit" class="space-y-6 p-6">
            <!-- Code -->
            <div>
              <label for="code" class="block text-sm font-medium text-gray-700">
                Kode Bahan <span class="text-red-500">*</span>
              </label>
              <input
                id="code"
                v-model="form.code"
                type="text"
                required
                placeholder="Contoh: KA-001"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                :class="{ 'border-red-300': form.errors.code }"
              />
              <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                {{ form.errors.code }}
              </p>
              <p class="mt-1 text-xs text-gray-500">
                Kode unik untuk identifikasi bahan baku
              </p>
            </div>

            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">
                Nama Bahan <span class="text-red-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                placeholder="Contoh: Kain Katun Rayon Premium"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                :class="{ 'border-red-300': form.errors.name }"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Type & Unit (Row) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="type" class="block text-sm font-medium text-gray-700">
                  Jenis Bahan <span class="text-red-500">*</span>
                </label>
                <select
                  id="type"
                  v-model="form.type"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                  :class="{ 'border-red-300': form.errors.type }"
                >
                  <option value="kain">Kain</option>
                  <option value="benang">Benang</option>
                  <option value="aksesoris">Aksesoris</option>
                  <option value="kemasan">Kemasan</option>
                  <option value="lainnya">Lainnya</option>
                </select>
                <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">
                  {{ form.errors.type }}
                </p>
              </div>

              <div>
                <label for="unit" class="block text-sm font-medium text-gray-700">
                  Satuan <span class="text-red-500">*</span>
                </label>
                <select
                  id="unit"
                  v-model="form.unit"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                  :class="{ 'border-red-300': form.errors.unit }"
                >
                  <option value="meter">Meter</option>
                  <option value="yard">Yard</option>
                  <option value="kg">Kilogram (kg)</option>
                  <option value="gram">Gram</option>
                  <option value="roll">Roll</option>
                  <option value="pcs">Pieces (pcs)</option>
                  <option value="lusin">Lusin</option>
                  <option value="pack">Pack</option>
                </select>
                <p v-if="form.errors.unit" class="mt-1 text-sm text-red-600">
                  {{ form.errors.unit }}
                </p>
              </div>
            </div>

            <!-- Price & Reorder Point (Row) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="standard_price" class="block text-sm font-medium text-gray-700">
                  Harga Standar (Rp)
                </label>
                <input
                  id="standard_price"
                  v-model="form.standard_price"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                  :class="{ 'border-red-300': form.errors.standard_price }"
                />
                <p v-if="form.errors.standard_price" class="mt-1 text-sm text-red-600">
                  {{ form.errors.standard_price }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                  Harga per satuan (opsional)
                </p>
              </div>

              <div>
                <label for="reorder_point" class="block text-sm font-medium text-gray-700">
                  Minimum Stok
                </label>
                <input
                  id="reorder_point"
                  v-model="form.reorder_point"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                  :class="{ 'border-red-300': form.errors.reorder_point }"
                />
                <p v-if="form.errors.reorder_point" class="mt-1 text-sm text-red-600">
                  {{ form.errors.reorder_point }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                  Peringatan stok rendah (opsional)
                </p>
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                />
                <span class="ml-2 text-sm text-gray-700">
                  Bahan baku aktif (dapat digunakan untuk produksi)
                </span>
              </label>
            </div>

            <!-- Dynamic Attributes -->
            <div class="border-t pt-4">
              <div class="flex justify-between items-center mb-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700">
                    Atribut Tambahan
                  </label>
                  <p class="text-xs text-gray-500">
                    Tambahkan informasi dinamis seperti corak, gramasi, lebar, dll
                  </p>
                </div>
                <button
                  type="button"
                  @click="addAttribute"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-md hover:bg-indigo-100"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Tambah Atribut
                </button>
              </div>

              <div v-if="form.attributes.length > 0" class="space-y-3">
                <div
                  v-for="(attr, index) in form.attributes"
                  :key="index"
                  class="flex gap-2 items-start"
                >
                  <div class="flex-1">
                    <input
                      v-model="attr.name"
                      type="text"
                      placeholder="Nama atribut (misal: Corak)"
                      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    />
                  </div>
                  <div class="flex-1">
                    <input
                      v-model="attr.value"
                      type="text"
                      placeholder="Nilai atribut (misal: Polos)"
                      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    />
                  </div>
                  <button
                    type="button"
                    @click="removeAttribute(index)"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-md"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <div v-else class="text-center py-4 text-sm text-gray-500 bg-gray-50 rounded-md">
                Belum ada atribut. Klik "Tambah Atribut" untuk menambahkan.
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
              <Link
                href="/materials"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Batal
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
    </div>
  </AppLayout>
</template>
