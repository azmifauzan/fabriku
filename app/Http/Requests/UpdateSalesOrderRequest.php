<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSalesOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)],
            'order_date' => ['required', 'date'],
            'channel' => ['required', 'in:offline,online,reseller,marketplace'],
            'status' => ['nullable', 'in:draft,confirmed,processing,shipped,completed,cancelled'],
            'payment_method' => ['required', 'in:cash,transfer,credit_card,qris,cod'],
            'payment_status' => ['nullable', 'in:unpaid,partial,paid'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_due_date' => ['nullable', 'date'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'shipping_address' => ['nullable', 'string'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'resi_number' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['nullable', 'required_without:items.*.service_id', Rule::exists('inventory_items', 'id')->where('tenant_id', $tenantId)],
            'items.*.service_id' => ['nullable', 'required_without:items.*.inventory_item_id', Rule::exists('services', 'id')->where('tenant_id', $tenantId)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('items', []) as $index => $item) {
                if (! empty($item['inventory_item_id']) && ! empty($item['service_id'])) {
                    $validator->errors()->add(
                        "items.{$index}.service_id",
                        'Satu baris hanya boleh berisi produk atau layanan, tidak keduanya.'
                    );
                }
            }
        });
    }
}
