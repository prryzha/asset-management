<x-guest-layout>

    <h2 class="text-sm font-normal text-gray-900 mb-1.5">{{ __('ui.auth.forgot_password_title') }}</h2>
    <p class="text-xs text-gray-600 mb-6">
        {{ __('ui.auth.forgot_password_desc') }}
    </p>

    @if(session('status'))
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
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
                class="form-input @error('email') is-invalid @enderror">
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Submit --}}
        <div class="mt-8">
            <button class="btn-primary w-full">
                {{ __('ui.auth.send_reset_link') }}
            </button>
        </div>

    </form>

</x-guest-layout>
