<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = Location::withCount('assets')
            ->orderBy('nama')
            ->paginate(10);

        return view('locations.index', compact('locations'));
    }

    public function create(): View
    {
        return view('locations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('locations', 'nama'),
            ],
            'kode' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('locations', 'kode'),
            ],
            'gedung' => ['nullable', 'string', 'max:100'],
        ]);

        $location = Location::create($validated);

        Cache::forget('filter_locations');

        ActivityLog::record(
            $location,
            'location.created',
            "Menambahkan lokasi {$location->nama}"
        );

        return redirect()
            ->route('locations.index')
            ->with('success', __('ui.messages.location_created'));
    }

    public function edit(Location $location): View
    {
        return view('locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('locations', 'nama')->ignore($location),
            ],
            'kode' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('locations', 'kode')->ignore($location),
            ],
            'gedung' => ['nullable', 'string', 'max:100'],
        ]);

        $previousName = $location->nama;

        $location->update($validated);

        Cache::forget('filter_locations');

        if ($location->wasChanged('nama')) {
            ActivityLog::record(
                $location,
                'location.updated',
                "Mengubah lokasi {$previousName} menjadi {$location->nama}",
                [
                    'nama_lama' => $previousName,
                    'nama_baru' => $location->nama,
                ]
            );
        }

        return redirect()
            ->route('locations.index')
            ->with('success', __('ui.messages.location_updated'));
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($location->assets()->exists()) {
            return redirect()
                ->route('locations.index')
                ->with('error', __('ui.messages.location_in_use'));
        }

        $name = $location->nama;

        $location->delete();

        Cache::forget('filter_locations');

        ActivityLog::record(
            $location,
            'location.deleted',
            "Menghapus lokasi {$name}",
            [
                'nama' => $name,
            ]
        );

        return redirect()
            ->route('locations.index')
            ->with('success', __('ui.messages.location_deleted'));
    }
}