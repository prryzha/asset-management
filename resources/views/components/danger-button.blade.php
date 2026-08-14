<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-danger-600 border border-transparent font-normal text-xs text-white uppercase tracking-wider hover:bg-danger-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 transition-colors duration-100']) }}>
    {{ $slot }}
</button>
