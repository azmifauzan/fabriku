export interface Customer {
    id?: number;
    code: string;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    province: string | null;
    is_active: boolean;
    notes: string | null;
}
