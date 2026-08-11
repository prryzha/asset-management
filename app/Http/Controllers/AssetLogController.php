<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssetLogController extends Controller
{
    public function store(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'tipe' => ['required', 'in:mutasi,perawatan,kondisi,lainnya'],
            'deskripsi' => ['required', 'string', 'max:500'],
        ]);

        AssetLog::create([
            'asset_id' => $asset->id,
            'tipe' => $validated['tipe'],
            'deskripsi' => $validated['deskripsi'],
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Log aktivitas berhasil ditambahkan.');
    }
}
