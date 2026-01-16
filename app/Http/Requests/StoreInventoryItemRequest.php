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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'production_order_id' => 'required|exists:production_orders,id',
            'sku' => 'required|string|max:100|unique:inventory_items,sku,NULL,id,tenant_id,'.auth()->user()->tenant_id,
            'name' => 'required|string|max:255',
            'inventory_location_id' => 'required|exists:inventory_locations,id',
            'target_quantity' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'production_date' => 'nullable|date',
            'quality_grade' => 'nullable|in:grade_a,grade_b,reject',
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
