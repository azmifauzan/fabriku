<?php

namespace App\Http\Requests;

use App\Models\InventoryLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransferInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tenant_id === $this->route('item')->tenant_id;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'splits' => 'required|array|min:1',
            'splits.*.location_id' => [
                'required', 'distinct',
                Rule::exists('inventory_locations', 'id')->where('tenant_id', $tenantId),
                Rule::notIn([$this->route('item')->location_id]),
            ],
            'splits.*.quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $splits = $this->input('splits', []);
            $item = $this->route('item');

            if (! is_array($splits) || ! $item) {
                return;
            }

            $totalQuantity = array_sum(array_column($splits, 'quantity'));

            if ($totalQuantity > $item->available_stock) {
                $validator->errors()->add(
                    'splits',
                    "Total yang dipindah ({$totalQuantity}) melebihi stock tersedia ({$item->available_stock})."
                );
            }

            foreach ($splits as $index => $entry) {
                $locationId = $entry['location_id'] ?? null;
                $quantity = (int) ($entry['quantity'] ?? 0);

                if (! $locationId || $quantity < 1) {
                    continue;
                }

                $location = InventoryLocation::find($locationId);

                if (! $location || $location->capacity === null) {
                    continue;
                }

                if ($quantity > $location->available_capacity) {
                    $validator->errors()->add(
                        "splits.{$index}.quantity",
                        "Rak {$location->name} tidak cukup kapasitas (sisa: {$location->available_capacity})."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'splits.required' => 'Minimal 1 lokasi tujuan harus diisi.',
            'splits.*.location_id.required' => 'Lokasi tujuan harus dipilih.',
            'splits.*.location_id.distinct' => 'Lokasi tujuan tidak boleh dipilih dua kali.',
            'splits.*.location_id.exists' => 'Lokasi tujuan tidak ditemukan.',
            'splits.*.location_id.not_in' => 'Lokasi tujuan tidak boleh sama dengan lokasi asal.',
            'splits.*.quantity.required' => 'Jumlah yang dipindah harus diisi.',
            'splits.*.quantity.min' => 'Jumlah yang dipindah minimal 1.',
            'reason.required' => 'Alasan pemindahan harus diisi.',
        ];
    }
}
