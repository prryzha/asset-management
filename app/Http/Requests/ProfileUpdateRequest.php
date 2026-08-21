<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Input kosong dari text field dikirim sebagai string "" (bukan null),
     * padahal "nullable" di rules() cuma melewatkan validasi lanjutan kalau
     * nilainya benar-benar null — tanpa ini, menyimpan form dengan username
     * dikosongkan akan gagal validasi "min:3" setiap kali.
     */
    protected function prepareForValidation(): void
    {
        if ($this->username === '') {
            $this->merge(['username' => null]);
        }
    }

    /**
     * "name" + "username" + "foto_profil" — perubahan email sekarang punya
     * alur verifikasi tersendiri lewat ProfileEmailController (lihat
     * routes/web.php: profile.email.update/profile.email.verify), bukan
     * langsung tersimpan lewat form ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Aturan username diambil dari User::usernameRules() — satu
            // sumber kebenaran yang sama dipakai UserController (Manajemen
            // User) supaya kedua form ini tidak pernah punya aturan
            // username yang beda sendiri-sendiri.
            'username' => User::usernameRules($this->user()->id),
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
