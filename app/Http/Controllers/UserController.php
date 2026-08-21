<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Input kosong dari text field dikirim sebagai string "" (bukan
        // null) — "nullable" di usernameRules() cuma melewatkan validasi
        // lanjutan kalau nilainya benar-benar null, sama seperti
        // ProfileUpdateRequest.
        if ($request->input('username') === '') {
            $request->merge(['username' => null]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => User::usernameRules(),
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,staff',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', __('ui.messages.user_created'));
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($request->input('username') === '') {
            $request->merge(['username' => null]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => User::usernameRules($user->id),
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,staff',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Sistem tidak boleh sampai kehilangan seluruh Admin. Dicek di backend
        // (bukan cuma disembunyikan di form) supaya request langsung pun ditolak.
        if ($user->isAdmin() && $validated['role'] !== 'admin' && $user->isLastAdmin()) {
            return redirect()
                ->route('users.index')
                ->with('error', __('ui.messages.last_admin_cannot_demote'));
        }

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        // "password" nullable — request yang tidak menyertakan field ini sama sekali
        // (mis. lewat API/raw request) bikin key-nya tidak ada di $validated, jadi
        // akses langsung $validated['password'] memicu "Undefined array key" → 500.
        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', __('ui.messages.user_updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', __('ui.messages.cannot_delete_self'));
        }

        if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', __('ui.messages.user_deleted'));
    }
}
