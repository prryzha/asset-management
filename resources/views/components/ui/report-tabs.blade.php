@props(['tabs' => []])

<div class="flex items-center gap-1 border-b border-[#E5E7EB] dark:border-gray-700 mb-6">
    @foreach($tabs as $tab)
    <a href="{{ $tab['url'] }}"
       class="px-3.5 py-2.5 text-sm font-normal border-b-2 -mb-px transition-colors {{ $tab['active'] ? 'border-primary-600 text-primary-700 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
        {{ $tab['label'] }}
    </a>
    @endforeach
</div>
