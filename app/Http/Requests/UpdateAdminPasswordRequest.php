<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateAdminPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(12)],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Password saat ini salah.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
            'password.min' => 'Password baru minimal 12 karakter.',
        ];
    }
}
