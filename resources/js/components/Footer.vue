<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="px-6 py-4">
            <div class="flex flex-col gap-2 text-sm text-gray-600 sm:flex-row sm:items-center sm:justify-between dark:text-gray-400">
                <p>&copy; {{ new Date().getFullYear() }} Fabriku. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <p>Aplikasi manajemen produksi dan penjualan untuk UMKM</p>
                    <Link href="/privasi" class="hover:underline">Privasi</Link>
                    <Link href="/syarat-ketentuan" class="hover:underline">Syarat & Ketentuan</Link>
                </div>
            </div>
        </div>
    </footer>
</template>
