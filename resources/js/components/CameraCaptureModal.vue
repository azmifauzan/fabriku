<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import Modal from '@/Components/Modal.vue';
import { Camera, X, RefreshCw } from 'lucide-vue-next';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close', 'capture']);

const videoEl = ref<HTMLVideoElement | null>(null);
const canvasEl = ref<HTMLCanvasElement | null>(null);
const stream = ref<MediaStream | null>(null);
const error = ref<string | null>(null);
const facingMode = ref<'environment' | 'user'>('environment');

const startCamera = async () => {
    try {
        error.value = null;
        if (stream.value) {
            stopCamera();
        }

        stream.value = await navigator.mediaDevices.getUserMedia({
            video: { 
                facingMode: facingMode.value,
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        });

        if (videoEl.value) {
            videoEl.value.srcObject = stream.value;
        }
    } catch (err) {
        console.error('Error accessing camera:', err);
        error.value = 'Tidak dapat mengakses kamera. Pastikan izin diberikan.';
    }
};

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
        stream.value = null;
    }
};

const switchCamera = () => {
    facingMode.value = facingMode.value === 'environment' ? 'user' : 'environment';
    startCamera();
};

const capture = () => {
    if (!videoEl.value || !canvasEl.value) return;

    const context = canvasEl.value.getContext('2d');
    if (!context) return;

    // Set canvas dimensions to match video
    canvasEl.value.width = videoEl.value.videoWidth;
    canvasEl.value.height = videoEl.value.videoHeight;

    // Draw video frame to canvas
    context.drawImage(videoEl.value, 0, 0, canvasEl.value.width, canvasEl.value.height);

    // Convert to file
    canvasEl.value.toBlob((blob) => {
        if (blob) {
            const file = new File([blob], `camera_${Date.now()}.jpg`, { type: 'image/jpeg' });
            emit('capture', file);
            close();
        }
    }, 'image/jpeg', 0.8);
};

const close = () => {
    stopCamera();
    emit('close');
};

// Start camera when modal is shown
const onShow = () => { // logic handled by parent or watch if needed, simple approach:
    startCamera();
};

// Watch prop to start/stop
// Using Modal's slots or life cycle might be tricky if Modal doesn't emit 'show'. 
// We will just expose a method or rely on the parent mounting this component only when shown 
// or use a watcher.
import { watch } from 'vue';
watch(() => props.show, (newVal) => {
    if (newVal) startCamera();
    else stopCamera();
});

onBeforeUnmount(() => {
    stopCamera();
});
</script>

<template>
    <Modal :show="show" maxWidth="lg" @close="close">
        <div class="relative overflow-hidden bg-black rounded-lg">
            <!-- Header -->
            <div class="absolute top-0 left-0 right-0 z-10 flex items-center justify-between p-4 bg-gradient-to-b from-black/50 to-transparent">
                <h3 class="text-white font-medium">Ambil Foto</h3>
                <button @click="close" class="text-white hover:text-gray-300">
                    <X class="h-6 w-6" />
                </button>
            </div>

            <!-- Video Preview -->
            <div class="relative aspect-[3/4] sm:aspect-[4/3] bg-black">
                <video 
                    ref="videoEl" 
                    autoplay 
                    playsinline 
                    muted 
                    class="h-full w-full object-cover"
                    :class="{ 'scale-x-[-1]': facingMode === 'user' }" 
                ></video>
                
                <div v-if="error" class="absolute inset-0 flex items-center justify-center p-4 text-center">
                    <p class="text-red-400 mb-2">{{ error }}</p>
                </div>
            </div>

            <!-- Controls -->
            <div class="absolute bottom-0 left-0 right-0 z-10 p-6 flex items-center justify-center gap-8 bg-gradient-to-t from-black/80 to-transparent">
                <!-- Switch Camera (only visible if not error) -->
                <button 
                    v-if="!error"
                    @click="switchCamera" 
                    class="rounded-full bg-white/20 p-3 text-white backdrop-blur-sm transition hover:bg-white/30"
                    title="Ganti Kamera"
                >
                    <RefreshCw class="h-6 w-6" />
                </button>

                <!-- Capture Button -->
                <button 
                    v-if="!error"
                    @click="capture"
                    class="group relative flex h-16 w-16 items-center justify-center rounded-full border-4 border-white bg-transparent transition hover:bg-white/20 active:scale-95"
                >
                    <div class="h-12 w-12 rounded-full bg-white transition group-hover:scale-90"></div>
                </button>

                <!-- Spacer to center capture button -->
                <div class="w-12"></div> 
            </div>

            <canvas ref="canvasEl" class="hidden"></canvas>
        </div>
    </Modal>
</template>
