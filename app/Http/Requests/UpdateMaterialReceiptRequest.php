<?php

namespace App\Http\Requests;

use App\Models\MaterialReceipt;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var MaterialReceipt $receipt */
        $receipt = $this->route('material_receipt');
        $usedQuantity = $receipt->quantity - $receipt->remaining_quantity;

        return [
            'supplier_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:'.$usedQuantity],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'receipt_date' => ['required', 'date'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        /** @var MaterialReceipt $receipt */
        $receipt = $this->route('material_receipt');
        $usedQuantity = $receipt->quantity - $receipt->remaining_quantity;

        return [
            'supplier_name.required' => 'Nama supplier wajib diisi.',
            'quantity.required' => 'Jumlah qty wajib diisi.',
            'quantity.numeric' => 'Jumlah qty harus berupa angka.',
            'quantity.min' => "Jumlah qty tidak boleh kurang dari {$usedQuantity} (sudah terpakai).",
            'price_per_unit.required' => 'Harga satuan wajib diisi.',
            'price_per_unit.numeric' => 'Harga satuan harus berupa angka.',
            'price_per_unit.min' => 'Harga satuan tidak boleh negatif.',
            'receipt_date.required' => 'Tanggal penerimaan wajib diisi.',
            'receipt_date.date' => 'Format tanggal tidak valid.',
            'image.image' => 'File harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 5MB.',
        ];
    }
}
