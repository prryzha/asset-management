<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InstitutionProfileUpdateRequest extends FormRequest
{
    /**
     * Otorisasi sudah ditangani middleware route `role:admin`
     * (routes/web.php), bukan di sini — sama seperti ProfileUpdateRequest.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_instansi' => ['required', 'string', 'max:255'],
            'nama_singkat' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
