<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryLocationRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:inventory_locations,name,NULL,id,tenant_id,'.auth()->user()->tenant_id,
            'zone' => 'required|string|max:10',
            'rack' => 'required|string|max:20',
            'description' => 'nullable|string|max:1000',
            'capacity' => 'nullable|integer|min:1',
            'temperature_min' => 'nullable|integer',
            'temperature_max' => 'nullable|integer|gte:temperature_min',
            'status' => 'required|in:active,inactive,maintenance',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama lokasi sudah digunakan.',
            'zone.required' => 'Zone harus diisi.',
            'rack.required' => 'Rack harus diisi.',
            'status.in' => 'Status tidak valid.',
            'temperature_max.gte' => 'Suhu maksimal harus lebih besar atau sama dengan suhu minimal.',
        ];
    }
}
