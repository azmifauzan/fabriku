<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductionBatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quantity_received' => ['required', 'integer', 'min:1'],
            'quantity_good' => ['required', 'integer', 'min:0'],
            'quantity_defect' => ['nullable', 'integer', 'min:0'],
            'quantity_reject' => ['nullable', 'integer', 'min:0'],
            'grade' => ['required', 'string', Rule::in(['A', 'B', 'C', 'reject'])],
            'labor_cost_actual' => ['nullable', 'numeric', 'min:0'],
            'production_cost' => ['nullable', 'numeric', 'min:0'],
            'production_date' => ['required', 'date'],
            'received_date' => ['required', 'date', 'after_or_equal:production_date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:received_date'],
            'qc_notes' => ['nullable', 'string'],
            'defect_reasons' => ['nullable', 'string'],
            'qc_checklist' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $received = (int) $this->input('quantity_received', 0);
            $good = (int) $this->input('quantity_good', 0);
            $defect = (int) $this->input('quantity_defect', 0);
            $reject = (int) $this->input('quantity_reject', 0);

            if ($good > $received) {
                $validator->errors()->add('quantity_good', 'Jumlah good tidak boleh lebih besar dari jumlah diterima.');
            }

            if (($good + $defect + $reject) !== $received) {
                $validator->errors()->add('quantity_received', 'Jumlah diterima harus sama dengan good + defect + reject.');
            }
        });
    }
}
