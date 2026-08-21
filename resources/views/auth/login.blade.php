<x-guest-layout>

    @if(session('status'))
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div class="form-group">
            <label class="form-label">{{ __('ui.auth.email') }}</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="form-input @error('email') is-invalid @enderror">
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Password --}}
        <div class="form-group mt-4">
            <label class="form-label">{{ __('ui.auth.password') }}</label>
            <input
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="form-input @error('password') is-invalid @enderror">
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Remember Me --}}
        <div class="flex items-center justify-between mt-6">

            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input
                    type="checkbox"
                    name="remember"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                    id="remember_me">
                <span class="text-xs text-gray-600">{{ __('ui.auth.remember_me') }}</span>
            </label>

            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-xs font-normal text-primary-600 hover:text-primary-700">
                    {{ __('ui.auth.forgot_password') }}
                </a>
            @endif

        </div>

        {{-- Submit --}}
        <div class="mt-8">
            <button class="btn-primary w-full">
                {{ __('ui.auth.login') }}
            </button>
        </div>

    </form>

</x-guest-layout>
