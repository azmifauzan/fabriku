<script setup lang="ts">
import { useDarkMode } from '@/composables/useDarkMode';
import { router } from '@inertiajs/vue3';
import { Bot, Loader2, MessageCircle, Minus, RotateCcw, Send, X } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

interface Message {
    id: number;
    role: 'user' | 'assistant' | 'system';
    content: string;
    created_at: string;
}

interface UsageStats {
    today: {
        message_count: number;
    };
    limit: number;
    remaining: number;
    plan: string;
}

const { isDark } = useDarkMode();

const isOpen = ref(false);
const isMinimized = ref(false);
const isLoading = ref(false);
const isSending = ref(false);
const inputMessage = ref('');
const messages = ref<Message[]>([]);
const messagesContainer = ref<HTMLElement | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);
const usage = ref<UsageStats | null>(null);
const error = ref<string | null>(null);

// Quick suggestions for new conversations
const quickSuggestions = [
    'Berapa penjualan bulan ini?',
    'Material apa yang stok rendah?',
    'Tunjukkan produksi yang pending',
];

const hasMessages = computed(() => messages.value.length > 0);
const canSend = computed(() => inputMessage.value.trim().length > 0 && !isSending.value);
const remainingMessages = computed(() => usage.value?.remaining ?? 0);

// Get CSRF token from cookie (more reliable than meta tag)
const getCsrfToken = (): string => {
    // Try XSRF-TOKEN cookie first (Laravel's encrypted cookie)
    const xsrfMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (xsrfMatch) {
        return decodeURIComponent(xsrfMatch[1]);
    }
    // Fallback to meta tag
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
};

// Scroll to bottom of messages
const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

// Load conversation history
const loadHistory = async () => {
    isLoading.value = true;
    error.value = null;
    
    try {
        const response = await fetch('/assistant/history', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        if (response.ok) {
            const data = await response.json();
            messages.value = data.messages || [];
            scrollToBottom();
        }
    } catch (e) {
        console.error('Failed to load chat history:', e);
    } finally {
        isLoading.value = false;
    }
};

// Load usage stats
const loadUsage = async () => {
    try {
        const response = await fetch('/assistant/usage', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        if (response.ok) {
            const data = await response.json();
            usage.value = data.usage;
        }
    } catch (e) {
        console.error('Failed to load usage:', e);
    }
};

// Send message
const sendMessage = async (content?: string) => {
    const messageContent = content || inputMessage.value.trim();
    if (!messageContent || isSending.value) return;
    
    error.value = null;
    isSending.value = true;
    
    // Add user message immediately
    const userMessage: Message = {
        id: Date.now(),
        role: 'user',
        content: messageContent,
        created_at: new Date().toISOString(),
    };
    messages.value.push(userMessage);
    inputMessage.value = '';
    scrollToBottom();
    
    try {
        const response = await fetch('/assistant/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'include',
            body: JSON.stringify({ message: messageContent }),
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            // Add assistant message
            const assistantMessage: Message = {
                id: data.message_id,
                role: 'assistant',
                content: data.message,
                created_at: new Date().toISOString(),
            };
            messages.value.push(assistantMessage);
            
            // Update usage
            if (usage.value) {
                usage.value.today.message_count++;
                usage.value.remaining = Math.max(0, usage.value.remaining - 1);
            }
        } else {
            // Handle specific error types with their messages
            const errorMessage = data.message || data.error || 'Gagal mengirim pesan. Silakan coba lagi.';
            
            // For demo/expired accounts, show the message as assistant response for better UX
            if (data.type === 'demo_account' || data.type === 'subscription_expired' || data.type === 'rate_limit') {
                const assistantMessage: Message = {
                    id: Date.now() + 1,
                    role: 'assistant',
                    content: errorMessage,
                    created_at: new Date().toISOString(),
                };
                messages.value.push(assistantMessage);
            } else {
                error.value = errorMessage;
                // Remove the user message on error
                messages.value.pop();
            }
        }
    } catch (e) {
        console.error('Failed to send message:', e);
        error.value = 'Terjadi kesalahan. Silakan coba lagi.';
        messages.value.pop();
    } finally {
        isSending.value = false;
        scrollToBottom();
    }
};

// Clear conversation
const clearConversation = async () => {
    if (!confirm('Hapus semua riwayat percakapan?')) return;
    
    try {
        const response = await fetch('/assistant/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'include',
        });
        
        if (response.ok) {
            messages.value = [];
        }
    } catch (e) {
        console.error('Failed to clear conversation:', e);
    }
};

// Toggle chat window
const toggleChat = () => {
    if (isMinimized.value) {
        isMinimized.value = false;
    } else if (isOpen.value) {
        isOpen.value = false;
    } else {
        isOpen.value = true;
        isMinimized.value = false;
        
        // Load data when opening
        if (messages.value.length === 0) {
            loadHistory();
        }
        loadUsage();
        
        // Focus input
        nextTick(() => {
            inputRef.value?.focus();
        });
    }
};

// Minimize chat
const minimizeChat = () => {
    isMinimized.value = true;
};

// Close chat
const closeChat = () => {
    isOpen.value = false;
    isMinimized.value = false;
};

// Handle quick suggestion click
const handleSuggestion = (suggestion: string) => {
    sendMessage(suggestion);
};

// Handle enter key
const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

// Format message content with markdown-like styling
const formatContent = (content: string): string => {
    return content
        // Bold
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        // Lists
        .replace(/^- (.*)$/gm, '<li>$1</li>')
        // Newlines
        .replace(/\n/g, '<br>');
};

// Watch for open state changes
watch(isOpen, (newValue) => {
    if (newValue && !isMinimized.value) {
        nextTick(() => {
            inputRef.value?.focus();
            scrollToBottom();
        });
    }
});

// Load initial data
onMounted(() => {
    loadUsage();
});
</script>

<template>
    <!-- FAB Button -->
    <button
        v-if="!isOpen"
        @click="toggleChat"
        class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg transition-all hover:bg-indigo-700 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        aria-label="Open Assistant"
    >
        <MessageCircle :size="24" />
        <span 
            v-if="usage && usage.remaining < 10"
            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-xs font-bold"
        >
            {{ usage.remaining }}
        </span>
    </button>

    <!-- Chat Window -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-4 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-4 scale-95"
    >
        <div
            v-if="isOpen"
            :class="[
                'fixed z-50 flex flex-col overflow-hidden rounded-2xl bg-white shadow-2xl transition-all dark:bg-gray-800',
                isMinimized
                    ? 'bottom-6 right-6 h-14 w-72'
                    : 'bottom-6 right-6 h-[32rem] w-96 sm:h-[36rem]',
            ]"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 bg-indigo-600 px-4 py-3 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                        <Bot :size="18" class="text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white">Fabriku Assistant</h3>
                        <p v-if="!isMinimized && usage" class="text-xs text-indigo-200">
                            {{ remainingMessages }} pesan tersisa hari ini
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        v-if="!isMinimized && hasMessages"
                        @click="clearConversation"
                        class="rounded-lg p-1.5 text-white/80 transition-colors hover:bg-white/10 hover:text-white"
                        title="Hapus percakapan"
                    >
                        <RotateCcw :size="16" />
                    </button>
                    <button
                        @click="minimizeChat"
                        class="rounded-lg p-1.5 text-white/80 transition-colors hover:bg-white/10 hover:text-white"
                        title="Minimize"
                    >
                        <Minus :size="16" />
                    </button>
                    <button
                        @click="closeChat"
                        class="rounded-lg p-1.5 text-white/80 transition-colors hover:bg-white/10 hover:text-white"
                        title="Tutup"
                    >
                        <X :size="16" />
                    </button>
                </div>
            </div>

            <!-- Content (hidden when minimized) -->
            <template v-if="!isMinimized">
                <!-- Messages Area -->
                <div 
                    ref="messagesContainer"
                    class="flex-1 overflow-y-auto p-4 space-y-4"
                >
                    <!-- Loading State -->
                    <div v-if="isLoading" class="flex items-center justify-center h-full">
                        <Loader2 :size="24" class="animate-spin text-indigo-600" />
                    </div>

                    <!-- Empty State with Suggestions -->
                    <div v-else-if="!hasMessages" class="flex flex-col items-center justify-center h-full text-center px-4">
                        <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
                            <Bot :size="32" class="text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Halo! 👋
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Saya Fabriku Assistant. Ada yang bisa saya bantu?
                        </p>
                        <div class="space-y-2 w-full">
                            <button
                                v-for="suggestion in quickSuggestions"
                                :key="suggestion"
                                @click="handleSuggestion(suggestion)"
                                class="w-full text-left px-3 py-2 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors"
                            >
                                {{ suggestion }}
                            </button>
                        </div>
                    </div>

                    <!-- Messages List -->
                    <template v-else>
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            :class="[
                                'flex',
                                message.role === 'user' ? 'justify-end' : 'justify-start',
                            ]"
                        >
                            <div
                                :class="[
                                    'max-w-[80%] rounded-2xl px-4 py-2.5 text-sm',
                                    message.role === 'user'
                                        ? 'bg-indigo-600 text-white rounded-br-md'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-bl-md',
                                ]"
                            >
                                <div 
                                    class="prose prose-sm dark:prose-invert max-w-none"
                                    v-html="formatContent(message.content)"
                                />
                            </div>
                        </div>

                        <!-- Typing Indicator -->
                        <div v-if="isSending" class="flex justify-start">
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl rounded-bl-md px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Error Message -->
                <div 
                    v-if="error"
                    class="px-4 py-2 bg-red-50 dark:bg-red-900/20 border-t border-red-200 dark:border-red-800"
                >
                    <p class="text-xs text-red-600 dark:text-red-400">{{ error }}</p>
                </div>

                <!-- Input Area -->
                <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                        <input
                            ref="inputRef"
                            v-model="inputMessage"
                            @keydown="handleKeydown"
                            type="text"
                            placeholder="Ketik pesan..."
                            :disabled="isSending"
                            class="flex-1 rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        />
                        <button
                            type="submit"
                            :disabled="!canSend"
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Loader2 v-if="isSending" :size="18" class="animate-spin" />
                            <Send v-else :size="18" />
                        </button>
                    </form>
                </div>
            </template>
        </div>
    </Transition>
</template>

<style scoped>
.prose li {
    list-style-type: disc;
    margin-left: 1rem;
}

.prose strong {
    font-weight: 600;
}
</style>
