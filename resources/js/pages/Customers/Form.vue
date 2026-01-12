<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import FormField from '@/components/FormField.vue'
import FormSection from '@/components/FormSection.vue'

interface Customer {
  id?: number
  code: string
  name: string
  type: string
  phone: string
  email: string
  address: string
  city: string
  province: string
  discount_percentage: string
  payment_term: string
  is_active: boolean
  notes: string
}

const props = defineProps<{
  customer?: Customer
}>()

const form = useForm({
  code: props.customer?.code || '',
  name: props.customer?.name || '',
  type: props.customer?.type || 'retail',
  phone: props.customer?.phone || '',
  email: props.customer?.email || '',
  address: props.customer?.address || '',
  city: props.customer?.city || '',
  province: props.customer?.province || '',
  discount_percentage: props.customer?.discount_percentage || '0',
  payment_term: props.customer?.payment_term || 'cash',
  notes: props.customer?.notes || '',
  is_active: props.customer?.is_active ?? true,
})

const submit = () => {
  if (props.customer?.id) {
    form.put(`/customers/${props.customer.id}`, {
      preserveScroll: true,
    })
  } else {
    form.post('/customers', {
      preserveScroll: true,
    })
  }
}

const isEditing = !!props.customer?.id
</script>

<template>
  <div>
    <Head :title="isEditing ? 'Edit Customer' : 'Tambah Customer'" />

    <div class="py-6 px-6">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
              {{ isEditing ? 'Edit Customer' : 'Tambah Customer Baru' }}
            </h1>
            <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">
              {{ isEditing ? 'Perbarui informasi customer' : 'Tambahkan data customer untuk penjualan produk' }}
            </p>
          </div>
          <Link
            href="/customers"
            class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Basic Information -->
          <FormSection title="Informasi Dasar" description="Data identitas customer">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.code"
                label="Kode Customer"
                type="text"
                placeholder="Contoh: CUST-001"
                :required="true"
                :error="form.errors.code"
                hint="Kode unik untuk identifikasi customer"
              />

              <FormField
                v-model="form.name"
                label="Nama Customer"
                type="text"
                placeholder="Nama lengkap customer"
                :required="true"
                :error="form.errors.name"
              />

              <FormField
                v-model="form.type"
                label="Tipe Customer"
                type="select"
                :required="true"
                :error="form.errors.type"
                :options="[
                  { value: 'retail', label: 'Retail' },
                  { value: 'reseller', label: 'Reseller' },
                  { value: 'online', label: 'Online' }
                ]"
              />

              <div class="flex items-center pt-8">
                <FormField
                  v-model="form.is_active"
                  label="Customer aktif (dapat bertransaksi)"
                  type="checkbox"
                />
              </div>
            </div>
          </FormSection>

          <!-- Contact Information -->
          <FormSection title="Informasi Kontak" description="Detail kontak dan alamat">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.phone"
                label="Telepon"
                type="text"
                placeholder="08123456789"
                :error="form.errors.phone"
              />

              <FormField
                v-model="form.email"
                label="Email"
                type="email"
                placeholder="customer@example.com"
                :error="form.errors.email"
              />

              <div class="md:col-span-2">
                <FormField
                  v-model="form.address"
                  label="Alamat"
                  type="textarea"
                  placeholder="Alamat lengkap customer"
                  :rows="2"
                  :error="form.errors.address"
                />
              </div>

              <FormField
                v-model="form.city"
                label="Kota"
                type="text"
                placeholder="Contoh: Jakarta"
                :error="form.errors.city"
              />

              <FormField
                v-model="form.province"
                label="Provinsi"
                type="text"
                placeholder="Contoh: DKI Jakarta"
                :error="form.errors.province"
              />
            </div>
          </FormSection>

          <!-- Pricing & Terms -->
          <FormSection title="Harga & Term" description="Informasi diskon dan pembayaran">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.discount_percentage"
                label="Diskon Default (%)"
                type="number"
                placeholder="0"
                :error="form.errors.discount_percentage"
                hint="Diskon standar untuk customer ini"
                min="0"
                max="100"
                step="0.01"
              />

              <FormField
                v-model="form.payment_term"
                label="Term Pembayaran"
                type="select"
                :required="true"
                :error="form.errors.payment_term"
                :options="[
                  { value: 'cash', label: 'Cash' },
                  { value: 'credit_7', label: 'Kredit 7 Hari' },
                  { value: 'credit_14', label: 'Kredit 14 Hari' },
                  { value: 'credit_30', label: 'Kredit 30 Hari' }
                ]"
              />
            </div>
          </FormSection>

          <!-- Notes -->
          <FormSection title="Catatan" description="Informasi tambahan (opsional)">
            <FormField
              v-model="form.notes"
              label="Catatan"
              type="textarea"
              placeholder="Catatan atau informasi tambahan tentang customer"
              :rows="3"
              :error="form.errors.notes"
            />
          </FormSection>

          <!-- Submit Button -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4">
            <Link
              href="/customers"
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
              {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Customer') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>