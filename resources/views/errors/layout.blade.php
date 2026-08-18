{{-- Layout bersama untuk halaman error kustom (403/404/500) — styling konsisten
     dengan halaman login (guest.blade.php): kartu tengah + radial background.
     Variabel datang dari @include di tiap halaman error. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Manajemen Aset') }} - {{ $title ?? 'Kesalahan' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="antialiased">

    <div class="min-h-screen relative flex flex-col items-center justify-center bg-gray-50 px-4 py-12 overflow-hidden"
         style="background-image: radial-gradient(#e5e7eb 1px, transparent 1px); background-size: 24px 24px;">

        {{-- Soft accent blobs — hidupin background tanpa keluar dari warna brand --}}
        <div class="pointer-events-none absolute -top-24 -left-24 w-96 h-96 bg-primary-200/50 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-16 w-[28rem] h-[28rem] bg-primary-100 rounded-full blur-3xl"></div>

        <div class="relative w-full sm:max-w-md text-center">

            <div class="w-16 h-16 mx-auto {{ $iconBg ?? 'bg-primary-600' }} rounded-xl shadow-lg shadow-primary-600/10 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath ?? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' }}"/>
                </svg>
            </div>

            <div class="text-6xl font-normal text-gray-300 mt-6">{{ $code ?? '' }}</div>

            <h1 class="text-2xl font-normal text-gray-900 mt-2">{{ $title ?? 'Terjadi Kesalahan' }}</h1>

            @if(!empty($message))
                <p class="text-gray-500 text-sm mt-3">{{ $message }}</p>
            @endif

            <p class="text-gray-500 text-sm mt-3">{{ $description ?? '' }}</p>

            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-2 mt-8 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Kembali ke Beranda
            </a>

        </div>

    </div>

</body>
</html>
