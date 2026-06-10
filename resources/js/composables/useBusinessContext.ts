import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type TerminologyKey = 'material' | 'pattern' | 'preparation' | 'preparation_order' | 'production' | 'production_order' | 'contractor' | 'service';

type CategoryRules = {
    enable_production_flow?: boolean;
    enable_material_module?: boolean;
    enable_preparation_module?: boolean;
    enable_pattern_module?: boolean;
    enable_contractor_module?: boolean;
    enable_simple_production?: boolean;
    enable_inventory_module?: boolean;
    enable_sales_module?: boolean;
    enable_purchase_module?: boolean;
    track_batch_number?: boolean;
    track_expired_date?: boolean;
    [key: string]: boolean | number | undefined;
};

type TenantProps = {
    business_category?: string | null;
    category_label?: string | null;
    terminology?: Partial<Record<TerminologyKey, string>>;
    category_config?: {
        product_types?: Record<string, string>;
        sizes?: string[];
        rules?: CategoryRules;
        mode?: string;
    } | null;
} | null;

export function useBusinessContext() {
    const page = usePage<{ tenant?: TenantProps }>();

    const tenant = computed(() => page.props.tenant ?? null);

    const terminology = computed<Partial<Record<TerminologyKey, string>>>(() => {
        return tenant.value?.terminology ?? {};
    });

    const categoryConfig = computed(() => {
        return tenant.value?.category_config ?? null;
    });

    const productTypes = computed<Record<string, string>>(() => {
        return categoryConfig.value?.product_types ?? {};
    });

    const sizes = computed<string[]>(() => {
        return categoryConfig.value?.sizes ?? [];
    });

    const rules = computed<CategoryRules>(() => {
        return categoryConfig.value?.rules ?? {};
    });

    const isRetailMode = computed<boolean>(() => {
        return (categoryConfig.value?.mode ?? 'full') === 'simple';
    });

    const isServiceMode = computed<boolean>(() => {
        return (categoryConfig.value?.mode ?? 'full') === 'service';
    });

    const isModuleEnabled = (moduleKey: string): boolean => {
        const rule = rules.value[`enable_${moduleKey}_module`];
        return rule !== false;
    };

    const term = (key: TerminologyKey, fallback: string): string => {
        return terminology.value[key] || fallback;
    };

    const termLower = (key: TerminologyKey, fallback: string): string => {
        return term(key, fallback).toLowerCase();
    };

    return {
        tenant,
        terminology,
        categoryConfig,
        productTypes,
        sizes,
        rules,
        isRetailMode,
        isServiceMode,
        isModuleEnabled,
        term,
        termLower,
    };
}
