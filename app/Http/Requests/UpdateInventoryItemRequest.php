<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryItemRequest extends FormRequest
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
            if ($this->has($old) && ! $this->has($new)) {
                $data[$new] = $this->input($old);
            }
        }

        // For manual entry, default target_quantity to 0 if not provided
        if (empty($this->input('production_order_id')) && ! $this->has('target_quantity')) {
            $data['target_quantity'] = 0;
        }

        if (! empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $item = $this->route('item');
        $itemId = is_string($item) ? $item : $item->id;
        $isManualEntry = empty($this->input('production_order_id'));

        return [
            // Source type for tracking
            'source_type' => 'nullable|in:production,opening_balance,purchase,return',

            // Production order is now optional
            'production_order_id' => 'nullable|exists:production_orders,id',

            'sku' => 'nullable|string|max:100|unique:inventory_items,sku,'.$itemId.',id,tenant_id,'.auth()->user()->tenant_id,

            // Product name required for manual entry
            'product_name' => $isManualEntry ? 'required|string|max:255' : 'nullable|string|max:255',
            'name' => 'sometimes|string|max:255', // backwards compatibility

            'location_id' => 'required|exists:inventory_locations,id',
            'inventory_location_id' => 'sometimes|exists:inventory_locations,id', // backwards compatibility

            'target_quantity' => $isManualEntry ? 'nullable|integer|min:0' : 'required|integer|min:0',
            'current_quantity' => 'required|integer|min:0',
            'current_stock' => 'sometimes|integer|min:0', // backwards compatibility
            'stock_quantity' => 'sometimes|integer|min:0', // backwards compatibility
            'minimum_stock' => 'integer|min:0',

            // Category
            'category_id' => 'nullable|exists:inventory_item_categories,id',

            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',

            'quality_grade' => 'nullable|in:grade_a,grade_b,reject,A,B,Reject',
            'status' => 'nullable|in:available,reserved,damaged,expired',
            'notes' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'production_order_id.exists' => 'Production order tidak ditemukan.',
            'product_name.required' => 'Nama produk harus diisi untuk manual entry.',
            'sku.unique' => 'SKU sudah digunakan.',
            'inventory_location_id.exists' => 'Lokasi inventory tidak ditemukan.',
            'location_id.required' => 'Lokasi harus dipilih.',
        ];
    }
}
