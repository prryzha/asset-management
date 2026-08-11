<x-guest-layout>

    @if(session('status'))
        <div class="mb-5 p-4 bg-primary-50 text-primary-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div>
            <label class="block font-semibold mb-2 text-gray-700">
                Email
            </label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="w-full rounded border-gray-300">

            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mt-5">
            <label class="block font-semibold mb-2 text-gray-700">
                Password
            </label>
            <input
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="w-full rounded border-gray-300">

            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember Me --}}
        <div class="flex items-center justify-between mt-6">

            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input
                    type="checkbox"
                    name="remember"
                    class="border-gray-300 text-primary-600 focus:ring-primary-500"
                    id="remember_me">
                <span class="text-sm text-gray-600">Remember me</span>
            </label>

            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-sm text-primary-600 hover:text-primary-800 underline">
                    Lupa password?
                </a>
            @endif

        </div>

        {{-- Submit --}}
        <div class="mt-8">
            <button
                class="w-full px-3 py-2.5 bg-primary-600 text-white font-semibold hover:bg-primary-700 transition-colors duration-100 text-sm">
                Login
            </button>
        </div>

    </form>

</x-guest-layout>
