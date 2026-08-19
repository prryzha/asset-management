@props(['tabs' => []])

<div class="report-tabs">
    @foreach($tabs as $tab)
    <a href="{{ $tab['url'] }}"
       class="report-tab {{ $tab['active'] ? 'report-tab-active' : '' }}">
        {{ $tab['label'] }}
    </a>
    @endforeach
</div>
