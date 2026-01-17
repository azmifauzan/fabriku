<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Navbar from '@/components/Navbar.vue';
import Sidebar from '@/components/Sidebar.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const isSidebarOpen = ref(false);
const isMobile = ref(false);
const didInitSidebar = ref(false);
const wasAutoClosedForMobile = ref(false);

const checkMobile = () => {
    const nextIsMobile = window.innerWidth < 768;

    if (!didInitSidebar.value) {
        isMobile.value = nextIsMobile;
        isSidebarOpen.value = !nextIsMobile;
        didInitSidebar.value = true;
        return;
    }

    // Mobile: force close (drawer)
    if (nextIsMobile) {
        if (isSidebarOpen.value) {
            wasAutoClosedForMobile.value = true;
        }

        isMobile.value = true;
        isSidebarOpen.value = false;
        return;
    }

    // Back to desktop: re-open only if it was auto-closed by mobile
    isMobile.value = false;

    if (wasAutoClosedForMobile.value) {
        isSidebarOpen.value = true;
        wasAutoClosedForMobile.value = false;
    }
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
    if (isMobile.value) {
        isSidebarOpen.value = false;
    }
};

const user = computed(() => page.props.auth?.user || null);
const currentRoute = computed(() => page.url);
</script>

<template>
    <div class="min-h-screen bg-gray-50 transition-colors dark:bg-gray-900">
        <!-- Navbar -->
        <Navbar :user="user" :is-mobile="isMobile" @toggle-sidebar="toggleSidebar" />

        <!-- Mobile Overlay -->
        <div v-if="isMobile && isSidebarOpen" class="fixed inset-0 z-40 bg-black/50 md:hidden" @click="closeSidebar" />

        <!-- Sidebar -->
        <Sidebar :is-open="isSidebarOpen" :is-mobile="isMobile" :current-route="currentRoute" @toggle="toggleSidebar" @close="closeSidebar" />

        <!-- Main Content -->
        <main
            :class="[
                'pt-16 transition-all duration-300',
                // Mobile: no margin, Desktop: with sidebar margin
                isMobile ? 'ml-0' : isSidebarOpen ? 'md:ml-64' : 'md:ml-16',
            ]"
        >
            <div class="min-h-[calc(100vh-8rem)]">
                <slot />
            </div>

            <!-- Footer -->
            <Footer />
        </main>
    </div>
</template>
