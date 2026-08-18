<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's name. Email TIDAK lagi ditangani di sini — punya
     * alur verifikasi tersendiri lewat ProfileEmailController.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // UserController::destroy() sudah memblokir admin menghapus akunnya sendiri,
        // tapi jalur /profile ini tidak lewat sana — tanpa guard di bawah, Admin
        // terakhir masih bisa menghapus akunnya sendiri dan menyisakan 0 Admin.
        if ($request->user()->isLastAdmin()) {
            return Redirect::route('profile.edit')
                ->with('error', 'Akun Admin terakhir tidak dapat dihapus.');
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
