{{-- 500 — jangan pernah menampilkan detail error/stack trace ke pengguna. --}}
@include('errors.layout', [
    'code' => '500',
    'title' => 'Terjadi Kesalahan Server',
    'description' => 'Terjadi kesalahan pada server. Silakan coba lagi beberapa saat, atau hubungi admin jika masalah berlanjut.',
    'message' => null,
    'iconBg' => 'bg-danger-600',
    'iconPath' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
])
