@props([
    'tabs',
    'activeTab' => 0,
])

@php
    $componentId = 'tabs-' . uniqid();
@endphp

<div class="nav-tabs-top nav-responsive-sm">
    <ul class="nav nav-tabs">
        @foreach($tabs as $tabIndex => $tabTitle)
            <li class="nav-item">
                <a class="nav-link{{ $tabIndex == $activeTab ? ' active' : '' }}" data-toggle="tab"
                   href="#{{ $componentId }}-tab-{{ $tabIndex }}">{{ $tabTitle }}</a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach($tabs as $tabIndex => $tabTitle)
            @php
                $tabSlot = str('tab-' . $tabTitle)->slug()->camel();
            @endphp
            <div class="tab-pane {{ $tabIndex == $activeTab ? ' active' : '' }}"
                 id="{{ $componentId }}-tab-{{ $tabIndex }}">
                {{ $$tabSlot }}
            </div>
        @endforeach
    </div>
</div>
