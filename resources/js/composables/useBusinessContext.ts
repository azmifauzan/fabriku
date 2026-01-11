import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

type TerminologyKey =
  | 'material'
  | 'pattern'
  | 'preparation'
  | 'preparation_order'
  | 'production'
  | 'production_order'
  | 'contractor'

type TenantProps = {
  business_category?: string | null
  category_label?: string | null
  terminology?: Partial<Record<TerminologyKey, string>>
  category_config?: {
    product_types?: Record<string, string>
    sizes?: string[]
  } | null
} | null

export function useBusinessContext() {
  const page = usePage<{ tenant?: TenantProps }>()

  const tenant = computed(() => page.props.tenant ?? null)

  const terminology = computed<Partial<Record<TerminologyKey, string>>>(() => {
    return tenant.value?.terminology ?? {}
  })

  const categoryConfig = computed(() => {
    return tenant.value?.category_config ?? null
  })

  const productTypes = computed<Record<string, string>>(() => {
    return categoryConfig.value?.product_types ?? {}
  })

  const sizes = computed<string[]>(() => {
    return categoryConfig.value?.sizes ?? []
  })

  const term = (key: TerminologyKey, fallback: string): string => {
    return terminology.value[key] || fallback
  }

  const termLower = (key: TerminologyKey, fallback: string): string => {
    return term(key, fallback).toLowerCase()
  }

  return {
    tenant,
    terminology,
    categoryConfig,
    productTypes,
    sizes,
    term,
    termLower,
  }
}
