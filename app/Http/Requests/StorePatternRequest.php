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
        $tenant = auth()->user()->tenant;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('patterns', 'code')->where('tenant_id', $tenant->id),
            ],
            'name' => 'required|string|max:255',
            'category' => 'required|in:garment,food,craft,cosmetic,other',
            'size' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
        ];
    }
}
