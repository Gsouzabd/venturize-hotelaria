<div class="tab-pane fade" id="transferencia" role="tabpanel" aria-labelledby="transferencia-tab">

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
                    value="{{ old('data_transferencia', isset($reserva->data_checkin) ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') : \Carbon\Carbon::today()->format('d/m/Y')) }}"
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

