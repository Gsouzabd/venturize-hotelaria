@props([
    'route',
    'routeParams' => [],
    'size' => 2,
    'hideClear' => false,
])

<form action="{{ route($route, $routeParams) }}" method="get" class="filters-form">
    <div class="ui-bordered px-4 pt-4 mb-0">
        <div class="form-row align-items-center">
            {{ $slot }}

            <div class="{{ 'col-xl-' . $size }} mb-4">
                @if($hideClear)
                    <label class="form-label d-none d-xl-block">&nbsp;</label>
                    <button type="submit" class="btn btn-secondary btn-block">Filtrar</button>
                @else
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div class="w-50" style="padding-right: 5px">
                            <label class="form-label d-none d-lg-block">&nbsp;</label>
                            <button type="submit" class="btn btn-secondary btn-block">Filtrar</button>
                        </div>

                        <div class="w-50" style="padding-left: 5px">
                            <label class="form-label d-none d-xl-block">&nbsp;</label>
                            <a href="{{ route($route, $routeParams) }}"
                               class="btn btn-secondary btn-block reset-filters-btn">Limpar</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</form>
