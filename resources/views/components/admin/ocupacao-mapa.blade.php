@php
use App\Models\Reserva;
use Illuminate\Support\Str;

@endphp

{{-- Overlay de loading para drag-and-drop --}}
<div id="mapa-loading-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center; flex-direction:column; gap:12px;">
    <div style="width:52px; height:52px; border:5px solid #fff; border-top-color:transparent; border-radius:50%; animation:mapa-spin .7s linear infinite;"></div>
    <span style="color:#fff; font-size:1rem; font-weight:600; letter-spacing:.5px;">Movendo reserva…</span>
</div>
<style>
    @keyframes mapa-spin { to { transform: rotate(360deg); } }
</style>

<div class="filters">
    <form method="GET" action="{{ $action }}">
        <div class="row">
            <div class="col-md-3">
                <label>Data Inicial</label>
                <input type="date" name="data_inicial" value="{{ $dataInicial->format('Y-m-d') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Intervalo</label>
                <select name="intervalo" class="form-control">
                    <option value="7" {{ $intervaloDias == 7 ? 'selected' : '' }}>Mostrar 7 dias</option>
                    <option value="15" {{ $intervaloDias == 15 ? 'selected' : '' }}>Mostrar 15 dias</option>
                    <option value="30" {{ $intervaloDias == 30 ? 'selected' : '' }}>Mostrar 30 dias</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
            </div>
        </div>
    </form>
</div>

<!-- Legenda -->
<div class="mt-4">
    <h5>Legenda de Situações</h5>
    <ul class="list-inline">
        @foreach (Reserva::SITUACOESRESERVA as $situacao)
            <li class="list-inline-item">
                <span class="badge" style="background-color: {{ $situacao['background'] }};">&nbsp;&nbsp;</span> {{ $situacao['label'] }}
            </li>
        @endforeach
    </ul>
</div>

<div class="reservations-calendar mt-4">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>UH</th>
                @for($i = 0; $i < $intervaloDias; $i++)
                    <th>{{ $dataInicial->copy()->addDays($i)->format('d/m') }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($quartos ?? [] as $quarto)
                <tr>
                    <td class="quarto">{{ $quarto->referencia ?? $quarto->numero ?? '' }}</td>
                        @for($i = 0; $i < ($intervaloDias ?? 0); $i++)
                            @php
                                $diaAtual = ($dataInicial ?? now())->copy()->addDays($i)->toDateString();
                                $reservasNoDia = ($reservas ?? collect())->filter(function($reserva) use ($quarto, $diaAtual) {
                                    $dataCheckin = \Carbon\Carbon::parse($reserva->data_checkin)->toDateString();
                                    $dataCheckout = \Carbon\Carbon::parse($reserva->data_checkout)->toDateString();

                                    return ($reserva->quarto_id ?? 0) == ($quarto->id ?? 0) &&
                                           $dataCheckin <= $diaAtual &&
                                           $dataCheckout >= $diaAtual;
                                });

           
                                // Definir a cor da célula com base na situação da reserva
                                $cor = '';
    
                            @endphp
                            @if(count($reservasNoDia) > 0)
                                <td style="padding: 0px; height: 30px;" data-date="{{ $diaAtual }}" data-quarto-id="{{ $quarto->id }}">
                                    @foreach($reservasNoDia->sortBy('data_checkin') as $reservaNoDia)

                                        @php
                                            $cor = '';
                                            $cor = Reserva::SITUACOESRESERVA[$reservaNoDia->situacao_reserva]['background'] ?? '';

                                            $dataCheckin = \Carbon\Carbon::parse($reservaNoDia->data_checkin)->toDateString();
                                            $dataCheckout = \Carbon\Carbon::parse($reservaNoDia->data_checkout)->toDateString();

                                            $titular = $reservaNoDia->clienteResponsavel
                                                ? $reservaNoDia->clienteResponsavel->nome
                                                : optional($reservaNoDia->clienteSolicitante)->nome;

                                            $situacaoLabel = Reserva::SITUACOESRESERVA[$reservaNoDia->situacao_reserva]['label'] ?? $reservaNoDia->situacao_reserva;
                                        @endphp
                                        <a href="{{ route('admin.reservas.edit', ['id' => $reservaNoDia->id] )}}"
                                            data-reserva-id="{{ $reservaNoDia->id }}"
                                            data-reserva-titular="{{ $titular }}"
                                            data-reserva-uh="{{ $quarto->referencia ?? $quarto->numero ?? '' }}"
                                            data-reserva-situacao="{{ $situacaoLabel }}"
                                            data-reserva-situacao-cor="{{ $cor }}"
                                            data-reserva-checkin="{{ $dataCheckin }}"
                                            data-reserva-checkout="{{ $dataCheckout }}"
                                            data-reserva-adultos="{{ $reservaNoDia->adultos }}"
                                            data-reserva-criancas-ate7="{{ $reservaNoDia->criancas_ate_7 }}"
                                            data-reserva-criancas-mais7="{{ $reservaNoDia->criancas_mais_7 }}"
                                            data-reserva-total="{{ $reservaNoDia->total }}"
                                            data-reserva-observacoes="{{ $reservaNoDia->observacoes }}"
                                            data-reserva-edit-url="{{ route('admin.reservas.edit', ['id' => $reservaNoDia->id] )}}"
                                            {{ $diaAtual == $dataCheckin ? 'draggable="true"' : '' }}
                                            class="
                                                reserva-dia
                                                js-open-reserva-modal
                                                {{$diaAtual == $dataCheckin ? 'checkin js-drag-reserva' : ''}}
                                                {{$diaAtual == $dataCheckout ? 'checkout' : ''}}
                                                {{$diaAtual != $dataCheckin && $diaAtual != $dataCheckout ? 'intermediario' : ''}}
                                            "
                                            style="flex: 1; background: {{ $cor }}; color: white; {{ $diaAtual == $dataCheckin ? 'cursor: grab;' : '' }}">
                                                {{ $diaAtual == $dataCheckin ? ($reservaNoDia->clienteResponsavel ? Str::limit(ucwords(strtolower($reservaNoDia->clienteResponsavel->nome)), 15) : "GR: " . Str::limit(ucwords(strtolower($reservaNoDia->clienteSolicitante->nome)), 20)) : '' }}
                                        </a>
                                    @endforeach
                                </td>
                            @else
                                @php $diaAtual = \Carbon\Carbon::parse($diaAtual); @endphp
                                <td style="padding: 0px; height: 30px;" data-date="{{ $diaAtual->format('Y-m-d') }}" data-quarto-id="{{ $quarto->id }}" class="js-drop-target">
                                    <a href="{{ route('admin.reservas.create',
                                            [
                                                'quarto_id' => $quarto->id ?? '',
                                                'quarto_numero' => $quarto->numero ?? '',
                                                'quarto_classificacao' => $quarto->classificacao ?? '',
                                                'quarto_andar' => $quarto->andar ?? '',
                                                'data_checkin' => $diaAtual->format('Y-m-d') ?? '',
                                                'data_checkout' => $diaAtual->copy()->addDay()->format('Y-m-d') ?? '',
                                            ]
                                        ) }}"
                                        class="text-white"
                                        style="display:block; width: 100%; height: 100%;">
                                    </a>
                                </td>
                            @endif
                        @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@php
    // Agrupar Day Uses (reservas sem quarto) por dia de check-in
    $dayUsePorDia = collect($reservas ?? [])->filter(function($reserva) {
        return method_exists($reserva, 'isDayUse') && $reserva->isDayUse();
    })->groupBy(function($reserva) {
        return \Carbon\Carbon::parse($reserva->data_checkin)->toDateString();
    });

    // Resumo de Day Use para o dia atual (caso não venha do controller)
    $hoje = \Carbon\Carbon::now('America/Sao_Paulo')->toDateString();
    $dayUseHoje = $dayUsePorDia->get($hoje, collect());
    $dayUseHojeTotal = $dayUseHoje->count();
    $dayUseHojePessoas = $dayUseHoje->sum('adultos')
        + $dayUseHoje->sum('criancas_ate_7')
        + $dayUseHoje->sum('criancas_mais_7');
@endphp

@if(($dayUseHoje ?? collect())->isNotEmpty())
    <!-- Bloco de Day Use (hoje) -->
    <div class="row mb-4">
        <div class="col-md-4 col-12">
            <a href="{{ route('admin.reservas.day-use', [
                    'data_checkin' => \Carbon\Carbon::now('America/Sao_Paulo')->format('d/m/Y'),
                    'data_checkout' => \Carbon\Carbon::now('America/Sao_Paulo')->format('d/m/Y'),
                ]) }}"
               class="text-decoration-none" style="color: inherit;">
                <div class="card shadow-sm h-100" style="cursor: pointer;">
                    <div class="card-body d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-sun fa-2x" style="color: #f39c12;"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Day Use (hoje)</h6>
                            <div><strong>{{ $dayUseHojeTotal }}</strong> reserva(s) Day Use</div>
                            <div class="text-muted" style="font-size: 0.9rem;">
                                {{ $dayUseHojePessoas }} pessoa(s) previstas
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endif

<!-- Modal de resumo da reserva -->
<div class="modal fade" id="reservaResumoModal" tabindex="-1" role="dialog" aria-labelledby="reservaResumoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservaResumoModalLabel">
                    Reserva #<span id="resumo-reserva-id"></span> - <span class="badge" id="resumo-reserva-situacao"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Titular:</strong> <span id="resumo-reserva-titular"></span></p>
                        <p><strong>UH:</strong> <span id="resumo-reserva-uh"></span></p>
                        <p><strong>Check-in:</strong> <span id="resumo-reserva-checkin"></span></p>
                        <p><strong>Check-out:</strong> <span id="resumo-reserva-checkout"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Adultos:</strong> <span id="resumo-reserva-adultos"></span></p>
                        <p><strong>Crianças até 7 anos:</strong> <span id="resumo-reserva-criancas-ate7"></span></p>
                        <p><strong>Crianças acima de 7 anos:</strong> <span id="resumo-reserva-criancas-mais7"></span></p>
                        <p><strong>Valor Total:</strong> R$ <span id="resumo-reserva-total"></span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <p><strong>Observações:</strong></p>
                        <p id="resumo-reserva-observacoes" class="border rounded p-2" style="min-height: 60px;"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="resumo-reserva-edit-link" class="btn btn-primary" target="_self">
                    Editar reserva
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Drag-and-drop para o mapa de ocupação
    document.addEventListener('DOMContentLoaded', function () {
        var csrfToken = window.APP_CSRF_TOKEN || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');

        document.querySelectorAll('.js-drag-reserva').forEach(function (el) {
            el.addEventListener('dragstart', function (e) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('reserva_id', el.getAttribute('data-reserva-id'));
                e.dataTransfer.setData('checkin', el.getAttribute('data-reserva-checkin'));
                e.dataTransfer.setData('checkout', el.getAttribute('data-reserva-checkout'));
                el.style.opacity = '0.5';
            });
            el.addEventListener('dragend', function () {
                el.style.opacity = '';
            });
        });

        document.querySelectorAll('.js-drop-target').forEach(function (td) {
            td.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                td.style.background = '#d0e8ff';
            });
            td.addEventListener('dragleave', function () {
                td.style.background = '';
            });
            td.addEventListener('drop', function (e) {
                e.preventDefault();
                td.style.background = '';

                var reservaId = e.dataTransfer.getData('reserva_id');
                var checkin   = e.dataTransfer.getData('checkin');
                var checkout  = e.dataTransfer.getData('checkout');
                var newDate   = td.getAttribute('data-date');
                var quartoId  = td.getAttribute('data-quarto-id');

                if (!reservaId || !newDate) return;

                var checkinDate  = new Date(checkin);
                var checkoutDate = new Date(checkout);
                var durationMs   = checkoutDate - checkinDate;
                var durationDays = Math.round(durationMs / 86400000);

                var newCheckin  = new Date(newDate);
                var newCheckout = new Date(newCheckin);
                newCheckout.setDate(newCheckout.getDate() + durationDays);

                var pad = function (n) { return String(n).padStart(2, '0'); };
                var fmt = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };

                var overlay = document.getElementById('mapa-loading-overlay');
                overlay.style.display = 'flex';

                fetch('/admin/reservas/' + reservaId + '/mover', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        quarto_id:    quartoId,
                        data_checkin: fmt(newCheckin),
                        data_checkout:fmt(newCheckout)
                    })
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        overlay.style.display = 'none';
                        alert(data.message || 'Erro ao mover reserva.');
                    }
                })
                .catch(function () {
                    overlay.style.display = 'none';
                    alert('Erro ao mover reserva.');
                });
            });
        });
    });

    $(document).ready(function () {
        $(document).on('click', '.js-open-reserva-modal', function (event) {
            event.preventDefault();

            var $el = $(this);

            var id = $el.data('reserva-id') || '';
            var titular = $el.data('reserva-titular') || '';
            var uh = $el.data('reserva-uh') || '';
            var situacao = $el.data('reserva-situacao') || '';
            var situacaoCor = $el.data('reserva-situacao-cor') || '';
            var checkinRaw = $el.data('reserva-checkin') || '';
            var checkoutRaw = $el.data('reserva-checkout') || '';
            var adultos = $el.data('reserva-adultos') || 0;

            function formatDate(dateStr, time) {
                if (!dateStr) return '';
                var parts = dateStr.split('-');
                return parts[2] + '/' + parts[1] + '/' + parts[0] + ' - ' + time;
            }
            var checkin = formatDate(checkinRaw, '15:00');
            var checkout = formatDate(checkoutRaw, '12:00');
            var criancasAte7 = $el.data('reserva-criancas-ate7') || 0;
            var criancasMais7 = $el.data('reserva-criancas-mais7') || 0;
            var total = $el.data('reserva-total') || '';
            var observacoes = $el.data('reserva-observacoes') || '';
            var editUrl = $el.data('reserva-edit-url') || '#';

            $('#resumo-reserva-id').text(id);
            $('#resumo-reserva-titular').text(titular);
            $('#resumo-reserva-uh').text(uh);
            $('#resumo-reserva-checkin').text(checkin);
            $('#resumo-reserva-checkout').text(checkout);
            $('#resumo-reserva-adultos').text(adultos);
            $('#resumo-reserva-criancas-ate7').text(criancasAte7);
            $('#resumo-reserva-criancas-mais7').text(criancasMais7);
            $('#resumo-reserva-total').text(total);
            $('#resumo-reserva-observacoes').text(observacoes);

            var $situacaoBadge = $('#resumo-reserva-situacao');
            $situacaoBadge.text(situacao);
            if (situacaoCor) {
                $situacaoBadge.css('background-color', situacaoCor);
                $situacaoBadge.css('color', '#fff');
            } else {
                $situacaoBadge.removeAttr('style');
            }

            $('#resumo-reserva-edit-link').attr('href', editUrl);

            $('#reservaResumoModal').modal('show');
        });
    });
</script>
@endpush
