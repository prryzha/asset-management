<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use App\Models\Location;
use App\Models\Transaction;
use App\Models\MaintenanceSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $locationId = $request->input('location_id');
        $kondisi = $request->input('kondisi');
        $status = $request->input('status');

        $assets = Asset::with(['category', 'location'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('merk', 'like', "%{$search}%")
                        ->orWhere('nomor_seri', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn($q, $v) => $q->where('category_id', $v))
            ->when($locationId, fn($q, $v) => $q->where('location_id', $v))
            ->when($kondisi, fn($q, $v) => $q->where('kondisi', $v))
            ->when($status, fn($q, $v) => $q->where('status', $v))
            ->orderBy('kode_barang')
            ->paginate(10)
            ->withQueryString();

        // Cache filter dropdowns — refresh tiap 1 jam karena jarang berubah
        $categories = Cache::remember('filter_categories', 60, function () {
            return Category::orderBy('nama')->get();
        });
        $locations = Cache::remember('filter_locations', 60, function () {
            return Location::orderBy('nama')->get();
        });

        return view('assets.index', compact('assets', 'search', 'categories', 'locations', 'categoryId', 'locationId', 'kondisi', 'status'));
    }

    public function create(): View
    {
        $categories = Cache::remember('filter_categories', 60, function () {
            return Category::orderBy('nama')->get();
        });
        $locations = Cache::remember('filter_locations', 60, function () {
            return Location::orderBy('nama')->get();
        });

        return view('assets.create', compact('categories', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => ['nullable', 'string', Rule::unique('assets', 'kode_barang')->whereNull('deleted_at')],
            'kode_bmd' => 'nullable|string|max:50',
            'kib' => 'nullable|in:A,B,C,D,E,F',
            'nama_barang' => 'required|string|max:255',
            'merk' => 'nullable|string|max:255',
            'nomor_seri' => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'penanggung_jawab' => 'nullable|string|max:255',
            'kondisi' => 'required|in:Baik,Kurang Baik,Rusak Berat',
            'status' => 'required|in:Tersedia,Dipinjam,Perbaikan',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'nullable|string|max:20',

            'tahun_perolehan' => 'nullable|integer|min:1900|max:' . date('Y'),

            'nilai_perolehan' => 'nullable|numeric|min:0',

            'catatan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Auto-generate kode barang jika tidak diisi
        if (empty($validated['kode_barang'])) {
            $category = Category::findOrFail($validated['category_id']);
            $validated['kode_barang'] = Asset::generateKodeBarang($category);
        }

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('assets', 'public');
        }

        $asset = Asset::create($validated);

        $asset->load('category', 'location');

        ActivityLog::record($asset, 'asset.created', "Menambahkan asset {$asset->kode_barang}");

        // Auto-log ke AssetLog
        AssetLog::create([
            'asset_id' => $asset->id,
            'tipe' => 'lainnya',
            'deskripsi' => "Asset baru: {$asset->nama_barang} ({$asset->kode_barang})",
            'user_id' => auth()->id(),
        ]);

        // Hapus cache dropdown & dashboard setelah data berubah
        $this->clearCache();

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Asset berhasil ditambahkan.');
    }

    public function show(Asset $asset): View
    {
        $asset->load(['category', 'location']);
        $transactions = $asset->transactions()->latest()->take(5)->get();
        $maintenanceSchedules = $asset->maintenanceSchedules()->latest('tanggal_jadwal')->get();
        $activityLogs = auth()->user()->isAdmin()
            ? $asset->activityLogs()->with('user')->latest()->take(10)->get()
            : collect();
        $assetLogs = $asset->assetLogs()->with('user')->latest()->take(20)->get();

        return view('assets.show', compact('asset', 'transactions', 'maintenanceSchedules', 'activityLogs', 'assetLogs'));
    }

    public function edit(Asset $asset): View
    {
        $categories = Cache::remember('filter_categories', 60, function () {
            return Category::orderBy('nama')->get();
        });
        $locations = Cache::remember('filter_locations', 60, function () {
            return Location::orderBy('nama')->get();
        });

        return view('assets.edit', compact('asset', 'categories', 'locations'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'kode_barang' => ['required', Rule::unique('assets', 'kode_barang')->ignore($asset->id)->whereNull('deleted_at')],
            'kode_bmd' => 'nullable|string|max:50',
            'kib' => 'nullable|in:A,B,C,D,E,F',
            'nama_barang' => 'required|string|max:255',
            'merk' => 'nullable|string|max:255',
            'nomor_seri' => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'penanggung_jawab' => 'nullable|string|max:255',
            'kondisi' => 'required|in:Baik,Kurang Baik,Rusak Berat',
            'status' => 'required|in:Tersedia,Dipinjam,Perbaikan',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'nullable|string|max:20',

            'tahun_perolehan' => 'nullable|integer|min:1900|max:' . date('Y'),

            'nilai_perolehan' => 'nullable|numeric|min:0',

            'catatan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Catat perubahan lokasi (mutasi)
        if ($validated['location_id'] != $asset->location_id) {
            $oldLocation = $asset->location?->nama ?? 'unknown';
            $newLocation = Location::find($validated['location_id'])?->nama ?? 'unknown';
            AssetLog::create([
                'asset_id' => $asset->id,
                'tipe' => 'mutasi',
                'deskripsi' => "Perpindahan lokasi dari {$oldLocation} ke {$newLocation}",
                'user_id' => auth()->id(),
            ]);
        }

        // Catat perubahan kondisi
        if ($validated['kondisi'] != $asset->kondisi) {
            AssetLog::create([
                'asset_id' => $asset->id,
                'tipe' => 'kondisi',
                'deskripsi' => "Kondisi berubah dari '{$asset->kondisi}' menjadi '{$validated['kondisi']}'",
                'user_id' => auth()->id(),
            ]);
        }

        if ($request->hasFile('foto')) {
            if ($asset->foto && Storage::disk('public')->exists($asset->foto)) {
                Storage::disk('public')->delete($asset->foto);
            }
            $validated['foto'] = $request->file('foto')->store('assets', 'public');
        }

        $asset->update($validated);

        ActivityLog::record($asset, 'asset.updated', "Mengubah asset {$asset->kode_barang}");

        // Hapus cache setelah data berubah
        $this->clearCache();

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Asset berhasil diperbarui.');
    }

    public function reportDamage(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'kondisi' => ['required', 'in:Kurang Baik,Rusak Berat'],
            'deskripsi' => ['required', 'string', 'max:500'],
        ]);

        $oldKondisi = $asset->kondisi;

        $asset->update(['kondisi' => $validated['kondisi']]);

        // Jika status masih Tersedia dan dilapor rusak berat, ubah ke Perbaikan
        if ($validated['kondisi'] === 'Rusak Berat' && $asset->status === 'Tersedia') {
            $asset->update(['status' => 'Perbaikan']);
        }

        AssetLog::create([
            'asset_id' => $asset->id,
            'tipe' => 'kondisi',
            'deskripsi' => $validated['deskripsi'] . " (Laporan: {$oldKondisi} → {$validated['kondisi']})",
            'user_id' => auth()->id(),
        ]);

        ActivityLog::record($asset, 'asset.reported-damage', "Melaporkan kerusakan {$asset->kode_barang}: {$validated['deskripsi']}");

        $this->clearCache();

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Kerusakan berhasil dilaporkan.');
    }

    public function deleteFoto(Asset $asset): RedirectResponse
    {
        if ($asset->foto) {
            if (Storage::disk('public')->exists($asset->foto)) {
                Storage::disk('public')->delete($asset->foto);
            }
            $asset->update(['foto' => null]);

            ActivityLog::record($asset, 'asset.photo-deleted', "Menghapus foto asset {$asset->kode_barang}");

            return redirect()
                ->route('assets.edit', $asset)
                ->with('success', 'Foto asset berhasil dihapus.');
        }

        return redirect()
            ->route('assets.edit', $asset)
            ->with('error', 'Tidak ada foto untuk dihapus.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        if ($asset->status === 'Dipinjam' || $asset->transactions()->where('status_peminjaman', 'Dipinjam')->exists()) {
            return redirect()->route('assets.index')->with('error', 'Tidak dapat menghapus aset yang sedang dipinjam.');
        }

        $photo = $asset->foto;
        $assetCode = $asset->kode_barang;

        DB::transaction(function () use ($asset, $assetCode) {
            $asset->delete();
            ActivityLog::record($asset, 'asset.deleted', "Menghapus aset {$assetCode}", ['kode_barang' => $assetCode]);
        });

        if ($photo) {
            Storage::disk('public')->delete($photo);
        }

        $this->clearCache();

        return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus.');
    }

    public function exportPdf(Request $request): Response
    {
        $categoryId = $request->input('category_id');
        $locationId = $request->input('location_id');

        $assets = Asset::with(['category', 'location'])
            ->when($categoryId, fn($q, $v) => $q->where('category_id', $v))
            ->when($locationId, fn($q, $v) => $q->where('location_id', $v))
            ->orderBy('kode_barang')
            ->get();

        $filterCategory = $categoryId ? Category::find($categoryId)?->nama : null;
        $filterLocation = $locationId ? Location::find($locationId)?->nama : null;

        $pdf = Pdf::loadView('pdf.assets', compact('assets', 'filterCategory', 'filterLocation'));

        return $pdf->download('laporan-aset.pdf');
    }

    public function qrCode(Asset $asset): Response
    {
        $svg = QrCode::format('svg')
            ->size(240)
            ->margin(1)
            ->generate(route('assets.show', $asset));

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function label(Asset $asset): View
    {
        return view('assets.label', compact('asset'));
    }

    public function labelPdf(Asset $asset): Response
    {
        $svg = QrCode::format('svg')
            ->size(240)
            ->margin(1)
            ->generate(route('assets.show', $asset));

        $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);

        $asset->load(['category', 'location']);

        $pdf = Pdf::loadView('pdf.label', compact('asset', 'qrDataUri'));

        return $pdf->download("label-{$asset->kode_barang}.pdf");
    }

    public function nextCode(string $prefix): \Illuminate\Http\JsonResponse
    {
        $kodeBarang = Asset::findNextKodeBarang(strtoupper($prefix));

        return response()->json(['kode_barang' => $kodeBarang]);
    }

    public function dashboard(): View
    {
        // Cache data dashboard — refresh setiap 5 menit
        $cached = Cache::remember('dashboard_data', 300, function () {
            /*
            |--------------------------------------------------------------------------
            | Statistik Asset & Kondisi — di-group jadi 1 query
            |--------------------------------------------------------------------------
            */
            $assetStats = Asset::selectRaw('
                COUNT(*) as total_asset,
                COALESCE(SUM(nilai_perolehan * jumlah), 0) as total_nilai,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as tersedia,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as dipinjam,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as perbaikan,
                SUM(CASE WHEN kondisi = ? THEN 1 ELSE 0 END) as baik,
                SUM(CASE WHEN kondisi = ? THEN 1 ELSE 0 END) as kurang_baik,
                SUM(CASE WHEN kondisi = ? THEN 1 ELSE 0 END) as rusak_berat
            ', ['Tersedia', 'Dipinjam', 'Perbaikan', 'Baik', 'Kurang Baik', 'Rusak Berat'])
            ->first();

            /*
            |--------------------------------------------------------------------------
            | Maintenance
            |--------------------------------------------------------------------------
            */
            $maintenanceTertunda = MaintenanceSchedule::where('status', 'Dijadwalkan')
                ->whereDate('tanggal_jadwal', '<=', today())
                ->count();

            $maintenanceAktif = MaintenanceSchedule::where('status', 'Dikerjakan')
                ->count();

            /*
            |--------------------------------------------------------------------------
            | Statistik Kategori
            |--------------------------------------------------------------------------
            */
            $kategori = Category::withCount('assets')
                ->orderBy('nama')
                ->get();

            $kategoriLabels = $kategori->pluck('nama');
            $kategoriData = $kategori->pluck('assets_count');

            /*
            |--------------------------------------------------------------------------
            | Statistik Lokasi
            |--------------------------------------------------------------------------
            */
            $lokasi = Location::withCount('assets')
                ->orderBy('nama')
                ->get();

            $lokasiLabels = $lokasi->pluck('nama');
            $lokasiData = $lokasi->pluck('assets_count');

            /*
            |--------------------------------------------------------------------------
            | Asset Terbaru (dengan eager loading biar ga N+1)
            |--------------------------------------------------------------------------
            */
            $recentAssets = Asset::with(['category', 'location'])
                ->latest()
                ->take(5)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Aktivitas Terbaru
            |--------------------------------------------------------------------------
            */
            $recentActivities = ActivityLog::with('user')
                ->latest()
                ->take(5)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Barang Dipinjam
            |--------------------------------------------------------------------------
            */
            $borrowedCount = Transaction::where('status_peminjaman', 'Dipinjam')
                ->count();

            /*
            |--------------------------------------------------------------------------
            | Maintenance Akan Datang
            |--------------------------------------------------------------------------
            */
            $maintenanceUpcoming = MaintenanceSchedule::with('asset')
                ->whereIn('status', ['Dijadwalkan', 'Dikerjakan'])
                ->orderBy('tanggal_jadwal')
                ->take(5)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Transaksi Terbaru
            |--------------------------------------------------------------------------
            */
            $recentTransactions = Transaction::with('asset')
                ->latest()
                ->take(5)
                ->get();

            return [
                'assetStats' => $assetStats,
                'maintenanceTertunda' => $maintenanceTertunda,
                'maintenanceAktif' => $maintenanceAktif,
                'kategoriLabels' => $kategoriLabels,
                'kategoriData' => $kategoriData,
                'lokasiLabels' => $lokasiLabels,
                'lokasiData' => $lokasiData,
                'recentAssets' => $recentAssets,
                'recentActivities' => $recentActivities,
                'borrowedCount' => $borrowedCount,
                'maintenanceUpcoming' => $maintenanceUpcoming,
                'recentTransactions' => $recentTransactions,
            ];
        });

        return view('dashboard', array_merge(
            $cached,
            [
                'totalAsset' => $cached['assetStats']->total_asset,
                'totalNilai' => $cached['assetStats']->total_nilai,
                'tersedia' => $cached['assetStats']->tersedia,
                'dipinjam' => $cached['assetStats']->dipinjam,
                'perbaikan' => $cached['assetStats']->perbaikan,
                'baik' => $cached['assetStats']->baik,
                'kurangBaik' => $cached['assetStats']->kurang_baik,
                'rusakBerat' => $cached['assetStats']->rusak_berat,
            ]
        ));
    }

    /**
     * Clear all cached data after any asset mutation.
     */
    private function clearCache(): void
    {
        Cache::forget('dashboard_data');
        Cache::forget('filter_categories');
        Cache::forget('filter_locations');
        Cache::forget('available_assets');
        Cache::forget('all_assets');
        Cache::forget('all_assets_v2');
    }
}
