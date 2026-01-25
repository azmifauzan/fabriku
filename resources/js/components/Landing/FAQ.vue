<script setup>
import { ref } from 'vue'

const openIndex = ref(null)

const faqs = [
  {
    question: 'Apa itu Fabriku?',
    answer: 'Fabriku adalah platform manajemen produksi terpadu untuk UMKM konveksi dan manufaktur. Kami membantu Anda mengelola inventory, pesanan, produksi, hingga laporan keuangan dalam satu aplikasi.'
  },
  {
    question: 'Berapa biaya berlangganan Fabriku?',
    answer: 'Kami menawarkan Free Trial selama 30 hari dengan akses penuh. Setelah itu, Anda bisa berlangganan paket Full Member seharga Rp 25.000/bulan atau Rp 250.000/tahun (hemat 2 bulan).'
  },
  {
    question: 'Apakah data saya aman?',
    answer: 'Sangat aman. Kami menggunakan enkripsi standar industri dan server yang andal untuk memastikan data bisnis Anda terlindungi dan dapat diakses kapan saja.'
  },
  {
    question: 'Bisakah saya upgrade atau membatalkan kapan saja?',
    answer: 'Tentu! Anda bisa memperpanjang langganan kapan saja. Jika masa aktif berakhir, akun Anda akan masuk ke mode read-only (hanya bisa melihat data) sampai Anda melakukan perpanjangan.'
  },
  {
    question: 'Metode pembayaran apa yang tersedia?',
    answer: 'Saat ini kami menerima pembayaran melalui transfer bank (BCA, Mandiri, dll). Konfirmasi pembayaran dilakukan secara manual oleh tim admin kami maksimum 1x24 jam.'
  }
]

const toggle = (index) => {
  openIndex.value = openIndex.value === index ? null : index
}
</script>

<template>
  <div class="py-24 bg-gray-50" id="faq">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-base font-semibold text-indigo-600 tracking-wide uppercase">FAQ</h2>
        <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
          Pertanyaan yang Sering Diajukan
        </p>
        <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
          Temukan jawaban untuk pertanyaan umum seputar layanan Fabriku.
        </p>
      </div>

      <div class="max-w-3xl mx-auto divide-y-2 divide-gray-200">
        <dl class="space-y-6 divide-y divide-gray-200">
          <div v-for="(faq, index) in faqs" :key="index" class="pt-6">
            <dt class="text-lg">
              <button 
                @click="toggle(index)"
                class="text-left w-full flex justify-between items-start text-gray-400 focus:outline-none"
              >
                <span class="font-medium text-gray-900">
                  {{ faq.question }}
                </span>
                <span class="ml-6 h-7 flex items-center">
                    <svg 
                        class="h-6 w-6 transform transition-transform duration-200" 
                        :class="{ '-rotate-180': openIndex === index, 'rotate-0': openIndex !== index }"
                        xmlns="http://www.w3.org/2000/svg" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor" 
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
              </button>
            </dt>
            <transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <dd v-if="openIndex === index" class="mt-2 pr-12">
                <p class="text-base text-gray-500">
                    {{ faq.answer }}
                </p>
                </dd>
            </transition>
          </div>
        </dl>
      </div>
    </div>
  </div>
</template>
