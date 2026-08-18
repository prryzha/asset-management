<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'merk',
        'nomor_seri',
        'category_id',
        'location_id',
        'penanggung_jawab',
        'kondisi',
        'status',
        'tahun_perolehan',
        'nilai_perolehan',
        'catatan',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'tahun_perolehan' => 'integer',
            'nilai_perolehan' => 'decimal:2',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function assetLogs(): HasMany
    {
        return $this->hasMany(AssetLog::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? Storage::disk('public')->url($this->foto) : null;
    }

    public static function findNextKodeBarang(string $prefix): string
    {
        $existing = self::where('kode_barang', 'like', $prefix . '-%')
            ->pluck('kode_barang')
            ->map(fn($k) => (int) substr($k, strlen($prefix) + 1))
            ->sort()
            ->values();

        $next = 1;
        foreach ($existing as $i => $num) {
            if ($num !== $i + 1) {
                $next = $i + 1;
                break;
            }
            $next = $num + 1;
        }

        return $prefix . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate $count kode barang berurutan untuk fitur tambah banyak unit sekaligus.
     * Mulai dari slot kosong pertama (reuse findNextKodeBarang), lalu jalan berurutan.
     */
    public static function nextSequentialCodes(string $prefix, int $count): array
    {
        $start = (int) substr(self::findNextKodeBarang($prefix), strlen($prefix) + 1);
        $existing = self::where('kode_barang', 'like', $prefix . '-%')->pluck('kode_barang')->flip();

        $codes = [];
        $n = $start;
        while (count($codes) < $count) {
            $candidate = $prefix . '-' . str_pad($n, 3, '0', STR_PAD_LEFT);
            if (! $existing->has($candidate)) {
                $codes[] = $candidate;
            }
            $n++;
        }

        return $codes;
    }

    public static function generateKodeBarang(Category $category): string
    {
        $kodeKategori = $category->kode ?? strtoupper(substr($category->nama, 0, 3));
        $tahun = now()->format('Y');
        $prefix = $kodeKategori . '-' . $tahun . '-';

        $last = self::where('kode_barang', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('kode_barang');

        $nextSeq = 1;
        if ($last) {
            $parts = explode('-', $last);
            $lastNum = (int) end($parts);
            $nextSeq = $lastNum + 1;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cache yang bergantung pada status/kondisi aset — dashboard, dropdown
     * "Catat Peminjaman" (available_assets), dropdown pilih-aset Perawatan
     * (all_assets_v3), dan notifikasi header (header_notifications). Dipakai
     * bersama oleh AssetController, TransactionController, dan
     * MaintenanceScheduleController supaya ketiganya tidak bisa saling lupa
     * meng-invalidate cache yang sama setelah mengubah status aset — sebelum
     * ini, hanya AssetController yang membersihkannya (lihat audit pre-demo:
     * peminjaman/pengembalian/perawatan bisa meninggalkan dashboard & dropdown
     * aset basi sampai 5 menit, atau sampai 1 jam untuk all_assets_v3).
     */
    public static function forgetStatusCaches(): void
    {
        Cache::forget('dashboard_data');
        Cache::forget('filter_categories');
        Cache::forget('filter_locations');
        Cache::forget('available_assets');
        Cache::forget('all_assets');
        Cache::forget('all_assets_v2');
        Cache::forget('all_assets_v3');
        Cache::forget('header_notifications');
    }
}
