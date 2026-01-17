<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductionOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'labor_cost' => $this->labor_cost ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'preparation_order_id' => ['required', 'exists:preparation_orders,id'],
            'type' => ['required', 'string', Rule::in(['internal', 'external'])],
            'contractor_id' => ['nullable', 'exists:contractors,id', 'required_if:type,external'],
            'quantity_requested' => ['required', 'integer', 'min:1'],
            'quantity_produced' => ['nullable', 'integer', 'min:0'],
            'quantity_good' => ['nullable', 'integer', 'min:0'],
            'quantity_reject' => ['nullable', 'integer', 'min:0'],
            'estimated_completion_date' => ['nullable', 'date', 'after_or_equal:today'],
            'sent_date' => ['nullable', 'date'],
            'completed_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'pending', 'sent', 'in_progress', 'completed', 'cancelled'])],
            'priority' => ['required', 'string', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'completion_notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'preparation_order_id.required' => 'Preparation order wajib dipilih.',
            'preparation_order_id.exists' => 'Preparation order tidak valid.',
            'type.required' => 'Tipe produksi wajib dipilih.',
            'type.in' => 'Tipe produksi tidak valid.',
            'contractor_id.required_if' => 'Kontraktor wajib dipilih untuk produksi eksternal.',
            'contractor_id.exists' => 'Kontraktor tidak valid.',
            'estimated_completion_date.after_or_equal' => 'Tanggal estimasi selesai harus hari ini atau setelahnya.',
            'status.in' => 'Status tidak valid.',
            'priority.required' => 'Prioritas wajib dipilih.',
            'priority.in' => 'Prioritas tidak valid.',
        ];
    }
}
