<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePreparationOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pattern_id' => 'nullable|exists:patterns,id',
            'order_date' => 'required|date',
            'prepared_by' => 'nullable|exists:users,id',
            'output_quantity' => 'required|numeric|min:0.01',
            'output_unit' => 'required|string|max:20',
            'materials_used' => 'required|array|min:1',
            'materials_used.*.material_id' => 'required|exists:materials,id',
            'materials_used.*.material_name' => 'required|string',
            'materials_used.*.quantity' => 'required|numeric|min:0.01',
            'materials_used.*.unit' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,in_progress,completed,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'materials_used.required' => 'Minimal harus ada 1 material yang digunakan',
            'materials_used.*.material_id.required' => 'Material harus dipilih',
            'materials_used.*.quantity.required' => 'Quantity material harus diisi',
            'output_quantity.required' => 'Jumlah hasil produksi harus diisi',
        ];
    }
}
