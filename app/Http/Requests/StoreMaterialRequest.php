<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'material_type_id' => ['required', Rule::exists('material_types', 'id')->where('tenant_id', $tenantId)],
            'code' => ['required', 'string', 'max:50', Rule::unique('materials')->where('tenant_id', $tenantId)],
            'name' => ['required', 'string', 'max:255'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'price_per_unit' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'image_path' => ['exclude'], // Prevent image_path from being sent
            'description' => ['nullable', 'string'],
        ];
    }
}
