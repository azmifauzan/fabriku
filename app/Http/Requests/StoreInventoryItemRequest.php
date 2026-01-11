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
            'sku' => 'required|string|max:100|unique:inventory_items,sku,NULL,id,tenant_id,'.auth()->user()->tenant_id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|in:garment,food,craft,other',
            'pattern_id' => 'required|exists:patterns,id',
            'production_batch_id' => 'nullable|exists:production_batches,id',
            'inventory_location_id' => 'required|exists:inventory_locations,id',
            'current_stock' => 'required|integer|min:0',
            'reserved_stock' => 'integer|min:0',
            'minimum_stock' => 'integer|min:0',
            'maximum_stock' => 'nullable|integer|min:0|gte:minimum_stock',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'weight_per_unit' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:50',
            'production_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:production_date',
            'batch_number' => 'nullable|string|max:100',
            'quality_grade' => 'nullable|in:A,B,C',
            'status' => 'required|in:available,reserved,damaged,expired',
            'storage_requirements' => 'nullable|in:frozen,chilled,room_temp,dry_place,climate_controlled',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'SKU sudah digunakan.',
            'pattern_id.exists' => 'Pattern tidak ditemukan.',
            'production_batch_id.exists' => 'Production batch tidak ditemukan.',
            'inventory_location_id.exists' => 'Lokasi inventory tidak ditemukan.',
            'maximum_stock.gte' => 'Maksimal stock harus lebih besar atau sama dengan minimal stock.',
            'expiry_date.after' => 'Tanggal expired harus setelah tanggal produksi.',
        ];
    }
}
