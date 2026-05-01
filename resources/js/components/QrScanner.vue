<template>
    <div class="relative">
        <!-- Scanner Container -->
        <div v-if="isScanning" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Scan QR Code
                    </h3>
                    <button
                        type="button"
                        @click="stopScanning"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Scanner Area -->
                <div class="relative bg-black rounded-lg overflow-hidden" style="min-height: 400px">
                    <div id="qr-reader" class="w-full"></div>
                </div>

                <!-- Status Message -->
                <div v-if="statusMessage" class="mt-4 p-3 rounded" :class="statusClass">
                    {{ statusMessage }}
                </div>

                <!-- Instructions -->
                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    <p>Posisikan QR code item atau lokasi dalam frame kamera untuk melakukan scan.</p>
                </div>
            </div>
        </div>

        <!-- Trigger Button (Slot) -->
        <slot :startScanning="startScanning" />
    </div>
</template>

<script setup lang="ts">
import { ref, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Html5Qrcode } from 'html5-qrcode'

const isScanning = ref(false)
const statusMessage = ref('')
const statusClass = ref('')
let html5QrCode: Html5Qrcode | null = null

async function startScanning() {
    isScanning.value = true
    statusMessage.value = ''

    try {
        // Wait for DOM to be ready
        await new Promise(resolve => setTimeout(resolve, 100))

        html5QrCode = new Html5Qrcode('qr-reader')

        await html5QrCode.start(
            { facingMode: 'environment' },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 },
            },
            onScanSuccess,
            onScanFailure
        )
    } catch (err) {
        console.error('Error starting scanner:', err)
        statusMessage.value = 'Gagal mengakses kamera. Pastikan izin kamera diberikan.'
        statusClass.value = 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
    }
}

async function stopScanning() {
    try {
        if (html5QrCode) {
            await html5QrCode.stop()
            html5QrCode.clear()
            html5QrCode = null
        }
    } catch (err) {
        console.error('Error stopping scanner:', err)
    }

    isScanning.value = false
    statusMessage.value = ''
}

async function onScanSuccess(decodedText: string) {
    statusMessage.value = 'QR Code terdeteksi! Mencari data...'
    statusClass.value = 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'

    try {
        // Stop scanning while processing
        if (html5QrCode) {
            await html5QrCode.pause(true)
        }

        // Look up the item/location by QR code value
        const response = await fetch('/inventory/items/scan-lookup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ qr_code: decodedText }),
        })

        const data = await response.json()

        if (data.success && data.redirect_url) {
            const label = data.type === 'location' ? 'Lokasi' : 'Item'
            statusMessage.value = `${label} ditemukan! Mengarahkan...`
            statusClass.value = 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'

            // Clean up before navigation
            await stopScanning()

            // Navigate to item detail page
            router.visit(data.redirect_url)
        } else {
            statusMessage.value = data.message || 'Item atau lokasi tidak ditemukan.'
            statusClass.value = 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'

            // Resume scanning after 2 seconds
            setTimeout(() => {
                if (html5QrCode) {
                    html5QrCode.resume()
                }
                statusMessage.value = ''
            }, 2000)
        }
    } catch (error) {
        console.error('Error during scan lookup:', error)
        statusMessage.value = 'Terjadi kesalahan saat mencari data.'
        statusClass.value = 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'

        // Resume scanning after error
        setTimeout(() => {
            if (html5QrCode) {
                html5QrCode.resume()
            }
            statusMessage.value = ''
        }, 2000)
    }
}

function onScanFailure() {
    // Ignore scan failures (they happen continuously while scanning)
}

// Cleanup on component unmount
onUnmounted(async () => {
    await stopScanning()
})
</script>
