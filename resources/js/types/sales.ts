export interface SalesCustomer {
    id: number;
    code: string;
    name: string;
}

export interface SalesProduct {
    id: number;
    sku: string;
    product_name?: string | null;
    current_stock: number;
    reserved_stock: number;
    selling_price?: number | string | null;
    image_url?: string | null;
    pattern?: { name?: string | null } | null;
}

export interface SalesOrderItem {
    inventory_item_id: number | null;
    quantity: number | string;
    unit_price: number | string;
    discount_amount: number | string | null;
    subtotal: number | string;
}

export interface SalesOrderForEdit {
    id: number;
    order_number: string;
    customer_id: number | null;
    order_date: string;
    channel: string;
    invoice_number: string | null;
    resi_number: string | null;
    status: string;
    payment_method: string | null;
    payment_status: string;
    paid_amount: number | string;
    payment_due_date: string | null;
    shipping_cost: number | string;
    discount_percentage: number | string;
    tax_amount: number | string;
    shipping_address: string | null;
    notes: string | null;
    items: SalesOrderItem[];
}
