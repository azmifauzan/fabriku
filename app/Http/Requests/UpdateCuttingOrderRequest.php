<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCuttingOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pattern_id' => 'required|exists:patterns,id',
            'order_date' => 'required|date',
            'target_date' => 'nullable|date|after_or_equal:order_date',
            'target_quantity' => 'required|integer|min:1',
            'cutter_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ];
    }
}
