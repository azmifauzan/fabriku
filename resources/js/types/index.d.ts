export interface Auth {
    user: (Pick<User, 'id' | 'name' | 'email' | 'role'> & { permissions: string[] }) | null;
    admin: { id: number; name: string; email: string; role: string } | null;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: Auth;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    role: string;
    tenant_id: number;
    created_at: string;
    updated_at: string;
}
