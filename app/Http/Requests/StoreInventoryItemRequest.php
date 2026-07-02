<?php

namespace App\Http\Requests;

use App\Models\InventoryLocation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInventoryItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Prepare data for validation - map field names
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name') && ! $this->has('product_name')) {
            $this->merge(['product_name' => $this->input('name')]);
        }

        // Map field names for backwards compatibility
        $locationId = $this->input('location_id') ?? $this->input('inventory_location_id');
        $quantity = $this->input('current_quantity') ?? $this->input('current_stock') ?? $this->input('stock_quantity');

        if ($locationId && $quantity !== null) {
            $this->merge([
                'locations' => [
                    [
                        'location_id' => (int) $locationId,
                        'quantity' => (int) $quantity,
                    ]
                ]
            ]);
        }

        // For manual entry, default target_quantity to 0 if not provided
        if (empty($this->input('production_order_id')) && ! $this->has('target_quantity')) {
            $this->merge(['target_quantity' => 0]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isManualEntry = empty($this->input('production_order_id'));
        $tenantId = $this->user()->tenant_id;

        return [
            // Source type for tracking
            'source_type' => 'nullable|in:production,opening_balance,purchase,return',

            // Production order is now optional (nullable for manual entry / opening balance)
            'production_order_id' => ['nullable', Rule::exists('production_orders', 'id')->where('tenant_id', $tenantId)],

            'sku' => 'nullable|string|max:100|unique:inventory_items,sku,NULL,id,tenant_id,'.$tenantId,

            // Product name required for manual entry
            'product_name' => $isManualEntry ? 'required|string|max:255' : 'nullable|string|max:255',
            'name' => 'sometimes|string|max:255', // backwards compatibility

            // One or more rack allocations for this batch
            'locations' => 'required|array|min:1',
            'locations.*.location_id' => ['required', 'distinct', Rule::exists('inventory_locations', 'id')->where('tenant_id', $tenantId)],
            'locations.*.quantity' => 'required|integer|min:1',

            // Quantities - required for manual entry
            'target_quantity' => $isManualEntry ? 'nullable|integer|min:0' : 'required|integer|min:0',
            'minimum_stock' => 'integer|min:0',

            // Category
            'category_id' => ['nullable', Rule::exists('inventory_item_categories', 'id')->where('tenant_id', $tenantId)],

            // Pricing - required for manual entry
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',

            'quality_grade' => 'nullable|in:grade_a,grade_b,reject,A,B,Reject',
            'expired_date' => ['nullable', 'date'],
            'status' => 'nullable|in:available,reserved,damaged,expired',
            'notes' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * Block any rack allocation that would overflow that rack's remaining capacity.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $locations = $this->input('locations', []);

            if (! is_array($locations)) {
                return;
            }

            foreach ($locations as $index => $entry) {
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
                        "locations.{$index}.quantity",
                        "Rak {$location->name} tidak cukup kapasitas (sisa: {$location->available_capacity})."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'production_order_id.exists' => 'Production order tidak ditemukan.',
            'product_name.required' => 'Nama produk harus diisi untuk manual entry.',
            'sku.unique' => 'SKU sudah digunakan.',
            'locations.required' => 'Minimal 1 lokasi harus diisi.',
            'locations.*.location_id.required' => 'Lokasi harus dipilih.',
            'locations.*.location_id.distinct' => 'Lokasi tidak boleh dipilih dua kali dalam satu form.',
            'locations.*.location_id.exists' => 'Lokasi inventory tidak ditemukan.',
            'locations.*.quantity.required' => 'Jumlah stock harus diisi.',
            'locations.*.quantity.min' => 'Jumlah stock minimal 1.',
            'target_quantity.required' => 'Jumlah target harus diisi.',
            'unit_cost.required' => 'Harga modal harus diisi.',
        ];
    }
}
