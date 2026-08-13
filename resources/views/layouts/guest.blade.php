<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Manajemen Aset') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font loading — hanya weight yang dipakai --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="antialiased">

    <div class="min-h-screen relative flex flex-col items-center justify-center bg-gray-50 px-4 py-12 overflow-hidden"
         style="background-image: radial-gradient(#e5e7eb 1px, transparent 1px); background-size: 24px 24px;">

        {{-- Soft accent blobs — hidupin background tanpa keluar dari warna brand --}}
        <div class="pointer-events-none absolute -top-24 -left-24 w-96 h-96 bg-primary-200/50 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-16 w-[28rem] h-[28rem] bg-primary-100 rounded-full blur-3xl"></div>

        <div class="relative w-full sm:max-w-md">

            {{-- Logo & Title --}}
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto bg-primary-600 rounded-xl shadow-lg shadow-primary-600/20 flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mt-4">Manajemen Aset</h1>
                <p class="text-gray-500 text-sm mt-1">Sistem Informasi Inventaris & Peminjaman Aset Sekolah</p>
            </div>

            {{-- Card --}}
            <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-8">
                {{ $slot }}
            </div>

        </div>

    </div>

</body>
</html>
