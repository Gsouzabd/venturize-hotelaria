@php use App\Models\Reserva; @endphp

@if ($edit)
    <div class="tab-pane fade" id="checkin" role="tabpanel" aria-labelledby="checkin-tab">
        <h3>Realizar Check-in</h3>
        <br/>

        <!-- Conteúdo da aba de check-in -->
        @if (in_array($reserva->situacao_reserva, ['HOSPEDADO', 'NO SHOW', 'cancelado']))
            <div class="alert alert-info" style="background: {{Reserva::SITUACOESRESERVA[$reserva->situacao_reserva]['background']}}; color: white">
                O status da reserva já foi atualizado para: <strong>{{ $reserva->situacao_reserva }}.<strong>
                    <br/><br/>
                    @if (in_array($reserva->situacao_reserva, ['HOSPEDADO', 'NO SHOW', 'cancelado']))

                    <strong>Data da operação: </strong> {{$reserva->checkin ? timestamp_br($reserva->checkin->checkin_at) : '' }}
                    @endif
                <input class="form-check-input" type="hidden" name="situacao_reserva" id="confirmarCheckin" value= {{ $reserva->situacao_reserva }}>
            </div>
        @else
            <div class="d-flex justify-content-center" style="gap: 20px">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="situacao_reserva" id="confirmarCheckin" value="hospedado" {{ $reserva->situacao_reserva == 'hospedado' ? 'checked' : '' }}>
                    <label class="form-check-label btn btn-success w-100" for="confirmarCheckin">
                        <i class="fas fa-check"></i> Check-in
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="situacao_reserva" id="noShow" value="no show" {{ $reserva->situacao_reserva == 'no show' ? 'checked' : '' }}>
                    <label class="form-check-label btn btn-warning w-100" for="noShow">
                        <i class="fas fa-times-circle"></i> No Show
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="situacao_reserva" id="cancelado" value="cancelada" {{ $reserva->situacao_reserva == 'cancelada' ? 'checked' : '' }}>
                    <label class="form-check-label btn btn-danger w-100" for="cancelado">
                        <i class="fas fa-ban"></i> Cancelado
                    </label>
                </div>
            </div>

            
            <div class="d-flex justify-content-center mt-3 w-100">
                <button id="confirmButton" class="btn btn-primary" style="display: none;">Confirmar</button>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const radioButtons = document.querySelectorAll('input[name="situacao_reserva"]');
                    const confirmButton = document.getElementById('confirmButton');
                
                    radioButtons.forEach(radio => {
                        radio.addEventListener('change', function () {
                            confirmButton.style.display = 'block';
                        });
                    });
                
                
                });
            </script>

        @endif
    </div>
@endif

