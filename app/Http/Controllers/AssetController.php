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
use Illuminate\Validation\ValidationException;
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

        // Data baru ditampilkan setelah tombol "Cari" diklik (form filter dikirim) — tabel
        // kosong di kunjungan awal. Hidden input "f" menandai form pernah dikirim, supaya klik
        // "Cari" dengan semua kolom dibiarkan default (Semua Kategori/Semua Lokasi) tetap
        // menampilkan semua data, bukan dianggap "belum difilter".
        $hasFilter = $request->has('f') || $search || $categoryId || $locationId || $kondisi || $status;

        $sortOptions = [
            'kode_asc' => ['kode_barang', 'asc'],
            'kode_desc' => ['kode_barang', 'desc'],
            'nama_asc' => ['nama_barang', 'asc'],
            'nama_desc' => ['nama_barang', 'desc'],
            'nilai_desc' => ['nilai_perolehan', 'desc'],
            'nilai_asc' => ['nilai_perolehan', 'asc'],
            'terbaru' => ['created_at', 'desc'],
        ];
        $sort = $sortOptions[$request->input('sort')] ?? $sortOptions['kode_asc'];

        $assets = $this->activeAssetQuery($request)
            ->when(!$hasFilter, fn($q) => $q->whereIn('id', []))
            ->orderBy($sort[0], $sort[1])
            ->paginate(10)
            ->withQueryString();

        // Cache filter dropdowns — refresh tiap 1 jam karena jarang berubah
        $categories = Cache::remember('filter_categories', 60, function () {
            return Category::orderBy('nama')->get();
        });
        $locations = Cache::remember('filter_locations', 60, function () {
            return Location::orderBy('nama')->get();
        });

        return view('assets.index', compact('assets', 'search', 'categories', 'locations', 'categoryId', 'locationId', 'kondisi', 'status', 'hasFilter'));
    }

    /**
     * Query dasar Daftar Aset — ASET AKTIF saja (status != 'Disposed') dengan
     * seluruh filter yang didukung: search, kategori, lokasi, kondisi, status.
     * Dipakai bareng index() dan export PDF/CSV supaya dataset tabel == dataset
     * export untuk filter yang sama (pola sama seperti archiveQuery()/
     * lostReportQuery()/transactionReportQuery()).
     *
     * $respectFilters dimatikan khusus export "data terpilih" (ids): saat user
     * mengekspor baris yang dicentang, filter halaman tidak ikut membatasi —
     * perilaku export lama yang sengaja dipertahankan.
     */
    private function activeAssetQuery(Request $request, bool $respectFilters = true)
    {
        return Asset::with(['category', 'location'])
            // Aset "Disposed" (label UI: Dihapuskan) dikecualikan di level query,
            // bukan disembunyikan di view — supaya filter/search/paginate/export
            // semuanya konsisten dan tidak bisa bocor lewat raw request
            // (mis. ?status=Disposed).
            ->where('status', '!=', 'Disposed')
            // CATATAN: pakai ternary, bukan "&&" — di PHP, true && 'Baik' menghasilkan
            // boolean true, bukan string 'Baik', sehingga when() akan melempar nilai
            // true ke closure dan where('kondisi', true) tidak mencocokkan apa pun.
            ->when($respectFilters ? $request->input('search') : null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('merk', 'like', "%{$search}%")
                        ->orWhere('nomor_seri', 'like', "%{$search}%");
                });
            })
            ->when($respectFilters ? $request->input('category_id') : null, fn($q, $v) => $q->where('category_id', $v))
            ->when($respectFilters ? $request->input('location_id') : null, fn($q, $v) => $q->where('location_id', $v))
            ->when($respectFilters ? $request->input('kondisi') : null, fn($q, $v) => $q->where('kondisi', $v))
            ->when($respectFilters ? $request->input('status') : null, fn($q, $v) => $q->where('status', $v));
    }

    /**
     * Arsip Aset — kebalikan dari index(): HANYA aset berstatus "Disposed"
     * (label UI: Dihapuskan). Ini murni pemisahan tampilan; datanya tetap di
     * tabel `assets` yang sama, tidak ada tabel arsip terpisah.
     *
     * CATATAN: arsip di sini TIDAK ada hubungannya dengan SoftDeletes. Aset yang
     * di-soft-delete lewat tombol "Hapus Aset" tetap tidak muncul di manapun
     * (global scope Eloquent), sedangkan aset Dihapuskan adalah record aktif di
     * DB yang statusnya administratif — dua konsep berbeda.
     */
    public function archive(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $locationId = $request->input('location_id');

        $assets = $this->archiveQuery($request)
            // Log penghapusan di-eager-load (bukan query per baris) supaya tabel
            // arsip bisa menampilkan tanggal/petugas penghapusan tanpa N+1.
            ->with(['assetLogs' => fn($q) => $q->where('tipe', 'penghapusan')->with('user')->latest()])
            ->orderBy('kode_barang')
            ->paginate(10)
            ->withQueryString();

        $categories = Cache::remember('filter_categories', 60, function () {
            return Category::orderBy('nama')->get();
        });
        $locations = Cache::remember('filter_locations', 60, function () {
            return Location::orderBy('nama')->get();
        });

        return view('assets.archive', compact('assets', 'search', 'categories', 'locations', 'categoryId', 'locationId'));
    }

    /**
     * Query dasar Arsip Aset — dipakai bareng halaman arsip & export-nya supaya
     * filter status "Disposed" tidak mungkin kelewat di salah satu jalur.
     */
    private function archiveQuery(Request $request)
    {
        return Asset::with(['category', 'location'])
            ->where('status', 'Disposed')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('merk', 'like', "%{$search}%")
                        ->orWhere('nomor_seri', 'like', "%{$search}%")
                        ->orWhereHas('category', fn($q) => $q->where('nama', 'like', "%{$search}%"))
                        ->orWhereHas('location', fn($q) => $q->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when($request->input('category_id'), fn($q, $v) => $q->where('category_id', $v))
            ->when($request->input('location_id'), fn($q, $v) => $q->where('location_id', $v));
    }

    public function archiveExportPdf(Request $request): Response
    {
        $assets = $this->archiveQuery($request)->orderBy('kode_barang')->get();

        $pdf = Pdf::loadView('pdf.assets-archive', compact('assets'));

        return $pdf->download('laporan-arsip-aset.pdf');
    }

    public function archiveExportCsv(Request $request): Response
    {
        $assets = $this->archiveQuery($request)
            ->with(['assetLogs' => fn($q) => $q->where('tipe', 'penghapusan')->with('user')->latest()])
            ->orderBy('kode_barang')
            ->get();

        return response()->streamDownload(function () use ($assets) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Barang', 'Nama Barang', 'Merk', 'Kategori', 'Lokasi Terakhir', 'Kondisi', 'Status', 'Tanggal Penghapusan', 'Petugas', 'Keterangan Penghapusan']);
            foreach ($assets as $asset) {
                $log = $asset->assetLogs->first();
                fputcsv($out, [
                    $asset->kode_barang,
                    $asset->nama_barang,
                    $asset->merk,
                    $asset->category?->nama,
                    $asset->location?->nama,
                    $asset->kondisi,
                    $this->statusLabel($asset->status),
                    $log?->created_at->format('Y-m-d H:i'),
                    $log?->user?->name,
                    $log?->deskripsi,
                ]);
            }
            fclose($out);
        }, 'arsip-aset-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Laporan Aset Hilang — daftar aset yang masih berstatus "Hilang" beserta
     * detail laporannya (tanggal, kronologi, petugas) yang diambil dari AssetLog
     * tipe 'hilang'. Murni tampilan laporan baca-saja; tidak mengubah status.
     *
     * Aset yang sudah ditemukan kembali / Dihapuskan otomatis tidak muncul karena
     * statusnya sudah berubah (Tersedia / Disposed) — query hanya menyentuh
     * status 'Hilang' di level query, konsisten dengan pola archive().
     */
    public function lostReport(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $assets = $this->lostReportQuery($request)
            // Log kehilangan di-eager-load (bukan query per baris) supaya tabel
            // laporan bisa menampilkan tanggal/petugas/kronologi tanpa N+1.
            ->with(['assetLogs' => fn($q) => $q->where('tipe', 'hilang')->with('user')->latest()])
            ->orderBy('kode_barang')
            ->paginate(10)
            ->withQueryString();

        $categories = Cache::remember('filter_categories', 60, function () {
            return Category::orderBy('nama')->get();
        });

        return view('assets.lost', compact('assets', 'search', 'categories', 'categoryId'));
    }

    /**
     * Query dasar Laporan Aset Hilang — dipakai bareng halaman & export-nya supaya
     * filter status "Hilang" tidak mungkin kelewat di salah satu jalur.
     */
    private function lostReportQuery(Request $request)
    {
        return Asset::with(['category', 'location'])
            ->where('status', 'Hilang')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('merk', 'like', "%{$search}%")
                        ->orWhereHas('category', fn($q) => $q->where('nama', 'like', "%{$search}%"))
                        ->orWhereHas('location', fn($q) => $q->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when($request->input('category_id'), fn($q, $v) => $q->where('category_id', $v));
    }

    public function lostReportExportPdf(Request $request): Response
    {
        $assets = $this->lostReportQuery($request)
            ->with(['assetLogs' => fn($q) => $q->where('tipe', 'hilang')->with('user')->latest()])
            ->orderBy('kode_barang')
            ->get();

        $pdf = Pdf::loadView('pdf.assets-lost', compact('assets'));

        return $pdf->download('laporan-aset-hilang.pdf');
    }

    public function lostReportExportCsv(Request $request): Response
    {
        $assets = $this->lostReportQuery($request)
            ->with(['assetLogs' => fn($q) => $q->where('tipe', 'hilang')->with('user')->latest()])
            ->orderBy('kode_barang')
            ->get();

        return response()->streamDownload(function () use ($assets) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Barang', 'Nama Barang', 'Merk', 'Kategori', 'Lokasi Terakhir', 'Kondisi', 'Tanggal Laporan', 'Petugas', 'Keterangan']);
            foreach ($assets as $asset) {
                $log = $asset->assetLogs->first();
                fputcsv($out, [
                    $asset->kode_barang,
                    $asset->nama_barang,
                    $asset->merk,
                    $asset->category?->nama,
                    $asset->location?->nama,
                    $asset->kondisi,
                    $log?->created_at->format('Y-m-d H:i'),
                    $log?->user?->name,
                    $log?->deskripsi,
                ]);
            }
            fclose($out);
        }, 'laporan-aset-hilang-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
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
            'nama_barang' => 'required|string|max:255',
            'merk' => 'nullable|string|max:255',
            'nomor_seri' => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'penanggung_jawab' => 'nullable|string|max:255',
            'kondisi' => 'required|in:Baik,Kurang Baik,Rusak Berat',
            'jumlah_unit' => 'nullable|integer|min:1|max:50',

            'tahun_perolehan' => 'nullable|integer|min:1900|max:' . date('Y'),

            'nilai_perolehan' => 'nullable|numeric|min:0',

            'catatan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // "status" SENGAJA tidak divalidasi dari request — status aset mengikuti
        // business lifecycle transaction-driven (Dipinjam via peminjaman, Perbaikan
        // via maintenance/report damage), bukan pilihan bebas saat aset baru dibuat.
        // Aset baru belum pernah mengalami transaksi apa pun, jadi satu-satunya
        // status yang valid di titik ini adalah Tersedia — ditetapkan server-side.
        $validated['status'] = 'Tersedia';

        $jumlahUnit = (int) ($validated['jumlah_unit'] ?? 1);
        unset($validated['jumlah_unit']);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('assets', 'public');
        }

        // Tambah banyak unit identik sekaligus — tiap unit jadi baris tersendiri
        // dengan kode barang berurutan (mis. PLP-001, PLP-002, ...).
        if ($jumlahUnit > 1) {
            // Nomor seri fisik tiap unit biasanya beda — jangan sampai satu nilai
            // ke-copy identik ke semua unit yang dibuat sekaligus.
            unset($validated['nomor_seri']);

            $baseKode = $validated['kode_barang'] ?? null;
            if (! $baseKode) {
                $category = Category::findOrFail($validated['category_id']);
                $baseKode = Asset::generateKodeBarang($category);
            }
            $prefix = Str::beforeLast($baseKode, '-');
            $codes = Asset::nextSequentialCodes($prefix, $jumlahUnit);
            unset($validated['kode_barang']);

            $assets = collect();
            DB::transaction(function () use (&$assets, $codes, $validated) {
                foreach ($codes as $kode) {
                    $assets->push(Asset::create(array_merge($validated, ['kode_barang' => $kode])));
                }
            });

            foreach ($assets as $asset) {
                ActivityLog::record($asset, 'asset.created', "Menambahkan aset {$asset->kode_barang}");
                AssetLog::create([
                    'asset_id' => $asset->id,
                    'tipe' => 'lainnya',
                    'deskripsi' => "Aset baru: {$asset->nama_barang} ({$asset->kode_barang})",
                    'user_id' => auth()->id(),
                ]);
            }

            $this->clearCache();

            return redirect()
                ->route('assets.index')
                ->with('success', "{$jumlahUnit} unit aset berhasil ditambahkan.");
        }

        // Auto-generate kode barang jika tidak diisi
        if (empty($validated['kode_barang'])) {
            $category = Category::findOrFail($validated['category_id']);
            $validated['kode_barang'] = Asset::generateKodeBarang($category);
        }

        $asset = Asset::create($validated);

        $asset->load('category', 'location');

        ActivityLog::record($asset, 'asset.created', "Menambahkan aset {$asset->kode_barang}");

        // Auto-log ke AssetLog
        AssetLog::create([
            'asset_id' => $asset->id,
            'tipe' => 'lainnya',
            'deskripsi' => "Aset baru: {$asset->nama_barang} ({$asset->kode_barang})",
            'user_id' => auth()->id(),
        ]);

        // Hapus cache dropdown & dashboard setelah data berubah
        $this->clearCache();

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show(Asset $asset): View
    {
        $asset->load(['category', 'location']);
        $transactions = $asset->transactions()->latest()->take(5)->get();
        $maintenanceSchedules = $asset->maintenanceSchedules()->latest('tanggal_jadwal')->get();
        $activityLogs = $asset->activityLogs()->with('user')->latest()->take(10)->get();
        $assetLogs = $asset->assetLogs()->with('user')->latest()->take(20)->get();

        // Info kehilangan di kartu Detail Aset — hanya diperlukan saat status Hilang.
        $lostLog = $asset->status === 'Hilang'
            ? $asset->assetLogs()->with('user')->where('tipe', 'hilang')->latest()->first()
            : null;

        // Info penghapusan — hanya diperlukan saat status Disposed (label UI: Dihapuskan).
        $disposalLog = $asset->status === 'Disposed'
            ? $asset->assetLogs()->with('user')->where('tipe', 'penghapusan')->latest()->first()
            : null;

        // Dipakai untuk gating tombol "Proses Penghapusan" — aset Perbaikan yang masih
        // punya maintenance aktif (Dikerjakan) belum boleh dihapuskan, lihat processDisposal().
        $hasActiveMaintenance = $asset->maintenanceSchedules()->where('status', 'Dikerjakan')->exists();

        return view('assets.show', compact('asset', 'transactions', 'maintenanceSchedules', 'activityLogs', 'assetLogs', 'lostLog', 'disposalLog', 'hasActiveMaintenance'));
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
        // "status" SENGAJA tidak divalidasi/diterima di sini. Status aset (Dipinjam,
        // Perbaikan, Hilang, Disposed) adalah hasil proses lain (Peminjaman,
        // Perawatan, dst) — bukan atribut administratif yang boleh diubah bebas
        // lewat Edit Aset. Karena Laravel's validate() cuma me-return key yang ada
        // di rules, "status" tidak akan pernah masuk ke $validated walau raw
        // request body menyertakannya (mis. lewat curl/Postman/manipulasi URL) —
        // sehingga $asset->update($validated) di bawah tidak pernah menyentuh
        // kolom itu. Satu-satunya jalur resmi mengubah status: Transaction (borrow/
        // return) & MaintenanceSchedule (start/complete). Lihat juga store() yang
        // MASIH boleh menerima status karena itu penentuan awal saat aset dibuat,
        // bukan perubahan status aset yang sudah eksis.
        $validated = $request->validate([
            'kode_barang' => ['required', Rule::unique('assets', 'kode_barang')->ignore($asset->id)->whereNull('deleted_at')],
            'nama_barang' => 'required|string|max:255',
            'merk' => 'nullable|string|max:255',
            'nomor_seri' => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'penanggung_jawab' => 'nullable|string|max:255',
            'kondisi' => 'required|in:Baik,Kurang Baik,Rusak Berat',

            'tahun_perolehan' => 'nullable|integer|min:1900|max:' . date('Y'),

            'nilai_perolehan' => 'nullable|numeric|min:0',

            'catatan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Aset Dihapuskan tetap boleh diperbarui sebagai data master, tetapi tidak
        // boleh dimutasi lagi. Ini menjaga arsip tetap sebagai catatan lokasi terakhir
        // dan menutup bypass lewat raw request.
        if ($asset->status === 'Disposed'
            && array_key_exists('location_id', $validated)
            && $validated['location_id'] != $asset->location_id) {
            throw ValidationException::withMessages([
                'location_id' => 'Lokasi aset yang sudah Dihapuskan tidak dapat diubah.',
            ]);
        }

        // Catat perubahan lokasi (mutasi). Pakai array_key_exists, bukan cuma "?? null"
        // — location_id "nullable" berarti raw request yang tidak menyertakan field ini
        // SAMA SEKALI (bukan cuma dikosongkan lewat form) membuat key-nya tidak ada di
        // $validated (Laravel's validated() cuma include key yang benar-benar ada di
        // input). Kalau key benar-benar tidak ada, berarti request ini tidak berniat
        // menyentuh lokasi sama sekali — jangan dianggap "dipindah ke kosong", dan
        // $asset->update($validated) di bawah memang tidak akan menyentuh kolom ini.
        if (array_key_exists('location_id', $validated) && $validated['location_id'] != $asset->location_id) {
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

        ActivityLog::record($asset, 'asset.updated', "Mengubah aset {$asset->kode_barang}");

        // Hapus cache setelah data berubah
        $this->clearCache();

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Aset berhasil diperbarui.');
    }

    public function reportDamage(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'kondisi' => ['required', 'in:Kurang Baik,Rusak Berat'],
            'deskripsi' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $asset) {
            $locked = Asset::lockForUpdate()->findOrFail($asset->id);

            // Melaporkan kerusakan cuma masuk akal untuk aset yang masih aktif —
            // tombolnya sudah disembunyikan untuk Hilang/Disposed di UI, tapi backend
            // tetap wajib menolak raw request juga (lihat pola yang sama di reportLost()).
            if (in_array($locked->status, ['Hilang', 'Disposed'])) {
                throw ValidationException::withMessages([
                    'status' => 'Aset dengan status ' . $this->statusLabel($locked->status) . ' tidak dapat dilaporkan kerusakannya.',
                ]);
            }

            $oldKondisi = $locked->kondisi;

            $locked->update(['kondisi' => $validated['kondisi']]);

            // Jika status masih Tersedia dan dilapor rusak berat, ubah ke Perbaikan
            if ($validated['kondisi'] === 'Rusak Berat' && $locked->status === 'Tersedia') {
                $locked->update(['status' => 'Perbaikan']);
            }

            AssetLog::create([
                'asset_id' => $locked->id,
                'tipe' => 'kondisi',
                'deskripsi' => $validated['deskripsi'] . " (Laporan: {$oldKondisi} → {$validated['kondisi']})",
                'user_id' => auth()->id(),
            ]);
        });

        ActivityLog::record($asset, 'asset.reported-damage', "Melaporkan kerusakan {$asset->kode_barang}: {$validated['deskripsi']}");

        $this->clearCache();

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Kerusakan berhasil dilaporkan.');
    }

    public function reportLost(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_kehilangan' => ['required', 'date', 'before_or_equal:today'],
            'keterangan' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $asset) {
            $locked = Asset::lockForUpdate()->findOrFail($asset->id);

            // Hanya aset Tersedia yang boleh dilaporkan hilang — lihat catatan di
            // reportDamage()/update() soal kenapa status hanya boleh disentuh satu
            // "pemilik" workflow dalam satu waktu. Aset Dipinjam/Perbaikan sudah
            // dimiliki Transaction/MaintenanceSchedule; melaporkan hilang di state
            // itu akan meninggalkan transaksi/jadwal itu menggantung tanpa penutup.
            if ($locked->status !== 'Tersedia') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya aset berstatus Tersedia yang dapat dilaporkan hilang.',
                ]);
            }

            $locked->update(['status' => 'Hilang']);

            $tanggal = \Carbon\Carbon::parse($validated['tanggal_kehilangan'])->format('d/m/Y');
            $lokasi = $locked->location?->nama ?? 'tidak diketahui';

            AssetLog::create([
                'asset_id' => $locked->id,
                'tipe' => 'hilang',
                'deskripsi' => "Dilaporkan hilang pada {$tanggal}. Lokasi terakhir: {$lokasi}. Kronologi: {$validated['keterangan']}",
                'user_id' => auth()->id(),
            ]);
        });

        ActivityLog::record($asset, 'asset.reported-lost', "Melaporkan aset {$asset->kode_barang} hilang");

        $this->clearCache();

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Aset berhasil dilaporkan hilang.');
    }

    public function markFound(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_ditemukan' => ['required', 'date', 'before_or_equal:today'],
            'lokasi_ditemukan' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $asset) {
            $locked = Asset::lockForUpdate()->findOrFail($asset->id);

            if ($locked->status !== 'Hilang') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya aset berstatus Hilang yang dapat ditandai ditemukan.',
                ]);
            }

            $locked->update(['status' => 'Tersedia']);

            $tanggal = \Carbon\Carbon::parse($validated['tanggal_ditemukan'])->format('d/m/Y');
            $deskripsi = "Aset ditemukan kembali di {$validated['lokasi_ditemukan']} pada {$tanggal}. Dikonfirmasi oleh " . (auth()->user()->name ?? 'petugas') . '.';
            if (! empty($validated['catatan'])) {
                $deskripsi .= " Catatan: {$validated['catatan']}";
            }

            AssetLog::create([
                'asset_id' => $locked->id,
                'tipe' => 'ditemukan',
                'deskripsi' => $deskripsi,
                'user_id' => auth()->id(),
            ]);
        });

        ActivityLog::record($asset, 'asset.marked-found', "Menandai aset {$asset->kode_barang} ditemukan kembali");

        $this->clearCache();

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Aset berhasil ditandai ditemukan.');
    }

    public function processDisposal(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_penghapusan' => ['required', 'date', 'before_or_equal:today'],
            'alasan' => ['required', 'string', 'in:Rusak Berat,Tidak Layak Digunakan,Usia Aset,Biaya Perbaikan Tidak Ekonomis,Hilang/Tidak Ditemukan,Lainnya'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $asset) {
            $locked = Asset::lockForUpdate()->findOrFail($asset->id);

            // Dipinjam dapat penolakan pesan khusus — harus dikembalikan dulu lewat
            // Pengembalian (TransactionController), bukan lewat sini, supaya Transaction
            // yang masih aktif tidak menggantung tanpa penutup. Sama alasannya dengan
            // guard di reportLost().
            if ($locked->status === 'Dipinjam') {
                throw ValidationException::withMessages([
                    'status' => 'Aset yang sedang dipinjam harus dikembalikan terlebih dahulu sebelum diproses penghapusan.',
                ]);
            }

            if (! in_array($locked->status, ['Tersedia', 'Perbaikan', 'Hilang'])) {
                throw ValidationException::withMessages([
                    'status' => 'Aset dengan status ' . $this->statusLabel($locked->status) . ' tidak dapat diproses penghapusan.',
                ]);
            }

            // Perbaikan bisa terjadi tanpa MaintenanceSchedule aktif (lewat reportDamage()),
            // tapi kalau memang ada schedule Dikerjakan, itu harus diselesaikan dulu — pola
            // guard yang sama dipakai TransactionController::store().
            if ($locked->maintenanceSchedules()->where('status', 'Dikerjakan')->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Aset sedang dalam perawatan aktif, selesaikan perawatan terlebih dahulu sebelum diproses penghapusan.',
                ]);
            }

            $locked->update(['status' => 'Disposed']);

            $tanggal = \Carbon\Carbon::parse($validated['tanggal_penghapusan'])->format('d/m/Y');
            $deskripsi = "Aset dihapuskan pada {$tanggal} karena {$validated['alasan']}. Diproses oleh " . (auth()->user()->name ?? 'petugas') . '.';
            if (! empty($validated['keterangan'])) {
                $deskripsi .= " Keterangan: {$validated['keterangan']}";
            }

            AssetLog::create([
                'asset_id' => $locked->id,
                'tipe' => 'penghapusan',
                'deskripsi' => $deskripsi,
                'user_id' => auth()->id(),
            ]);
        });

        ActivityLog::record($asset, 'asset.disposed', "Menghapuskan aset {$asset->kode_barang}");

        $this->clearCache();

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Aset berhasil diproses penghapusan.');
    }

    public function deleteFoto(Asset $asset): RedirectResponse
    {
        if ($asset->foto) {
            if (Storage::disk('public')->exists($asset->foto)) {
                Storage::disk('public')->delete($asset->foto);
            }
            $asset->update(['foto' => null]);

            ActivityLog::record($asset, 'asset.photo-deleted', "Menghapus foto aset {$asset->kode_barang}");

            return redirect()
                ->route('assets.edit', $asset)
                ->with('success', 'Foto aset berhasil dihapus.');
        }

        return redirect()
            ->route('assets.edit', $asset)
            ->with('error', 'Tidak ada foto untuk dihapus.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        if ($asset->status === 'Disposed') {
            return redirect()->route('assets.archive')->with('error', 'Aset yang sudah Dihapuskan harus tetap tersimpan di Arsip Aset untuk kebutuhan audit.');
        }

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

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        $assets = Asset::whereIn('id', $ids)->get();

        $deletedCodes = [];
        $skippedCodes = [];
        $photosToDelete = [];

        DB::transaction(function () use ($assets, &$deletedCodes, &$skippedCodes, &$photosToDelete) {
            foreach ($assets as $asset) {
                if ($asset->status === 'Disposed'
                    || $asset->status === 'Dipinjam'
                    || $asset->transactions()->where('status_peminjaman', 'Dipinjam')->exists()) {
                    $skippedCodes[] = $asset->kode_barang;
                    continue;
                }

                $assetCode = $asset->kode_barang;
                if ($asset->foto) {
                    $photosToDelete[] = $asset->foto;
                }

                $asset->delete();
                ActivityLog::record($asset, 'asset.deleted', "Menghapus aset {$assetCode}", ['kode_barang' => $assetCode]);
                $deletedCodes[] = $assetCode;
            }
        });

        foreach ($photosToDelete as $photo) {
            Storage::disk('public')->delete($photo);
        }

        $this->clearCache();

        if (empty($deletedCodes)) {
            return redirect()->route('assets.index')
                ->with('error', 'Tidak ada aset yang dihapus — semua aset yang dipilih sedang dipinjam: ' . implode(', ', $skippedCodes) . '.');
        }

        $message = count($deletedCodes) . ' aset berhasil dihapus (' . implode(', ', $deletedCodes) . ').';
        if (!empty($skippedCodes)) {
            $message .= ' ' . count($skippedCodes) . ' dilewati karena sedang dipinjam: ' . implode(', ', $skippedCodes) . '.';
        }

        return redirect()->route('assets.index')->with('success', $message);
    }

    public function exportPdf(Request $request): Response
    {
        $ids = $request->input('ids', []);
        $categoryId = $request->input('category_id');
        $locationId = $request->input('location_id');
        $kondisi = $request->input('kondisi');
        $status = $request->input('status');
        $search = $request->input('search');

        // Export Daftar Aset memakai query dasar yang SAMA dengan halaman index()
        // (activeAssetQuery) — dataset PDF selalu = dataset tabel untuk filter yang
        // sama. Saat user mengekspor baris terpilih (ids), filter halaman dilewati
        // (perilaku lama dipertahankan).
        $assets = $this->activeAssetQuery($request, empty($ids))
            ->when($ids, fn($q) => $q->whereIn('id', $ids))
            ->orderBy('kode_barang')
            ->get();

        $isSelection = !empty($ids);
        $filterCategory = (!$isSelection && $categoryId) ? Category::find($categoryId)?->nama : null;
        $filterLocation = (!$isSelection && $locationId) ? Location::find($locationId)?->nama : null;
        $filterKondisi = (!$isSelection && $kondisi) ? $kondisi : null;
        $filterStatus = (!$isSelection && $status) ? $this->statusLabel($status) : null;
        $filterSearch = (!$isSelection && $search) ? $search : null;

        $pdf = Pdf::loadView('pdf.assets', compact('assets', 'filterCategory', 'filterLocation', 'filterKondisi', 'filterStatus', 'filterSearch', 'isSelection'));

        return $pdf->download('laporan-aset.pdf');
    }

    public function exportCsv(Request $request): Response
    {
        $ids = $request->input('ids', []);

        // Query dasar yang SAMA dengan halaman index() (activeAssetQuery) — isi CSV
        // selalu = dataset tabel untuk filter yang sama, termasuk search/kondisi/
        // status. Export "data terpilih" (ids) tetap melewati filter halaman.
        $assets = $this->activeAssetQuery($request, empty($ids))
            ->when($ids, fn($q) => $q->whereIn('id', $ids))
            ->orderBy('kode_barang')
            ->get();

        return response()->streamDownload(function () use ($assets) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Barang', 'Nama Barang', 'Merk', 'Nomor Seri', 'Kategori', 'Lokasi', 'Kondisi', 'Status', 'Tahun Perolehan', 'Nilai Perolehan', 'Penanggung Jawab', 'Catatan']);
            foreach ($assets as $asset) {
                fputcsv($out, [
                    $asset->kode_barang,
                    $asset->nama_barang,
                    $asset->merk,
                    $asset->nomor_seri,
                    $asset->category?->nama,
                    $asset->location?->nama,
                    $asset->kondisi,
                    $this->statusLabel($asset->status),
                    $asset->tahun_perolehan,
                    $asset->nilai_perolehan,
                    $asset->penanggung_jawab,
                    $asset->catatan,
                ]);
            }
            fclose($out);
        }, 'data-aset-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
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
            /*
             * Dashboard = monitoring aset AKTIF. "total_asset", "total_nilai", dan
             * ketiga hitungan kondisi sengaja mengecualikan status "Disposed" supaya:
             *  - persentase "x% dari total" pada tiap kartu benar-benar berjumlah 100%,
             *  - bar kondisi tidak bisa melebihi 100% (baik/total),
             *  - nilai inventaris tidak menghitung aset yang sudah dihapuskan.
             * "disposed" tetap dihitung terpisah sebagai angka arsip (dipakai di chart
             * status & keterangan kartu Total Aset) — jadi tidak ada double count.
             */
            $assetStats = Asset::selectRaw('
                SUM(CASE WHEN status != ? THEN 1 ELSE 0 END) as total_asset,
                COALESCE(SUM(CASE WHEN status != ? THEN nilai_perolehan ELSE 0 END), 0) as total_nilai,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as tersedia,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as dipinjam,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as perbaikan,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as hilang,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as disposed,
                SUM(CASE WHEN kondisi = ? AND status != ? THEN 1 ELSE 0 END) as baik,
                SUM(CASE WHEN kondisi = ? AND status != ? THEN 1 ELSE 0 END) as kurang_baik,
                SUM(CASE WHEN kondisi = ? AND status != ? THEN 1 ELSE 0 END) as rusak_berat
            ', [
                'Disposed', 'Disposed',
                'Tersedia', 'Dipinjam', 'Perbaikan', 'Hilang', 'Disposed',
                'Baik', 'Disposed',
                'Kurang Baik', 'Disposed',
                'Rusak Berat', 'Disposed',
            ])
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
            // Konsisten dengan kartu statistik: kategori/lokasi pada dashboard
            // hanya menghitung aset AKTIF (status != 'Disposed'). Aset Dihapuskan
            // dihitung terpisah sebagai angka arsip, bukan bagian dari statistik
            // operasional — kalau ikut dihitung, chart kategori/lokasi bisa
            // menampilkan aset yang sudah tidak menjadi inventaris aktif.
            $kategori = Category::withCount(['assets' => fn($q) => $q->where('status', '!=', 'Disposed')])
                ->orderBy('nama')
                ->get();

            $kategoriLabels = $kategori->pluck('nama');
            $kategoriData = $kategori->pluck('assets_count');

            /*
            |--------------------------------------------------------------------------
            | Statistik Lokasi
            |--------------------------------------------------------------------------
            */
            $lokasi = Location::withCount(['assets' => fn($q) => $q->where('status', '!=', 'Disposed')])
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
                // "Aset Terbaru" = bagian dari statistik operasional dashboard, jadi
                // konsisten dengan definisi aset aktif di seluruh dashboard: hanya
                // status != 'Disposed'. Aset Dihapuskan hanya muncul di Arsip Aset.
                ->where('status', '!=', 'Disposed')
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
                'hilang' => $cached['assetStats']->hilang,
                'disposed' => $cached['assetStats']->disposed,
                'baik' => $cached['assetStats']->baik,
                'kurangBaik' => $cached['assetStats']->kurang_baik,
                'rusakBerat' => $cached['assetStats']->rusak_berat,
            ]
        ));
    }

    /**
     * Label Bahasa Indonesia untuk status internal. Internal value tetap "Disposed"
     * (enum DB, dipakai di semua query/validasi) — cuma tampilan ke user yang
     * diterjemahkan jadi "Dihapuskan", supaya tidak perlu migration/rename kolom.
     */
    private function statusLabel(string $status): string
    {
        return $status === 'Disposed' ? 'Dihapuskan' : $status;
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
        Cache::forget('all_assets_v3');
        // Notifikasi header ikut bergantung pada status/kondisi aset (lihat
        // HeaderNotificationComposer) — tanpa ini aset yang baru dihapuskan masih
        // nongol sebagai "Aset Rusak Berat" sampai cache-nya expired sendiri.
        Cache::forget('header_notifications');
    }
}
