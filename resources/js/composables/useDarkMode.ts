import { ref, watch, onMounted } from 'vue'

export function useDarkMode() {
  const STORAGE_KEY = 'fabriku-theme'
  const isDark = ref(false)

  const applyTheme = (dark: boolean) => {
    document.documentElement.classList.toggle('dark', dark)

    try {
      localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light')
    } catch (e) {
      // ignore
    }
  }

  // Initialize theme
  const initTheme = () => {
    try {
      const savedTheme = localStorage.getItem(STORAGE_KEY)
      isDark.value = savedTheme === 'dark'
    } catch (e) {
      isDark.value = false
    }

    applyTheme(isDark.value)
  }

  // Toggle function
  const toggleDark = () => {
    isDark.value = !isDark.value
  }

  // Watch for changes
  watch(isDark, (newValue) => {
    applyTheme(newValue)
  })

  // Initialize once
  if (typeof window !== 'undefined') {
    initTheme()
  } else {
    onMounted(() => {
      initTheme()
    })
  }

  return {
    isDark,
    toggleDark,
  }
}
