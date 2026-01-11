<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatternRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('patterns', 'code')->where('tenant_id', auth()->user()->tenant_id),
            ],
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:mukena,daster,gamis,jilbab,lainnya',
            'size' => 'nullable|in:XS,S,M,L,XL,XXL,XXXL,all_size',
            'description' => 'nullable|string',
            'estimated_time' => 'nullable|numeric|min:0',
            'standard_waste_percentage' => 'nullable|numeric|min:0|max:100',
            'image_url' => 'nullable|url',
            'is_active' => 'boolean',
            'materials' => 'nullable|array',
            'materials.*.material_id' => 'required|exists:materials,id',
            'materials.*.quantity_needed' => 'required|numeric|min:0',
            'materials.*.notes' => 'nullable|string',
        ];
    }
}
