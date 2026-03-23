<div class="tab-pane fade" id="transferencia" role="tabpanel" aria-labelledby="transferencia-tab">

    {{-- Seção: Editar Período --}}
    <h5><i class="fas fa-calendar-edit"></i> Editar Período da Reserva</h5>
    <p class="text-muted">Altere as datas de check-in e check-out sem trocar o apartamento.</p>

    <div id="editar-periodo-form">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nova Data de Check-in</label>
                    <input type="date" id="periodo_checkin" class="form-control"
                        value="{{ \Carbon\Carbon::parse($reserva->data_checkin)->format('Y-m-d') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nova Data de Check-out</label>
                    <input type="date" id="periodo_checkout" class="form-control"
                        value="{{ \Carbon\Carbon::parse($reserva->data_checkout)->format('Y-m-d') }}">
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-primary mb-3 w-100" id="btn-salvar-periodo">
                    <i class="fas fa-save"></i> Salvar Período
                </button>
            </div>
        </div>
        <div id="periodo-msg" style="display:none;"></div>
    </div>

    <hr>

    <h5><i class="fas fa-exchange-alt"></i> Transferência de Apartamento</h5>
    <p class="text-muted">Transfira o hóspede para outro apartamento a partir de uma data específica.</p>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <x-admin.field-group>
        <x-admin.field cols="6">
            <x-admin.label label="Novo Apartamento" required/>
            <select name="quarto_id" class="form-control" required>
                <option value="">Selecione o apartamento...</option>
                @foreach(\App\Models\Quarto::where('inativo', 0)->orderBy('numero')->get() as $q)
                    <option value="{{ $q->id }}" {{ $q->id == $reserva->quarto_id ? 'disabled' : '' }}>
                        {{ $q->numero }} — {{ $q->referencia ?? $q->classificacao }}
                        {{ $q->id == $reserva->quarto_id ? '(atual)' : '' }}
                    </option>
                @endforeach
            </select>
        </x-admin.field>
        <x-admin.field cols="6">
            <x-admin.label label="Data da Transferência" required/>
            <div class="input-group">
                <input
                    type="text"
                    id="data_transferencia"
                    name="data_transferencia"
                    class="form-datepicker form-control date-mask"
                    value="{{ old('data_transferencia', isset($reserva->data_checkin) ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d-m-Y') : \Carbon\Carbon::today()->format('d-m-Y')) }}"
                    required
                >
                <div class="input-group-append">
                    <span class="input-group-text">
                        <i class="fas fa-calendar"></i>
                    </span>
                </div>
            </div>
        </x-admin.field>
    </x-admin.field-group>
    <small class="text-muted d-block mb-2">
        Use uma data dentro do período da reserva (entre check-in e check-out).
    </small>

    <button
        type="submit"
        formaction="{{ route('admin.reservas.transferir', ['id' => $reserva->id]) }}"
        formmethod="POST"
        formnovalidate
        onclick="(function(btn){ var methodInput = btn.form ? btn.form.querySelector('input[name=\'_method\']') : null; if (methodInput) { methodInput.disabled = true; } })(this)"
        class="btn btn-warning"
    >
        <i class="fas fa-exchange-alt"></i> Transferir
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnSalvar = document.getElementById('btn-salvar-periodo');
    if (!btnSalvar) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || '{{ csrf_token() }}';
    const moverUrl = '{{ route("admin.reservas.mover", ["id" => $reserva->id]) }}';

    btnSalvar.addEventListener('click', function () {
        const checkin  = document.getElementById('periodo_checkin').value;
        const checkout = document.getElementById('periodo_checkout').value;
        const msgEl    = document.getElementById('periodo-msg');

        msgEl.style.display = 'none';
        msgEl.className = '';

        if (!checkin || !checkout) {
            msgEl.textContent = 'Informe as duas datas.';
            msgEl.className = 'alert alert-danger';
            msgEl.style.display = 'block';
            return;
        }
        if (checkout <= checkin) {
            msgEl.textContent = 'A data de check-out deve ser posterior ao check-in.';
            msgEl.className = 'alert alert-danger';
            msgEl.style.display = 'block';
            return;
        }

        btnSalvar.disabled = true;

        const body = new URLSearchParams({
            _token: csrfToken,
            _method: 'PATCH',
            quarto_id: '{{ $reserva->quarto_id }}',
            data_checkin: checkin,
            data_checkout: checkout,
        });

        fetch(moverUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body,
        })
        .then(r => r.json())
        .then(data => {
            btnSalvar.disabled = false;
            if (data.success) {
                msgEl.textContent = 'Período atualizado com sucesso!';
                msgEl.className = 'alert alert-success';
            } else {
                msgEl.textContent = data.message || 'Erro ao atualizar período.';
                msgEl.className = 'alert alert-danger';
            }
            msgEl.style.display = 'block';
        })
        .catch(() => {
            btnSalvar.disabled = false;
            msgEl.textContent = 'Erro de comunicação.';
            msgEl.className = 'alert alert-danger';
            msgEl.style.display = 'block';
        });
    });
});
</script>
