@props([
    'name',
    'class' => '',
    'value' => '',
    'disabled' => false,
])

<div class="input-group">
    <input type="text"
           name="{{ $name }}"
           class="form-daterangepicker daterange-mask form-control{{ $class ? ' ' . $class : '' }}"
           value="{{ $value }}"{{ $disabled ? ' disabled' : '' }}>
    <div class="input-group-append">
        <span class="input-group-text">
            <i class="fas fa-calendar"></i>
        </span>
    </div>
</div>

@pushonce('styles')
    <link rel="stylesheet"
          href="{{ asset('assets/admin/vendor/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}">
@endpushonce

@pushonce('scripts')
    <script src="{{ asset('assets/admin/vendor/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script>
        $(function () {
            $('.form-daterangepicker').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: "DD/MM/YYYY",
                    separator: " - ",
                    applyLabel: "Aplicar",
                    cancelLabel: "Limpar",
                    fromLabel: "De",
                    toLabel: "Até",
                    customRangeLabel: "Customizado",
                    weekLabel: "S",
                    daysOfWeek: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
                    monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                    firstDay: 0
                }
            }).on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            }).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                $(this).data('daterangepicker').setStartDate(new Date().getDate().toString())
                $(this).data('daterangepicker').setEndDate(new Date().getDate().toString())
            });
        });
    </script>
@endpushonce
