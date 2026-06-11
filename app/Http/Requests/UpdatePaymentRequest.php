<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $salesOrder = $this->route('sales_order');

        return [
            'paid_amount' => ['required', 'numeric', 'min:0', 'max:'.$salesOrder->total_amount],
        ];
    }

    public function messages(): array
    {
        return [
            'paid_amount.max' => 'Jumlah dibayar tidak boleh melebihi total pesanan.',
        ];
    }
}
