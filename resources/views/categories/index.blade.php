@extends('layouts.app')

@section('title', __('ui.categories.title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.categories.title')" />

    <div class="card overflow-hidden">
        <x-ui.table-heading :title="__('ui.categories.daftar_kategori')">
            <a href="{{ route('categories.create') }}" class="btn-primary btn-icon" title="{{ __('ui.categories.tambah_kategori') }}" aria-label="{{ __('ui.categories.tambah_kategori') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </a>
        </x-ui.table-heading>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('ui.categories.kode') }}</th>
                        <th>{{ __('ui.categories.nama_kategori') }}</th>
                        <th class="text-center">{{ __('ui.categories.jumlah_aset') }}</th>
                        <th class="text-center">{{ __('ui.common.aksi') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>
                            <span class="font-mono text-secondary">{{ $category->kode ?? '—' }}</span>
                        </td>
                        <td class="font-normal">{{ $category->nama }}</td>
                        <td class="text-center">{{ $category->assets_count }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('categories.edit', $category) }}"
                                   class="btn-ghost btn-icon" title="{{ __('ui.common.ubah') }}" aria-label="{{ __('ui.common.ubah') }} {{ $category->nama }}">
                                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('categories.destroy', $category) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('ui.categories.confirm_delete', ['nama' => $category->nama])) }})">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-icon text-danger hover:text-white hover:bg-danger" title="{{ __('ui.common.hapus') }}" aria-label="{{ __('ui.common.hapus') }} {{ $category->nama }}">
                                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-16">
                            <x-ui.empty-state
                                icon="folder"
                                :title="__('ui.categories.empty_title')"
                                :description="__('ui.categories.empty_desc')" />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="px-5 py-3 border-t border-default">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
