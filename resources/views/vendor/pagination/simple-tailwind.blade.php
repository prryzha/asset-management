@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pagination-simple">

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

    </nav>
@endif
