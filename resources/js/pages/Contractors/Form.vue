<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import FormField from '@/components/FormField.vue'
import FormSection from '@/components/FormSection.vue'
import { useBusinessContext } from '@/composables/useBusinessContext'

interface Contractor {
  id?: number
  name: string
  type: string
  specialty: string
  contact_person: string
  email: string
  phone: string
  address: string
  rate_per_piece: string
  rate_per_hour: string
  payment_terms: string
  status: string
  notes: string
}

const props = defineProps<{
  contractor?: Contractor
}>()

const { tenant, term, termLower } = useBusinessContext()

const contractorLabel = computed(() => term('contractor', 'Kontraktor'))
const contractorLabelLower = computed(() => termLower('contractor', 'kontraktor'))
const defaultSpecialty = computed(() => (tenant.value?.business_category === 'food' ? 'baking' : 'sewing'))

const form = useForm({
  name: props.contractor?.name || '',
  type: props.contractor?.type || 'individual',
  specialty: props.contractor?.specialty || defaultSpecialty.value,
  contact_person: props.contractor?.contact_person || '',
  email: props.contractor?.email || '',
  phone: props.contractor?.phone || '',
  address: props.contractor?.address || '',
  rate_per_piece: props.contractor?.rate_per_piece || '0',
  rate_per_hour: props.contractor?.rate_per_hour || '0',
  payment_terms: props.contractor?.payment_terms || '',
  status: props.contractor?.status || 'active',
  notes: props.contractor?.notes || '',
})

const submit = () => {
  if (props.contractor?.id) {
    form.put(`/contractors/${props.contractor.id}`, {
      preserveScroll: true,
    })
  } else {
    form.post('/contractors', {
      preserveScroll: true,
    })
  }
}

const isEditing = !!props.contractor?.id
</script>

<template>
  <AppLayout>
    <Head :title="isEditing ? `Edit ${contractorLabel}` : `Tambah ${contractorLabel}`" />

    <div class="py-6 px-6">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
              {{ isEditing ? `Edit ${contractorLabel}` : `Tambah ${contractorLabel} Baru` }}
            </h1>
            <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">
              {{ isEditing ? `Perbarui informasi ${contractorLabelLower}` : `Tambahkan ${contractorLabelLower} untuk produksi eksternal` }}
            </p>
          </div>
          <Link
            href="/contractors"
            class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Basic Information -->
          <FormSection title="Informasi Dasar" description="Data identitas kontraktor">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.name"
                :label="`Nama ${contractorLabel}`"
                type="text"
                placeholder="Nama lengkap kontraktor"
                :required="true"
                :error="form.errors.name"
              />

              <FormField
                v-model="form.type"
                label="Tipe"
                type="select"
                :required="true"
                :error="form.errors.type"
                :options="[
                  { value: 'individual', label: 'Individual' },
                  { value: 'company', label: 'Perusahaan' }
                ]"
              />

              <FormField
                v-model="form.specialty"
                label="Spesialisasi"
                type="select"
                :required="true"
                :error="form.errors.specialty"
                :options="[
                  { value: 'sewing', label: 'Penjahit' },
                  { value: 'baking', label: 'Tukang Kue' },
                  { value: 'crafting', label: 'Perajin' },
                  { value: 'other', label: 'Lainnya' }
                ]"
              />

              <FormField
                v-model="form.status"
                label="Status"
                type="select"
                :required="true"
                :error="form.errors.status"
                :options="[
                  { value: 'active', label: 'Aktif' },
                  { value: 'inactive', label: 'Non-Aktif' }
                ]"
              />
            </div>
          </FormSection>

          <!-- Contact Information -->
          <FormSection title="Informasi Kontak" description="Detail kontak dan alamat">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.contact_person"
                label="Nama Kontak"
                type="text"
                placeholder="Nama person in charge"
                :error="form.errors.contact_person"
              />

              <FormField
                v-model="form.phone"
                label="Telepon"
                type="tel"
                placeholder="08xxxxxxxxxx"
                :error="form.errors.phone"
              />

              <FormField
                v-model="form.email"
                label="Email"
                type="email"
                placeholder="email@example.com"
                :error="form.errors.email"
              />

              <FormField
                v-model="form.address"
                label="Alamat"
                type="textarea"
                placeholder="Alamat lengkap"
                :rows="3"
                :error="form.errors.address"
              />
            </div>
          </FormSection>

          <!-- Pricing Information -->
          <FormSection title="Informasi Harga" description="Tarif dan syarat pembayaran">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.rate_per_piece"
                label="Tarif per Piece (Rp)"
                type="number"
                placeholder="0"
                hint="Tarif per piece/unit produk"
                :error="form.errors.rate_per_piece"
              />

              <FormField
                v-model="form.rate_per_hour"
                label="Tarif per Jam (Rp)"
                type="number"
                placeholder="0"
                hint="Tarif per jam kerja"
                :error="form.errors.rate_per_hour"
              />

              <div class="md:col-span-2">
                <FormField
                  v-model="form.payment_terms"
                  label="Syarat Pembayaran"
                  type="textarea"
                  placeholder="Contoh: 50% DP, 50% setelah selesai"
                  :rows="2"
                  :error="form.errors.payment_terms"
                />
              </div>
            </div>
          </FormSection>

          <!-- Notes -->
          <FormSection title="Catatan" description="Informasi tambahan (opsional)">
            <FormField
              v-model="form.notes"
              label="Catatan"
              type="textarea"
              placeholder="Catatan tambahan tentang kontraktor..."
              :rows="3"
              :error="form.errors.notes"
            />
          </FormSection>

          <!-- Submit Button -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4">
            <Link
              href="/contractors"
              class="order-2 sm:order-1 px-6 py-2.5 text-sm font-semibold text-center text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="order-1 sm:order-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm hover:shadow-md"
            >
              {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Update' : 'Simpan') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
