<?php

namespace App\Http\Requests\Assistant;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:1', 'max:2000'],
            'channel' => ['sometimes', 'string', 'in:web,telegram,whatsapp'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.min' => 'Pesan terlalu pendek.',
            'message.max' => 'Pesan terlalu panjang (maksimal 2000 karakter).',
            'channel.in' => 'Channel tidak valid.',
        ];
    }
}
