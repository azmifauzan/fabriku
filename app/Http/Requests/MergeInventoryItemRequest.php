<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MergeInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tenant_id === $this->route('item')->tenant_id;
    }

    public function rules(): array
    {
        return [
            'destination_item_id' => [
                'required',
                'integer',
                Rule::exists('inventory_items', 'id')->where('tenant_id', $this->user()->tenant_id)->whereNull('deleted_at'),
                Rule::notIn([$this->route('item')->id]),
            ],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_item_id.required' => 'Item tujuan harus dipilih.',
            'destination_item_id.exists' => 'Item tujuan tidak ditemukan.',
            'destination_item_id.not_in' => 'Item sumber dan tujuan harus berbeda.',
            'reason.required' => 'Alasan penggabungan harus diisi.',
        ];
    }
}
