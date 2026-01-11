<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
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

    <!-- Page Content -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="mb-6 flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold text-gray-900">
                  {{ isEditing ? `Edit ${contractorLabel}` : `Tambah ${contractorLabel} Baru` }}
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                  {{ isEditing ? `Perbarui informasi ${contractorLabelLower}` : `Tambahkan ${contractorLabelLower} untuk produksi eksternal` }}
                </p>
              </div>
              <Link
                href="/contractors"
                class="text-sm text-gray-600 hover:text-gray-900"
              >
                ← Kembali
              </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
              <!-- Basic Information -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Nama {{ contractorLabel }} <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Tipe <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.type"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.type }"
                    >
                      <option value="individual">Individual</option>
                      <option value="company">Perusahaan</option>
                    </select>
                    <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Spesialisasi <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.specialty"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.specialty }"
                    >
                      <option value="sewing">Penjahit</option>
                      <option value="baking">Tukang Kue</option>
                      <option value="crafting">Perajin</option>
                      <option value="other">Lainnya</option>
                    </select>
                    <p v-if="form.errors.specialty" class="mt-1 text-sm text-red-600">{{ form.errors.specialty }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Status <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.status"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.status }"
                    >
                      <option value="active">Aktif</option>
                      <option value="inactive">Non-Aktif</option>
                    </select>
                    <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                  </div>
                </div>
              </div>

              <!-- Contact Information -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Kontak</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Nama Kontak
                    </label>
                    <input
                      v-model="form.contact_person"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.contact_person }"
                    />
                    <p v-if="form.errors.contact_person" class="mt-1 text-sm text-red-600">{{ form.errors.contact_person }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Telepon
                    </label>
                    <input
                      v-model="form.phone"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.phone }"
                    />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Email
                    </label>
                    <input
                      v-model="form.email"
                      type="email"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.email }"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Alamat
                    </label>
                    <textarea
                      v-model="form.address"
                      rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.address }"
                    ></textarea>
                    <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                  </div>
                </div>
              </div>

              <!-- Pricing Information -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Harga</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Tarif per Piece (Rp)
                    </label>
                    <input
                      v-model="form.rate_per_piece"
                      type="number"
                      step="0.01"
                      min="0"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.rate_per_piece }"
                    />
                    <p v-if="form.errors.rate_per_piece" class="mt-1 text-sm text-red-600">{{ form.errors.rate_per_piece }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Tarif per Jam (Rp)
                    </label>
                    <input
                      v-model="form.rate_per_hour"
                      type="number"
                      step="0.01"
                      min="0"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.rate_per_hour }"
                    />
                    <p v-if="form.errors.rate_per_hour" class="mt-1 text-sm text-red-600">{{ form.errors.rate_per_hour }}</p>
                  </div>

                  <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Syarat Pembayaran
                    </label>
                    <textarea
                      v-model="form.payment_terms"
                      rows="2"
                      placeholder="Contoh: 50% DP, 50% setelah selesai"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.payment_terms }"
                    ></textarea>
                    <p v-if="form.errors.payment_terms" class="mt-1 text-sm text-red-600">{{ form.errors.payment_terms }}</p>
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div>
                <label class="block text-sm font-medium text-gray-700">
                  Catatan
                </label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  :class="{ 'border-red-500': form.errors.notes }"
                ></textarea>
                <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
              </div>

              <!-- Submit Button -->
              <div class="flex items-center justify-end gap-4">
                <Link
                  href="/contractors"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                  Batal
                </Link>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Update' : 'Simpan') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
