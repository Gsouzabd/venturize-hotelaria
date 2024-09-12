@props([
    'name',
    'class' => '',
    'items' => [],
    'selectedItems' => [],
    'moveOnSelect' => false,
    'hideFilterInputs' => false,
])

<select name="{{ $name }}" class="form-duallistbox{{ $class ? ' ' . $class : '' }}" multiple size="10">
    @foreach($items as $itemKey => $item)
        <option
                value="{{ $itemKey }}"{{ (in_array($itemKey, $selectedItems, true) || in_array((string) $itemKey, $selectedItems, true)) ? ' selected' : '' }}>
            {{ $item }}
        </option>
    @endforeach
</select>

@pushonce('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/bootstrap-duallistbox/bootstrap-duallistbox.css') }}">
@endpushonce

@pushonce('scripts')
    <script src="{{ asset('assets/admin/vendor/bootstrap-duallistbox/bootstrap-duallistbox.js') }}"></script>
    <script>
        $(function () {
            $(".form-duallistbox").bootstrapDualListbox({
                filterTextClear: 'mostrar todos',
                filterPlaceHolder: 'Filtrar',
                moveSelectedLabel: 'Mover selecionado',
                moveAllLabel: 'Mover todos',
                removeSelectedLabel: 'Remover selecionado',
                removeAllLabel: 'Remover todos',
                moveOnSelect: @json($moveOnSelect),
                preserveSelectionOnMove: false,
                selectedListLabel: 'Itens selecionados',
                nonSelectedListLabel: 'Itens',
                helperSelectNamePostfix: 'lista',
                selectorMinimalHeight: 200,
                infoText: 'Mostrando {0} itens',
                infoTextFiltered: '<span class="badge badge-warning">Filtrado</span> {0} de {1}',
                infoTextEmpty: 'Lista vazia',
                sortByInputOrder: true,
                showFilterInputs: @json(!$hideFilterInputs)
            });
        });
    </script>
@endpushonce
