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
            'code' => ['required', 'string', 'max:50', Rule::unique('materials')->where('tenant_id', $tenantId)->ignore($materialId)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:20'],
            'standard_price' => ['nullable', 'numeric', 'min:0'],
            'reorder_point' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
