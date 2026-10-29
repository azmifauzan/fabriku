export interface NamedOption {
    id: number;
    name: string;
}

export interface DashboardTenant extends NamedOption {
    business_category: string;
    subscription_plan: string;
    is_active: boolean;
    created_at: string;
}

export interface RoleSummary extends NamedOption {
    slug: string;
    description: string | null;
    is_system_role: boolean;
    permissions_count: number;
    users_count: number;
}

export interface Permission extends NamedOption {
    module: string | null;
}

export interface SubscriptionPayment {
    id: number;
    amount: number | string;
    status: string;
    plan_type: string;
    duration_months: number;
    created_at: string;
    tenant: NamedOption;
    admin?: NamedOption | null;
    proof_url: string;
}
