<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Cuma "name" — perubahan email sekarang punya alur verifikasi
     * tersendiri lewat ProfileEmailController (lihat routes/web.php:
     * profile.email.update/profile.email.verify), bukan langsung tersimpan
     * lewat form ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
