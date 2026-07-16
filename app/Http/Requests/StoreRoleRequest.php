<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where(fn ($query) => $query->where('module', '!=', 'tenant')),
            ],
        ];
    }
}
