<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import FormField from '@/components/FormField.vue'
import FormSection from '@/components/FormSection.vue'

interface Staff {
  id?: number
  code: string
  name: string
  position: string | null
  phone: string | null
  email: string | null
  is_active: boolean
}

const props = defineProps<{
  staff?: Staff
}>()

const form = useForm({
  code: props.staff?.code || '',
  name: props.staff?.name || '',
  position: props.staff?.position || '',
  phone: props.staff?.phone || '',
  email: props.staff?.email || '',
  is_active: props.staff?.is_active ?? true,
})

const submit = () => {
  if (props.staff?.id) {
    form.put(`/staff/${props.staff.id}`, {
      preserveScroll: true,
    })
  } else {
    form.post('/staff', {
      preserveScroll: true,
    })
  }
}

const isEditing = !!props.staff?.id
</script>

<template>
  <AppLayout>
    <Head :title="isEditing ? 'Edit Staff' : 'Tambah Staff'" />

    <div class="py-6 px-6">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
              {{ isEditing ? 'Edit Staff' : 'Tambah Staff Baru' }}
            </h1>
            <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">
              {{ isEditing ? 'Perbarui informasi staff' : 'Tambahkan staff / karyawan baru' }}
            </p>
          </div>
          <Link
            href="/staff"
            class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Basic Information -->
          <FormSection title="Informasi Dasar" description="Data identifikasi staff">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.code"
                label="Kode Staff"
                type="text"
                placeholder="Contoh: STF001"
                :required="true"
                :error="form.errors.code"
                hint="Kode unik untuk staff"
              />

              <FormField
                v-model="form.name"
                label="Nama Lengkap"
                type="text"
                placeholder="Nama lengkap staff"
                :required="true"
                :error="form.errors.name"
              />

              <div class="md:col-span-2">
                <FormField
                  v-model="form.position"
                  label="Posisi / Jabatan"
                  type="text"
                  placeholder="Contoh: Supervisor Produksi"
                  :error="form.errors.position"
                />
              </div>
            </div>
          </FormSection>

          <!-- Contact Information -->
          <FormSection title="Informasi Kontak" description="Data kontak staff">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.phone"
                label="Nomor Telepon"
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
            </div>
          </FormSection>

          <!-- Status -->
          <FormSection title="Status" description="Status aktif staff">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.is_active"
                label="Aktif (dapat digunakan)"
                type="checkbox"
              />
            </div>
          </FormSection>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <Link
              href="/staff"
              class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition-colors font-medium"
            >
              {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Update' : 'Simpan') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

