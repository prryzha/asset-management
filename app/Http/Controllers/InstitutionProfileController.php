<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstitutionProfileUpdateRequest;
use App\Models\InstitutionProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InstitutionProfileController extends Controller
{
    /**
     * Akses sudah dibatasi middleware route `role:admin` (routes/web.php).
     */
    public function edit(): View
    {
        return view('institution-profile.edit', [
            'profile' => InstitutionProfile::current(),
        ]);
    }

    public function update(InstitutionProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $profile = InstitutionProfile::current();

        if ($request->hasFile('logo')) {
            if ($profile->logo && Storage::disk('public')->exists($profile->logo)) {
                Storage::disk('public')->delete($profile->logo);
            }
            $validated['logo'] = $request->file('logo')->store('institution', 'public');
        } else {
            unset($validated['logo']);
        }

        InstitutionProfile::updateOrCreate(['id' => 1], $validated);

        // Cache dipakai InstitutionProfileComposer untuk branding header —
        // di-invalidate langsung di sini supaya perubahan langsung tampak,
        // tidak perlu menunggu TTL.
        Cache::forget('institution_profile');

        return Redirect::route('institution-profile.edit')->with('status', 'institution-profile-updated');
    }
}
