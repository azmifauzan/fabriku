<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = auth()->user()->tenant_id;

        return [
            'code' => ['required', 'string', 'max:50', "unique:customers,code,NULL,id,tenant_id,{$tenantId}"],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:retail,reseller,online'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_term' => ['required', 'in:cash,credit_7,credit_14,credit_30'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->is_active ?? true,
        ]);
    }
}
