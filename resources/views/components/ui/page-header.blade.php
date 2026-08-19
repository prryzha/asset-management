@props([
    'title' => '',
    'subtitle' => '',
    'actions' => null,
])

<div class="page-header">
    @if(isset($breadcrumbs))
    <div class="breadcrumb">
        @foreach($breadcrumbs as $breadcrumb)
            @if(isset($breadcrumb['url']))
                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                <span class="separator">/</span>
            @else
                <span class="current">{{ $breadcrumb['label'] }}</span>
            @endif
        @endforeach
    </div>
    @endif

    <div class="page-title-wrapper">
        <div>
            <h1 class="page-title ui-title">{{ $title }}</h1>
            @if($subtitle)
                <p class="page-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if($actions)
            <div class="flex items-center gap-3">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>