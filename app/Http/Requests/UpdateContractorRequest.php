<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contractorId = $this->route('contractor')->id;

        return [
            'code' => 'sometimes|string|max:50|unique:contractors,code,'.$contractorId.',id,tenant_id,'.auth()->user()->tenant_id,
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(['individual', 'company'])],
            'specialty' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Kode kontraktor sudah digunakan.',
            'name.required' => 'Nama kontraktor wajib diisi.',
            'type.required' => 'Tipe kontraktor wajib dipilih.',
            'type.in' => 'Tipe kontraktor tidak valid.',
            'specialty.max' => 'Spesialisasi maksimal 500 karakter.',
        ];
    }
}
