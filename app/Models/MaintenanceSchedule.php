<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'created_by',
        'jenis_perawatan',
        'teknisi',
        'tanggal_jadwal',
        'tanggal_selesai',
        'catatan',
        'catatan_selesai',
        'biaya',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_jadwal' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
