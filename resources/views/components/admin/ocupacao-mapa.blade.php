@php
use App\Models\Reserva;

@endphp

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
                    <td class="quarto">{{ $quarto->numero ?? '' }}</td>
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
                                <td style="padding: 0px; height: 30px;">
                                    @foreach($reservasNoDia->sortBy('data_checkin') as $reservaNoDia)
                                    
                                        @php
                                            $cor = '';
                                            $cor = Reserva::SITUACOESRESERVA[$reservaNoDia->situacao_reserva]['background'] ?? '';
                   
                                            $dataCheckin = \Carbon\Carbon::parse($reservaNoDia->data_checkin)->toDateString();
                                            $dataCheckout = \Carbon\Carbon::parse($reservaNoDia->data_checkout)->toDateString();
                                        @endphp
                                        <a href="{{ route('admin.reservas.edit', ['id' => $reservaNoDia->id] )}}" 
                                            class="
                                                reserva-dia 
                                                {{$diaAtual == $dataCheckin ? 'checkin' : ''}} 
                                                {{$diaAtual == $dataCheckout ? 'checkout' : ''}}
                                                {{$diaAtual != $dataCheckin && $diaAtual != $dataCheckout ? 'intermediario' : ''}}
                                            
                                            "
                                            style="flex: 1; background: {{ $cor }}; color: white; ">
                                                {{ $diaAtual == $dataCheckin ? ($reservaNoDia->clienteResponsavel ? ucwords(strtolower($reservaNoDia->clienteResponsavel->nome)) : "GR: " . ucwords(strtolower($reservaNoDia->clienteSolicitante->nome))) : '' }}
                                        </a>
                                    @endforeach
                                </td>
                            @else
                                @php $diaAtual = \Carbon\Carbon::parse($diaAtual); @endphp
                                <td style="padding: 0px; height: 30px;">
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
