<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    /**
     * Ganti locale aktif lalu kembali ke halaman sebelumnya — back() memakai
     * header Referer sehingga query string/filter di halaman asal tetap
     * terbawa tanpa perlu ditangani manual. Locale invalid diam-diam
     * diabaikan (redirect tetap terjadi, session tidak berubah) supaya URL
     * ini tidak pernah menyebabkan error, hanya no-op.
     */
    public function switch(string $locale): RedirectResponse
    {
        if (in_array($locale, ['id', 'en'], true)) {
            session(['locale' => $locale]);
        }

        return back();
    }
}
