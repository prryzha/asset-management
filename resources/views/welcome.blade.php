<x-layouts.welcome-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-primary-800">
        <div class="text-center px-6">
            <div class="mb-8">
                <div class="w-20 h-20 mx-auto bg-white/10 flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">Asset Management</h1>
            <p class="text-lg text-primary-200 mb-8">Sistem Informasi Inventaris & Peminjaman Aset Sekolah</p>
            <div class="flex justify-center">
                <a href="{{ route('login') }}" class="px-6 py-2.5 bg-white text-primary-700 font-semibold hover:bg-gray-100 transition-colors text-sm">
                    Login
                </a>
            </div>
        </div>
    </div>
</x-layouts.welcome-layout>
