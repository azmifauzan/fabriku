<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import FormSection from '@/components/FormSection.vue'
import FormField from '@/components/FormField.vue'

const props = defineProps<{
  order?: any
  patterns: any[]
  materials: any[]
  staff: any[]
}>()

// Format date properly for date input field (YYYY-MM-DD)
const formatDateForInput = (date: string | null | undefined): string => {
  if (!date) {
    return new Date().toISOString().split('T')[0]
  }

  // If date is already in YYYY-MM-DD format, return as is
  if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
    return date
  }

  // Otherwise parse and format
  return new Date(date).toISOString().split('T')[0]
}

const form = useForm({
  pattern_id: props.order?.pattern_id || null,
  order_date: formatDateForInput(props.order?.order_date),
  prepared_by: props.order?.prepared_by || null,
  output_quantity: props.order?.output_quantity || 0,
  output_unit: props.order?.output_unit || 'pieces',
  materials_used: props.order?.materials_used || [],
  notes: props.order?.notes || '',
  status: props.order?.status || 'completed',
})

const addMaterial = () => {
  form.materials_used.push({
    material_id: null,
    material_name: '',
    quantity: 0,
    unit: ''
  })
}

const removeMaterial = (index: number) => {
  form.materials_used.splice(index, 1)
}

const onMaterialSelect = (index: number) => {
  const selected = props.materials.find(m => m.id === form.materials_used[index].material_id)
  if (selected) {
    form.materials_used[index].material_name = selected.name
    form.materials_used[index].unit = selected.unit
  }
}

const submit = () => {
  if (props.order) {
    form.put(`/preparation-orders/${props.order.id}`, {
      preserveScroll: true,
    })
  } else {
    form.post('/preparation-orders', {
      preserveScroll: true,
    })
  }
}

const isEditing = !!props.order?.id
</script>

<template>
  <AppLayout>
    <Head :title="isEditing ? 'Edit Preparation Order' : 'Tambah Preparation Order'" />

    <div class="py-6 px-6">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
              {{ isEditing ? 'Edit Preparation Order' : 'Tambah Preparation Order Baru' }}
            </h1>
            <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">
              {{ isEditing ? 'Perbarui informasi preparation order' : 'Tambahkan preparation order baru ke sistem' }}
            </p>
          </div>
          <Link
            href="/preparation-orders"
            class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Basic Information -->
          <FormSection title="Informasi Dasar" description="Data identifikasi preparation order">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <FormField
                  v-model="form.pattern_id"
                  label="Pattern (Optional)"
                  type="select"
                  :error="form.errors.pattern_id"
                  hint="Pilih pattern sebagai acuan, atau kosongkan untuk custom prep"
                  :options="[
                    { value: null, label: 'Tanpa Pattern' },
                    ...patterns.map(p => ({ value: p.id, label: `${p.name} (${p.category})` }))
                  ]"
                />
              </div>

              <FormField
                v-model="form.order_date"
                label="Tanggal Order"
                type="date"
                :required="true"
                :error="form.errors.order_date"
              />

              <FormField
                v-model="form.prepared_by"
                label="Penanggung Jawab"
                type="select"
                :error="form.errors.prepared_by"
                hint="Staff yang melakukan persiapan"
                :options="[
                  { value: null, label: 'Pilih Staff' },
                  ...staff.map(s => ({ 
                    value: s.id, 
                    label: s.position ? `${s.name} (${s.position})` : s.name 
                  }))
                ]"
              />
            </div>
          </FormSection>

          <!-- Materials Used -->
          <FormSection title="Material yang Digunakan" description="Daftar material dan jumlah yang digunakan dalam prep">
            <div class="space-y-4">
              <div class="flex justify-end">
                <button
                  type="button"
                  @click="addMaterial"
                  class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-all"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Tambah Material
                </button>
              </div>

              <div v-if="form.materials_used.length > 0" class="space-y-3">
                <div
                  v-for="(material, idx) in form.materials_used"
                  :key="idx"
                  class="flex gap-3 items-start p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                >
                  <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Material</label>
                    <select
                      v-model="material.material_id"
                      @change="onMaterialSelect(Number(idx))"
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                      required
                    >
                      <option :value="null">Pilih Material</option>
                      <option v-for="mat in materials" :key="mat.id" :value="mat.id">
                        {{ mat.name }} (Stock: {{ mat.current_stock }} {{ mat.unit }})
                      </option>
                    </select>
                  </div>
                  <div class="w-32">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah</label>
                    <input
                      v-model.number="material.quantity"
                      type="number"
                      step="0.01"
                      placeholder="0"
                      required
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                    />
                  </div>
                  <div class="w-24">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Satuan</label>
                    <input
                      v-model="material.unit"
                      type="text"
                      placeholder="Unit"
                      required
                      readonly
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 dark:text-white rounded-lg text-sm"
                    />
                  </div>
                  <div class="pt-7">
                    <button
                      type="button"
                      @click="removeMaterial(Number(idx))"
                      class="p-2.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                      title="Hapus material"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-8 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                Belum ada material. Klik "Tambah Material" untuk menambahkan.
              </div>

              <div v-if="form.errors.materials_used" class="text-sm text-red-600 dark:text-red-400 font-medium">
                {{ form.errors.materials_used }}
              </div>
            </div>
          </FormSection>

          <!-- Output Information -->
          <FormSection title="Output Hasil Preparation" description="Informasi hasil output dari proses preparation">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model.number="form.output_quantity"
                label="Jumlah Output"
                type="number"
                step="0.01"
                placeholder="0"
                :required="true"
                :error="form.errors.output_quantity"
                hint="Jumlah hasil output dari preparation"
              />

              <FormField
                v-model="form.output_unit"
                label="Satuan Output"
                type="text"
                placeholder="pieces, kg, batch, dll"
                :required="true"
                :error="form.errors.output_unit"
                hint="Satuan hasil output (pieces, kg, batch, dll)"
              />
            </div>
          </FormSection>

          <!-- Status and Notes -->
          <FormSection title="Status dan Catatan" description="Status order dan catatan tambahan">
            <div class="space-y-6">
              <FormField
                v-model="form.status"
                label="Status"
                type="select"
                :required="true"
                :error="form.errors.status"
                hint="Draft/In Progress: stok belum dipotong. Completed: stok otomatis dipotong!"
                :options="[
                  { value: 'draft', label: 'Draft' },
                  { value: 'in_progress', label: 'In Progress' },
                  { value: 'completed', label: 'Completed' },
                  { value: 'cancelled', label: 'Cancelled' }
                ]"
              />

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Catatan
                </label>
                <textarea
                  v-model="form.notes"
                  rows="4"
                  placeholder="Catatan tambahan..."
                  class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                ></textarea>
                <p v-if="form.errors.notes" class="mt-1.5 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.notes }}
                </p>
              </div>
            </div>
          </FormSection>

          <!-- Submit Button -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4">
            <Link
              href="/preparation-orders"
              class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-semibold rounded-lg shadow-sm transition-all"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg
                v-if="form.processing"
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Update' : 'Simpan') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
