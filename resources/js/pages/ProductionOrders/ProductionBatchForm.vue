<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

interface ProductionOrder {
  id: number
  type: string
  status: string
  quantity_produced: number
}

const props = defineProps<{
  order: ProductionOrder
}>()

const form = useForm({
  quantity_received: 0,
  quantity_good: 0,
  quantity_defect: 0,
  quantity_reject: 0,
  grade: 'A',
  labor_cost_actual: null as number | null,
  production_cost: null as number | null,
  production_date: new Date().toISOString().split('T')[0],
  received_date: new Date().toISOString().split('T')[0],
  expiry_date: '' as string,
  qc_notes: '' as string,
  defect_reasons: '' as string,
})

watch(
  () => [form.quantity_received, form.quantity_good, form.quantity_defect],
  () => {
    const received = Math.max(0, Number(form.quantity_received) || 0)
    const good = Math.max(0, Number(form.quantity_good) || 0)
    const defect = Math.max(0, Number(form.quantity_defect) || 0)
    form.quantity_reject = Math.max(0, received - good - defect)
  },
  { immediate: true },
)

const submit = () => {
  form.post(`/production-orders/${props.order.id}/receive`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-gray-700">
          Quantity Diterima <span class="text-red-500">*</span>
        </label>
        <input
          v-model.number="form.quantity_received"
          type="number"
          min="1"
          :max="remaining || undefined"
          required
          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
          :class="{ 'border-red-500': form.errors.quantity_received }"
        />
        <p class="mt-1 text-xs text-gray-500">Sisa target: {{ remaining }} pcs</p>
        <p v-if="form.errors.quantity_received" class="mt-1 text-sm text-red-600">{{ form.errors.quantity_received }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">
          Grade <span class="text-red-500">*</span>
        </label>
        <select
          v-model="form.grade"
          required
          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
          :class="{ 'border-red-500': form.errors.grade }"
        >
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="reject">Reject</option>
        </select>
        <p v-if="form.errors.grade" class="mt-1 text-sm text-red-600">{{ form.errors.grade }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">
          Tanggal Produksi <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.production_date"
          type="date"
          required
          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
          :class="{ 'border-red-500': form.errors.production_date }"
        />
        <p v-if="form.errors.production_date" class="mt-1 text-sm text-red-600">{{ form.errors.production_date }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">
          Tanggal Diterima <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.received_date"
          type="date"
          required
          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
          :class="{ 'border-red-500': form.errors.received_date }"
        />
        <p v-if="form.errors.received_date" class="mt-1 text-sm text-red-600">{{ form.errors.received_date }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div>
        <label class="block text-sm font-medium text-gray-700">Good</label>
        <input
          v-model.number="form.quantity_good"
          type="number"
          min="0"
          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
          :class="{ 'border-red-500': form.errors.quantity_good }"
        />
        <p v-if="form.errors.quantity_good" class="mt-1 text-sm text-red-600">{{ form.errors.quantity_good }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Defect</label>
        <input
          v-model.number="form.quantity_defect"
          type="number"
          min="0"
          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
          :class="{ 'border-red-500': form.errors.quantity_defect }"
        />
        <p v-if="form.errors.quantity_defect" class="mt-1 text-sm text-red-600">{{ form.errors.quantity_defect }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Reject</label>
        <input
          v-model.number="form.quantity_reject"
          type="number"
          min="0"
          readonly
          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 dark:text-gray-400 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
          :class="{ 'border-red-500': form.errors.quantity_reject }"
        />
        <p v-if="form.errors.quantity_reject" class="mt-1 text-sm text-red-600">{{ form.errors.quantity_reject }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-gray-700">Biaya Tenaga Kerja (actual)</label>
        <input
          v-model.number="form.labor_cost_actual"
          type="number"
          min="0"
          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
          :class="{ 'border-red-500': form.errors.labor_cost_actual }"
        />
        <p v-if="form.errors.labor_cost_actual" class="mt-1 text-sm text-red-600">{{ form.errors.labor_cost_actual }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Biaya Produksi (actual)</label>
        <input
          v-model.number="form.production_cost"
          type="number"
          min="0"
          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
          :class="{ 'border-red-500': form.errors.production_cost }"
        />
        <p v-if="form.errors.production_cost" class="mt-1 text-sm text-red-600">{{ form.errors.production_cost }}</p>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700">Expiry Date (optional)</label>
      <input
        v-model="form.expiry_date"
        type="date"
        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
        :class="{ 'border-red-500': form.errors.expiry_date }"
      />
      <p v-if="form.errors.expiry_date" class="mt-1 text-sm text-red-600">{{ form.errors.expiry_date }}</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700">QC Notes</label>
      <textarea
        v-model="form.qc_notes"
        rows="3"
        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
        :class="{ 'border-red-500': form.errors.qc_notes }"
      ></textarea>
      <p v-if="form.errors.qc_notes" class="mt-1 text-sm text-red-600">{{ form.errors.qc_notes }}</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700">Alasan Defect/Reject</label>
      <textarea
        v-model="form.defect_reasons"
        rows="2"
        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
        :class="{ 'border-red-500': form.errors.defect_reasons }"
      ></textarea>
      <p v-if="form.errors.defect_reasons" class="mt-1 text-sm text-red-600">{{ form.errors.defect_reasons }}</p>
    </div>

    <div class="flex items-center justify-end gap-3">
      <button
        type="submit"
        :disabled="form.processing"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-50"
      >
        {{ form.processing ? 'Menyimpan...' : 'Terima Batch' }}
      </button>
    </div>
  </form>
</template>
