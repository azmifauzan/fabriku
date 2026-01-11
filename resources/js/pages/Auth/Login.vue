<script setup lang="ts">
import { Form } from '@inertiajs/vue3'

const form = {
  email: '',
  password: '',
  remember: false
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Fabriku
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Masuk ke akun Anda
        </p>
      </div>

      <Form
        action="/login"
        method="post"
        class="mt-8 space-y-6"
        v-slot="{ processing, errors }"
      >
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">Email</label>
            <input
              id="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              v-model="form.email"
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              :class="{ 'border-red-500': errors.email }"
              placeholder="Email"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Password</label>
            <input
              id="password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              v-model="form.password"
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              :class="{ 'border-red-500': errors.password || errors.email }"
              placeholder="Password"
            />
          </div>
        </div>

        <div v-if="errors.email" class="text-red-500 text-sm">
          {{ errors.email }}
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember"
              name="remember"
              type="checkbox"
              v-model="form.remember"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-900">
              Ingat saya
            </label>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="processing"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="processing">Memproses...</span>
            <span v-else>Masuk</span>
          </button>
        </div>

        <div class="text-center text-sm text-gray-600">
          <p class="font-semibold text-gray-900 mb-3">🎯 Demo Credentials</p>
          
          <div class="grid grid-cols-1 gap-3 text-left">
            <!-- Garment Demo -->
            <div class="bg-indigo-50 p-3 rounded-md border border-indigo-200">
              <div class="flex items-center gap-2 mb-2">
                <span class="text-base">🧵</span>
                <span class="font-semibold text-gray-900 text-sm">Konveksi Fabriku</span>
              </div>
              <div class="space-y-1 text-xs text-gray-600">
                <p>Email: <span class="font-mono text-indigo-600">admin@konveksi.com</span></p>
                <p>Password: <span class="font-mono text-indigo-600">password</span></p>
                <p class="text-xs text-gray-500 mt-1">Kategori: Garment & Konveksi</p>
              </div>
            </div>
            
            <!-- Food Demo -->
            <div class="bg-pink-50 p-3 rounded-md border border-pink-200">
              <div class="flex items-center gap-2 mb-2">
                <span class="text-base">🍰</span>
                <span class="font-semibold text-gray-900 text-sm">Kue Mama Homemade</span>
              </div>
              <div class="space-y-1 text-xs text-gray-600">
                <p>Email: <span class="font-mono text-pink-600">admin@kuemama.com</span></p>
                <p>Password: <span class="font-mono text-pink-600">password</span></p>
                <p class="text-xs text-gray-500 mt-1">Kategori: Makanan & Kue</p>
              </div>
            </div>
          </div>
        </div>
      </Form>
    </div>
  </div>
</template>
