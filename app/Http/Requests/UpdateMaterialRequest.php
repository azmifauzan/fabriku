<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $materialId = $this->route('material');

        return [
            'material_type_id' => ['required', 'exists:material_types,id'],
            'code' => ['required', 'string', 'max:50', Rule::unique('materials')->where('tenant_id', $tenantId)->ignore($materialId)],
            'name' => ['required', 'string', 'max:255'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'price_per_unit' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'description' => ['nullable', 'string'],
        ];
    }
}
