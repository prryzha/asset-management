@php
    // Pesan abort custom (mis. abort(403, 'Akses tidak diizinkan.') dari
    // CheckRole) tetap ditampilkan. Pesan bawaan framework yang berbahasa
    // Inggris disembunyikan dan diganti deskripsi generik Bahasa Indonesia.
    $customMessage = $exception?->getMessage();
    $defaultEnglish = ['Forbidden', 'This action is unauthorized.', 'You are not authorized to access this page.'];
    $showMessage = is_string($customMessage) && $customMessage !== '' && ! in_array($customMessage, $defaultEnglish);
@endphp

@include('errors.layout', [
    'code' => '403',
    'title' => 'Akses Tidak Diizinkan',
    'description' => 'Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi admin jika menurut Anda ini sebuah kesalahan.',
    'message' => $showMessage ? $customMessage : null,
    'iconBg' => 'bg-danger-600',
    'iconPath' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
])
