<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryLocationRequest extends FormRequest
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
        $locationId = $this->route('location')->id;

        return [
            'code' => 'sometimes|string|max:50|unique:inventory_locations,code,'.$locationId.',id,tenant_id,'.auth()->user()->tenant_id,
            'name' => 'required|string|max:255|unique:inventory_locations,name,'.$locationId.',id,tenant_id,'.auth()->user()->tenant_id,
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lokasi harus diisi.',
            'name.unique' => 'Nama lokasi sudah digunakan.',
            'code.unique' => 'Kode lokasi sudah digunakan.',
            'capacity.integer' => 'Kapasitas harus berupa angka.',
            'capacity.min' => 'Kapasitas minimal 1.',
        ];
    }
}
