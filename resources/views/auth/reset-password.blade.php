<x-guest-layout>

    <h2 class="text-lg font-normal text-gray-900 mb-1.5">Atur Ulang Password</h2>
    <p class="text-sm text-gray-600 mb-6">
        Masukkan password baru untuk akun Anda.
    </p>

    @if(session('status'))
        <div class="alert alert-success mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        {{-- Token reset password --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div class="form-group">
            <label class="form-label">Email</label>
            <input
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                required
                autofocus
                autocomplete="username"
                class="form-input @error('email') is-invalid @enderror">
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Password Baru --}}
        <div class="form-group mt-5">
            <label class="form-label">Password Baru</label>
            <input
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="form-input @error('password') is-invalid @enderror">
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Konfirmasi Password Baru --}}
        <div class="form-group mt-5">
            <label class="form-label">Konfirmasi Password Baru</label>
            <input
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="form-input @error('password_confirmation') is-invalid @enderror">
            @error('password_confirmation')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Submit --}}
        <div class="mt-8">
            <button class="btn-primary w-full">
                Atur Ulang Password
            </button>
        </div>

    </form>

</x-guest-layout>
