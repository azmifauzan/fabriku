<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { useBusinessContext } from '@/composables/useBusinessContext'

interface Pattern {
  id: number
  code: string
  name: string
}

interface CuttingOrder {
  id: number
  order_number: string
  pattern: Pattern
}

interface CuttingResult {
  id: number
  cutting_order_id: number
  actual_quantity: number
  defect_quantity: number
  cutting_order: CuttingOrder
}

interface Contractor {
  id: number
  name: string
  type: string
  specialty: string
  rate_per_piece: number
  rate_per_hour: number
}

interface ProductionOrder {
  id?: number
  order_number: string
  cutting_result_id: number
  contractor_id: number | null
  type: string
  quantity_requested: number
  requested_date: string
  promised_date: string | null
  status: string
  notes: string
  labor_cost: number | null
  priority: string
}

const props = defineProps<{
  cuttingResults: CuttingResult[]
  contractors: Contractor[]
  productionOrder?: ProductionOrder
}>()

const form = useForm({
  cutting_result_id: props.productionOrder?.cutting_result_id || 0,
  type: props.productionOrder?.type || 'internal',
  contractor_id: props.productionOrder?.contractor_id || null,
  quantity_requested: props.productionOrder?.quantity_requested || 0,
  requested_date: props.productionOrder?.requested_date || new Date().toISOString().split('T')[0],
  promised_date: props.productionOrder?.promised_date || '',
  labor_cost: props.productionOrder?.labor_cost ?? null,
  priority: props.productionOrder?.priority || 'normal',
  notes: props.productionOrder?.notes || '',
})

const selectedCuttingResult = computed(() => {
  return props.cuttingResults.find(cr => cr.id === form.cutting_result_id)
})

const selectedContractor = computed(() => {
  return props.contractors.find(c => c.id === form.contractor_id)
})

const availableQuantity = computed(() => {
  if (!selectedCuttingResult.value) return 0
  return selectedCuttingResult.value.actual_quantity - selectedCuttingResult.value.defect_quantity
})

const estimatedCost = computed(() => {
  if (!selectedContractor.value || form.type !== 'external') return 0
  return selectedContractor.value.rate_per_piece * form.quantity_requested
})

const { term, termLower } = useBusinessContext()

const productionOrderLabel = computed(() => term('production_order', 'Production Order'))
const productionOrderLabelLower = computed(() => termLower('production_order', 'production order'))
const preparationLabel = computed(() => term('preparation', 'Persiapan'))
const contractorLabel = computed(() => term('contractor', 'Kontraktor'))
const patternLabel = computed(() => term('pattern', 'Pattern'))

const submit = () => {
  if (props.productionOrder?.id) {
    form.put(`/production-orders/${props.productionOrder.id}`, {
      preserveScroll: true,
    })
  } else {
    form.post('/production-orders', {
      preserveScroll: true,
    })
  }
}

const isEditing = !!props.productionOrder?.id
</script>

<template>
  <AppLayout>
    <Head :title="isEditing ? `Edit ${productionOrderLabel}` : `Buat ${productionOrderLabel}`" />

    <!-- Page Content -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="mb-6 flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ isEditing ? `Edit ${productionOrderLabel}` : `Buat ${productionOrderLabel} Baru` }}
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                  {{ isEditing ? `Perbarui ${productionOrderLabelLower}` : `Buat order produksi dari hasil ${preparationLabel.toLowerCase()}` }}
                </p>
              </div>
              <Link
                href="/production-orders"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300"
              >
                ← Kembali
              </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
              <!-- Cutting Result Selection -->
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Hasil {{ preparationLabel }}</h3>
                <div class="grid grid-cols-1 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Pilih Hasil {{ preparationLabel }} <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.cutting_result_id"
                      required
                      :disabled="isEditing"
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                      :class="{ 'border-red-500': form.errors.cutting_result_id, 'bg-gray-100 dark:bg-gray-600': isEditing }"
                    >
                      <option value="0" disabled>-- Pilih Hasil {{ preparationLabel }} --</option>
                      <option v-for="cr in cuttingResults" :key="cr.id" :value="cr.id">
                        {{ cr.cutting_order.order_number }} - {{ cr.cutting_order.pattern.name }} ({{ cr.actual_quantity - cr.defect_quantity }} pcs good)
                      </option>
                    </select>
                    <p v-if="form.errors.cutting_result_id" class="mt-1 text-sm text-red-600">{{ form.errors.cutting_result_id }}</p>
                  </div>

                  <!-- Cutting Result Info -->
                  <div v-if="selectedCuttingResult" class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-md">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ patternLabel }}</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ selectedCuttingResult.cutting_order.pattern.name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ selectedCuttingResult.cutting_order.pattern.code }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Good Pieces</p>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ selectedCuttingResult.actual_quantity - selectedCuttingResult.defect_quantity }} pcs</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Production Type & Details -->
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Detail Produksi</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Tipe Produksi <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.type"
                      required
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                      :class="{ 'border-red-500': form.errors.type }"
                    >
                      <option value="internal">Internal</option>
                      <option value="external">Eksternal ({{ contractorLabel }})</option>
                    </select>
                    <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                  </div>

                  <div v-if="form.type === 'external'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      {{ contractorLabel }} <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.contractor_id"
                      :required="form.type === 'external'"
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                      :class="{ 'border-red-500': form.errors.contractor_id }"
                    >
                      <option :value="null" disabled>-- Pilih {{ contractorLabel }} --</option>
                      <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">
                        {{ contractor.name }} - Rp {{ contractor.rate_per_piece.toLocaleString('id-ID') }}/pcs
                      </option>
                    </select>
                    <p v-if="form.errors.contractor_id" class="mt-1 text-sm text-red-600">{{ form.errors.contractor_id }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Jumlah Diminta <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model.number="form.quantity_requested"
                      type="number"
                      required
                      min="1"
                      :max="availableQuantity || undefined"
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                      :class="{ 'border-red-500': form.errors.quantity_requested }"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tersedia: {{ availableQuantity }} pcs</p>
                    <p v-if="form.errors.quantity_requested" class="mt-1 text-sm text-red-600">{{ form.errors.quantity_requested }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Tanggal Diminta <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.requested_date"
                      type="date"
                      required
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                      :class="{ 'border-red-500': form.errors.requested_date }"
                    />
                    <p v-if="form.errors.requested_date" class="mt-1 text-sm text-red-600">{{ form.errors.requested_date }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Tanggal Janji
                    </label>
                    <input
                      v-model="form.promised_date"
                      type="date"
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                      :class="{ 'border-red-500': form.errors.promised_date }"
                    />
                    <p v-if="form.errors.promised_date" class="mt-1 text-sm text-red-600">{{ form.errors.promised_date }}</p>
                  </div>
                </div>
              </div>

              <!-- Cost Estimation -->
              <div v-if="form.type === 'external' && selectedContractor && form.quantity_requested > 0" class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Estimasi Biaya</h3>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tarif per Piece</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                      Rp {{ selectedContractor.rate_per_piece.toLocaleString('id-ID') }}
                    </p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Estimasi</p>
                    <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                      Rp {{ estimatedCost.toLocaleString('id-ID') }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Priority & Labor Cost -->
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Prioritas & Biaya</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Prioritas <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.priority"
                      required
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                      :class="{ 'border-red-500': form.errors.priority }"
                    >
                      <option value="low">Low</option>
                      <option value="normal">Normal</option>
                      <option value="high">High</option>
                      <option value="urgent">Urgent</option>
                    </select>
                    <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">{{ form.errors.priority }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Biaya Tenaga Kerja (estimasi)
                    </label>
                    <input
                      v-model.number="form.labor_cost"
                      type="number"
                      min="0"
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                      :class="{ 'border-red-500': form.errors.labor_cost }"
                    />
                    <p v-if="form.errors.labor_cost" class="mt-1 text-sm text-red-600">{{ form.errors.labor_cost }}</p>
                  </div>
                </div>
              </div>

              <!-- Notes -->\n              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Catatan
                </label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  :class="{ 'border-red-500': form.errors.notes }"
                ></textarea>
                <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
              </div>

              <!-- Submit Button -->
              <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <Link
                  href="/production-orders"
                  class="order-2 sm:order-1 px-4 py-2 text-sm font-medium text-center text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
                >
                  Batal
                </Link>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="order-1 sm:order-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Update' : 'Buat Order') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
