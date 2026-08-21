@extends('layouts.app')

@section('title', __('ui.users.title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.users.title')" />

    <div class="card overflow-hidden">
        <x-ui.table-heading :title="__('ui.users.daftar_pengguna')">
            <a href="{{ route('users.create') }}" class="btn-primary btn-icon" title="{{ __('ui.users.tambah_user') }}" aria-label="{{ __('ui.users.tambah_user') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </a>
        </x-ui.table-heading>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('ui.profile.nama_lengkap') }}</th>
                        <th>{{ __('ui.profile.nama_pengguna') }}</th>
                        <th>{{ __('ui.auth.email') }}</th>
                        <th>{{ __('ui.profile.peran') }}</th>
                        <th>{{ __('ui.users.bergabung') }}</th>
                        <th class="text-center">{{ __('ui.common.aksi') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($user->foto_profil)
                                    <img src="{{ asset('storage/'.$user->foto_profil) }}" alt="{{ $user->name }}" class="header-user-avatar object-cover">
                                @else
                                    <div class="header-user-avatar">{{ substr($user->name, 0, 1) }}</div>
                                @endif
                                <span class="font-normal">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="text-secondary">{{ $user->username ?? '—' }}</td>
                        <td class="text-secondary">{{ $user->email }}</td>
                        <td>
                            @php
                                $roleBadge = [
                                    'admin' => 'badge-subtle-purple',
                                    'staff' => 'badge-subtle-primary',
                                ];
                                $roleLabel = [
                                    'admin' => __('ui.users.badge_admin'),
                                    'staff' => __('ui.users.badge_staff'),
                                ];
                            @endphp
                            <span class="badge-subtle {{ $roleBadge[$user->role] ?? 'badge-subtle-neutral text-secondary' }}">
                                {{ $roleLabel[$user->role] ?? $user->role }}
                            </span>
                        </td>
                        <td class="text-xs text-secondary">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div class="table-actions">
                                <a href="{{ route('users.edit', $user) }}" class="btn-ghost btn-icon" title="{{ __('ui.common.ubah') }}" aria-label="{{ __('ui.common.ubah') }} {{ $user->name }}">
                                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="delete-user-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-icon text-danger hover:text-white hover:bg-danger" title="{{ __('ui.common.hapus') }}" aria-label="{{ __('ui.common.hapus') }} {{ $user->name }}">
                                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @else
                                <span class="inline-flex items-center justify-center btn-icon rounded-md text-secondary/70 cursor-default" title="{{ __('ui.users.no_self_delete') }}" aria-label="{{ __('ui.users.no_self_delete') }}">
                                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-16">
                            <x-ui.empty-state
                                icon="users"
                                :title="__('ui.users.empty_title')"
                                :description="__('ui.users.empty_desc')" />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-5 py-3 border-t border-default">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.delete-user-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: @json(__('ui.users.confirm_delete_title')),
            text: @json(__('ui.users.confirm_delete_text')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: @json(__('ui.common.ya_hapus')),
            cancelButtonText: @json(__('ui.common.batal'))
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
