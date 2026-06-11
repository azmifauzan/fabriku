<?php

namespace App\Http\Requests;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSalesOrderRequest extends FormRequest
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
        return [
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('tenant_id', $this->user()->tenant_id)],
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
            'items.*.inventory_item_id' => ['nullable', 'required_without:items.*.service_id', Rule::exists('inventory_items', 'id')->where('tenant_id', $this->user()->tenant_id)],
            'items.*.service_id' => ['nullable', 'required_without:items.*.inventory_item_id', Rule::exists('services', 'id')->where('tenant_id', $this->user()->tenant_id)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                if (! empty($item['inventory_item_id']) && ! empty($item['service_id'])) {
                    $validator->errors()->add(
                        "items.{$index}.service_id",
                        'Satu baris hanya boleh berisi produk atau layanan, tidak keduanya.'
                    );
                }

                if (empty($item['inventory_item_id']) || empty($item['quantity'])) {
                    continue;
                }

                $inventoryItem = InventoryItem::find($item['inventory_item_id']);

                if (! $inventoryItem) {
                    continue;
                }

                $availableStock = $inventoryItem->available_stock;

                if ((int) $item['quantity'] > $availableStock) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "Stok tersedia untuk {$inventoryItem->product_name} hanya {$availableStock}. Jumlah pesanan melebihi stok tersedia."
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->status ?? 'draft',
        ]);
    }
}
