@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pagination-wrapper">

        {{-- Mobile --}}
        <div class="flex items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 border border-[#E5E7EB] dark:border-gray-600 cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-[#111827] dark:text-gray-200 bg-white dark:bg-gray-800 border border-[#E5E7EB] dark:border-gray-600 hover:bg-[#F5F7FA] dark:hover:bg-gray-700 transition-colors">
                    {!! __('pagination.previous') !!}
                </a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-[#111827] dark:text-gray-200 bg-white dark:bg-gray-800 border border-[#E5E7EB] dark:border-gray-600 hover:bg-[#F5F7FA] dark:hover:bg-gray-700 transition-colors">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 border border-[#E5E7EB] dark:border-gray-600 cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-400 dark:text-gray-500">
                    Menampilkan
                    @if ($paginator->firstItem())
                        <span class="font-medium text-[#111827] dark:text-gray-200">{{ $paginator->firstItem() }}</span>
                        -
                        <span class="font-medium text-[#111827] dark:text-gray-200">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    dari
                    <span class="font-medium text-[#111827] dark:text-gray-200">{{ $paginator->total() }}</span>
                    data
                </p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1">
                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 border border-[#E5E7EB] dark:border-gray-600 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-9 h-9 text-sm text-[#111827] dark:text-gray-200 bg-white dark:bg-gray-800 border border-[#E5E7EB] dark:border-gray-600 hover:bg-[#F5F7FA] dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Elements --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 border border-[#E5E7EB] dark:border-gray-600">{{ $element }}</span>
                        @endif
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="inline-flex items-center justify-center w-9 h-9 text-sm font-semibold text-white bg-primary border border-primary dark:bg-primary-600 dark:border-primary-600">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center justify-center w-9 h-9 text-sm text-[#111827] dark:text-gray-200 bg-white dark:bg-gray-800 border border-[#E5E7EB] dark:border-gray-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:border-primary dark:hover:border-primary-500 hover:text-primary dark:hover:text-primary-400 transition-all" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-9 h-9 text-sm text-[#111827] dark:text-gray-200 bg-white dark:bg-gray-800 border border-[#E5E7EB] dark:border-gray-600 hover:bg-[#F5F7FA] dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 border border-[#E5E7EB] dark:border-gray-600 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
