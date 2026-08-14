@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="p-8">

    <x-ui.page-header title="Manajemen User" subtitle="Kelola akun pengguna sistem.">
        <x-slot:actions>
            <a href="{{ route('users.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Tambah User
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-50 dark:bg-primary-900/30 text-primary dark:text-primary-300 flex items-center justify-center font-normal text-xs">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <span class="font-normal">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="text-secondary">{{ $user->email }}</td>
                        <td>
                            @php
                                $roleBadge = [
                                    'admin' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                    'staff' => 'bg-primary-50 text-primary dark:bg-primary-900/30 dark:text-primary-300',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-normal {{ $roleBadge[$user->role] ?? 'bg-gray-100 text-secondary dark:bg-gray-700' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="text-sm text-secondary">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('users.edit', $user) }}" class="btn-ghost btn-sm px-2 py-1 text-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>
                                <div class="w-[78px] flex justify-center">
                                @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="delete-user-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm px-2 py-1 text-xs text-danger hover:text-white hover:bg-danger">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                                @else
                                <span class="inline-flex items-center gap-2 px-2 py-1 text-xs text-secondary/70 cursor-default" title="Ini akun Anda, tidak bisa dihapus sendiri">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Anda
                                </span>
                                @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-16">
                            <x-ui.empty-state
                                icon="users"
                                title="Belum Ada User"
                                description="Belum ada pengguna terdaftar. Silakan tambah user baru." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.delete-user-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Hapus User?',
            text: 'User yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
