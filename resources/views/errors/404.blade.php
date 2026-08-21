@php
    // Halaman tidak ditemukan — pesan abort custom (mis. abort(404, 'Aset tidak
    // ditemukan')) tetap ditampilkan; pesan bawaan framework disembunyikan.
    $customMessage = $exception?->getMessage();
    $defaultEnglish = ['Not Found', 'Page not found.'];
    $showMessage = is_string($customMessage) && $customMessage !== '' && ! in_array($customMessage, $defaultEnglish);
@endphp

@include('errors.layout', [
    'code' => '404',
    'title' => __('ui.errors.not_found_title'),
    'description' => __('ui.errors.not_found_description'),
    'message' => $showMessage ? $customMessage : null,
    'iconBg' => 'bg-warning-500',
    'iconPath' => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM13 10H7',
])
