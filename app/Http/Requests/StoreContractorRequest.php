<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(['individual', 'company'])],
            'specialty' => ['required', 'string', Rule::in(['sewing', 'baking', 'crafting', 'other'])],
            'rate_per_piece' => ['nullable', 'numeric', 'min:0'],
            'rate_per_hour' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kontraktor wajib diisi.',
            'type.required' => 'Tipe kontraktor wajib dipilih.',
            'type.in' => 'Tipe kontraktor tidak valid.',
            'specialty.required' => 'Spesialisasi kontraktor wajib dipilih.',
            'specialty.in' => 'Spesialisasi kontraktor tidak valid.',
            'rate_per_piece.numeric' => 'Tarif per piece harus berupa angka.',
            'rate_per_piece.min' => 'Tarif per piece tidak boleh negatif.',
            'rate_per_hour.numeric' => 'Tarif per jam harus berupa angka.',
            'rate_per_hour.min' => 'Tarif per jam tidak boleh negatif.',
        ];
    }
}
