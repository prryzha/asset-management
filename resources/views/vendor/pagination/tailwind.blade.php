@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pagination-wrapper">

        {{-- Mobile --}}
        <div class="pagination-mobile">
            @if ($paginator->onFirstPage())
                <span class="pagination-btn pagination-btn-disabled">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-btn">
                    {!! __('pagination.previous') !!}
                </a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-btn">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="pagination-btn pagination-btn-disabled">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="pagination-desktop">
            <div>
                <p class="pagination-info">
                    {{ __('ui.pagination.showing') }}
                    @if ($paginator->firstItem())
                        <strong>{{ $paginator->firstItem() }}</strong>
                        -
                        <strong>{{ $paginator->lastItem() }}</strong>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {{ __('ui.pagination.of') }}
                    <strong>{{ $paginator->total() }}</strong>
                    {{ __('ui.pagination.data') }}
                </p>
            </div>
            <div>
                <span class="pagination-controls">
                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <span class="pagination-link pagination-disabled" title="{{ __('ui.pagination.previous_page') }}" aria-label="{{ __('ui.pagination.previous_page') }}" aria-disabled="true">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-link" title="{{ __('ui.pagination.previous_page') }}" aria-label="{{ __('ui.pagination.previous_page') }}">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Elements --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="pagination-ellipsis">{{ $element }}</span>
                        @endif
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="pagination-page pagination-page-active">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="pagination-page" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-link" title="{{ __('ui.pagination.next_page') }}" aria-label="{{ __('ui.pagination.next_page') }}">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="pagination-link pagination-disabled" title="{{ __('ui.pagination.next_page') }}" aria-label="{{ __('ui.pagination.next_page') }}" aria-disabled="true">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
