<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\VerifyNewEmail;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

/**
 * Ganti email — stateless, TANPA kolom/tabel baru. State "email baru yang
 * masih menunggu verifikasi" hidup di dalam signed URL itu sendiri (payload
 * ter-enkripsi berisi user_id + email baru), bukan di database. Email aktif
 * tidak pernah tersentuh sampai link diklik & valid — lihat audit sebelumnya
 * (alternatif "pending_email column" butuh migration, ditolak).
 */
class ProfileEmailController extends Controller
{
    /**
     * Ajukan perubahan email — kirim link verifikasi ke alamat BARU.
     * Email aktif user TIDAK diubah di sini.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('changeEmail', [
            'new_email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($request->user()->id),
            ],
        ]);

        $user = $request->user();
        $newEmail = $validated['new_email'];

        if ($newEmail === $user->email) {
            return back()
                ->withErrors(['new_email' => 'Email baru harus berbeda dari email aktif saat ini.'], 'changeEmail')
                ->withInput();
        }

        // Payload dienkripsi (bukan cuma di-sign) supaya email baru tidak
        // tampak polos di URL/inbox — pertahanan berlapis, walau signed URL
        // sendiri sudah membuat URL ini tidak bisa dimanipulasi.
        $payload = Crypt::encryptString($user->id . '|' . $newEmail);

        // Parameter TIDAK dipetakan ke placeholder path (route tanpa
        // {payload}) — otomatis jadi query string oleh temporarySignedRoute,
        // supaya karakter hasil enkripsi (+, /, =) tidak merusak path URL.
        $verificationUrl = URL::temporarySignedRoute(
            'profile.email.verify',
            now()->addMinutes(60),
            ['payload' => $payload],
        );

        Notification::route('mail', $newEmail)->notify(new VerifyNewEmail($verificationUrl));

        return redirect()->route('profile.edit')->with('status', 'email-change-requested');
    }

    /**
     * Konfirmasi dari link yang dikirim ke email baru. Middleware `signed`
     * (lihat route) sudah menolak link yang expired/dimanipulasi SEBELUM
     * method ini jalan sama sekali.
     *
     * Link ini bisa dibuka TANPA sesi login sama sekali (device/browser
     * beda dari yang dipakai mengajukan perubahan) — lihat catatan routing.
     * Karena itu redirect-nya adaptif terhadap sesi yang sedang aktif,
     * bukan selalu ke profile.edit (yang butuh auth).
     */
    public function verify(Request $request): RedirectResponse
    {
        try {
            $decrypted = Crypt::decryptString((string) $request->query('payload'));
        } catch (DecryptException) {
            abort(403);
        }

        [$userId, $newEmail] = array_pad(explode('|', $decrypted, 2), 2, null);

        $user = $userId ? User::find($userId) : null;

        if (! $user || ! $newEmail) {
            abort(403);
        }

        // Cek ulang keunikan SAAT diklik (race condition) — bisa saja email
        // itu sudah dipakai user lain di antara waktu link dikirim & diklik.
        if (User::where('email', $newEmail)->where('id', '!=', $user->id)->exists()) {
            return $this->redirectAfterEmailVerification(
                $user,
                success: false,
                message: 'Email tersebut sudah digunakan akun lain. Email Anda tidak diubah — silakan ajukan perubahan dengan alamat lain.',
            );
        }

        $user->forceFill([
            'email' => $newEmail,
            'email_verified_at' => now(),
        ])->save();

        return $this->redirectAfterEmailVerification(
            $user,
            success: true,
            message: "Email berhasil diubah menjadi {$newEmail}.",
        );
    }

    /**
     * Sesi yang sedang aktif di browser ini bisa jadi: milik user yang sama
     * (ajukan & konfirmasi di device yang sama) → kembali ke Profil dengan
     * flash global (`success`/`error`, dirender <x-flash-message /> di semua
     * halaman authenticated). Sesi lain/tidak ada sesi sama sekali (buka
     * link dari inbox di device berbeda) → tidak bisa diarahkan ke halaman
     * yang butuh auth, jadi dikirim ke halaman yang dapat diakses siapa pun
     * (dashboard kalau sedang login sebagai user lain, atau login kalau
     * guest) dengan pesan lengkap supaya tetap informatif.
     */
    private function redirectAfterEmailVerification(User $user, bool $success, string $message): RedirectResponse
    {
        $flashKey = $success ? 'success' : 'error';

        if (auth()->check() && auth()->id() === $user->id) {
            return redirect()->route('profile.edit')->with($flashKey, $message);
        }

        if (auth()->check()) {
            return redirect()->route('dashboard')->with($flashKey, $message);
        }

        return redirect()->route('login')->with('status', $message);
    }
}
