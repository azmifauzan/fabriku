<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductionOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cutting_result_id' => ['required', 'exists:cutting_results,id'],
            'type' => ['required', 'string', Rule::in(['internal', 'external'])],
            'contractor_id' => ['nullable', 'exists:contractors,id', 'required_if:type,external'],
            'quantity_requested' => ['required', 'integer', 'min:1'],
            'requested_date' => ['required', 'date'],
            'promised_date' => ['nullable', 'date', 'after_or_equal:requested_date'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['required', 'string', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'cutting_result_id.required' => 'Hasil cutting wajib dipilih.',
            'cutting_result_id.exists' => 'Hasil cutting tidak valid.',
            'type.required' => 'Tipe produksi wajib dipilih.',
            'type.in' => 'Tipe produksi tidak valid.',
            'contractor_id.required_if' => 'Kontraktor wajib dipilih untuk produksi eksternal.',
            'contractor_id.exists' => 'Kontraktor tidak valid.',
            'quantity_requested.required' => 'Jumlah diminta wajib diisi.',
            'quantity_requested.min' => 'Jumlah diminta minimal 1.',
            'requested_date.required' => 'Tanggal diminta wajib diisi.',
            'promised_date.after_or_equal' => 'Tanggal janji harus sama atau setelah tanggal diminta.',
            'priority.required' => 'Prioritas wajib dipilih.',
            'priority.in' => 'Prioritas tidak valid.',
        ];
    }
}
