<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locale yang dipilih user (lewat LanguageController) disimpan di session
     * dan dipakai di sini untuk seluruh request berikutnya — satu tempat
     * terpusat, bukan dicek berulang di tiap controller/Blade. Fallback ke
     * config('app.locale') ("id") kalau belum pernah memilih atau nilainya
     * bukan locale yang didukung.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
