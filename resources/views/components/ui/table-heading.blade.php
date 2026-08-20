@props([
    'title' => '',
])

{{-- Sits as the first child inside the table's own .card — title left,
     table actions (export/create/etc.) right, divider below before the
     filter toolbar. Reuses .card-header (already used by the
     notification/modal headers elsewhere) for the box styling, plus
     .table-heading on top for the column→row responsive behavior a plain
     nowrap .card-header doesn't have (needed here since there can be up to
     5 toolbar buttons next to the title). --}}
<div class="card-header table-heading">
    <h2 class="ui-title">{{ $title }}</h2>
    @if($slot->isNotEmpty())
        <div class="flex items-center flex-wrap gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
