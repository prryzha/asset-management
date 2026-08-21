{{-- Status Aset: daftar sederhana, bukan chart — lebih cepat dibaca untuk 5 kategori --}}
@php
    $statusTotal = $totalAsset + $disposed;
    $statusRows = [
        ['label' => __('ui.status.Tersedia'), 'value' => $tersedia, 'bar' => 'bg-success'],
        ['label' => __('ui.status.Dipinjam'), 'value' => $dipinjam, 'bar' => 'bg-warning'],
        ['label' => __('ui.status.Perbaikan'), 'value' => $perbaikan, 'bar' => 'bg-danger'],
        ['label' => __('ui.status.Hilang'), 'value' => $hilang, 'bar' => 'bg-gray-400 dark:bg-gray-500'],
        ['label' => __('ui.status.Disposed'), 'value' => $disposed, 'bar' => 'bg-gray-300 dark:bg-gray-600'],
    ];
@endphp
<div class="card">
    <div class="card-header">
        <h3>{{ __('ui.dashboard.status_aset') }}</h3>
    </div>
    <div class="card-body space-y-3">
        @foreach($statusRows as $row)
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs text-gray-700 dark:text-gray-300">{{ $row['label'] }}</span>
                <span class="text-xs text-gray-900 dark:text-gray-100">{{ $row['value'] }}</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5">
                <div class="{{ $row['bar'] }} h-1.5" style="width: {{ $statusTotal > 0 ? ($row['value'] / $statusTotal) * 100 : 0 }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
