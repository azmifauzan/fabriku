<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data for validation - map field names
     */
    protected function prepareForValidation(): void
    {
        // Map field names for backwards compatibility
        $mappings = [
            'name' => 'product_name',
            'inventory_location_id' => 'location_id',
            'current_stock' => 'current_quantity',
            'stock_quantity' => 'current_quantity',
        ];

        $data = [];
        foreach ($mappings as $old => $new) {
            if ($this->has($old) && !$this->has($new)) {
                $data[$new] = $this->input($old);
            }
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'production_order_id' => 'required|exists:production_orders,id',
            'sku' => 'required|string|max:100|unique:inventory_items,sku,NULL,id,tenant_id,'.auth()->user()->tenant_id,
            'product_name' => 'required|string|max:255',
            'name' => 'sometimes|string|max:255', // backwards compatibility
            'location_id' => 'required|exists:inventory_locations,id',
            'inventory_location_id' => 'sometimes|exists:inventory_locations,id', // backwards compatibility
            'target_quantity' => 'required|integer|min:0',
            'current_quantity' => 'required|integer|min:0',
            'current_stock' => 'sometimes|integer|min:0', // backwards compatibility
            'stock_quantity' => 'sometimes|integer|min:0', // backwards compatibility
            'minimum_stock' => 'integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'production_date' => 'nullable|date',
            'quality_grade' => 'nullable|in:grade_a,grade_b,reject,A,B,Reject',
            'status' => 'required|in:available,reserved,damaged,expired',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'production_order_id.required' => 'Production order harus dipilih.',
            'production_order_id.exists' => 'Production order tidak ditemukan.',
            'sku.unique' => 'SKU sudah digunakan.',
            'inventory_location_id.exists' => 'Lokasi inventory tidak ditemukan.',
        ];
    }
}
