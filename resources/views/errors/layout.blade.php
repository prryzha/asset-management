{{-- Layout bersama untuk halaman error kustom (403/404/500) — styling konsisten
     dengan halaman login (guest.blade.php): kartu tengah + radial background.
     Variabel datang dari @include di tiap halaman error. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', __('ui.auth.app_name')) }} - {{ $title ?? __('ui.errors.default_title') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="antialiased">

    <div class="auth-page">

        {{-- Soft accent blobs --}}
        <div class="auth-blob auth-blob-top"></div>
        <div class="auth-blob auth-blob-bottom"></div>

        <div class="auth-card text-center">

            <div class="{{ $iconBg ?? 'auth-logo' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath ?? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' }}"/>
                </svg>
            </div>

            <div class="auth-error-code">{{ $code ?? '' }}</div>

            <h1 class="auth-error-title">{{ $title ?? __('ui.errors.default_error_title') }}</h1>

            @if(!empty($message))
                <p class="auth-error-message">{{ $message }}</p>
            @endif

            <p class="auth-error-message">{{ $description ?? '' }}</p>

            <a href="{{ url('/') }}" class="auth-back-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('ui.errors.back_to_home') }}
            </a>

        </div>

    </div>

</body>
</html>
