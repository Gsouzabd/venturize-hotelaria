<div class="tab-pane fade" id="refeicoes" role="tabpanel" aria-labelledby="refeicoes-tab">
    <h5><i class="fas fa-utensils"></i> Refeições</h5>

    @php
        $existingRefeicoes = $reserva->refeicoes ?? collect();
        $titularNome = $reserva->clienteResponsavel->nome ?? $reserva->clienteSolicitante->nome ?? 'Titular';
        $titularRefeicao = $existingRefeicoes->where('hospede_tipo', 'titular')->first();
        $i = 0;
    @endphp

        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th class="text-center">Café da Manhã</th>
                    <th class="text-center">Almoço</th>
                    <th class="text-center">Jantar</th>
                </tr>
            </thead>
            <tbody>
                {{-- Titular --}}
                <tr>
                    <td>{{ $titularNome }}</td>
                    <td><span class="badge badge-primary">Titular</span></td>
                    <td class="text-center">
                        <input type="hidden" name="refeicoes[{{ $i }}][hospede_nome]" value="{{ $titularNome }}">
                        <input type="hidden" name="refeicoes[{{ $i }}][hospede_tipo]" value="titular">
                        <input type="checkbox" name="refeicoes[{{ $i }}][cafe]" value="1"
                            {{ !$titularRefeicao || $titularRefeicao->cafe ? 'checked' : '' }}>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="refeicoes[{{ $i }}][almoco]" value="1"
                            {{ $titularRefeicao && $titularRefeicao->almoco ? 'checked' : '' }}>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="refeicoes[{{ $i }}][jantar]" value="1"
                            {{ $titularRefeicao && $titularRefeicao->jantar ? 'checked' : '' }}>
                    </td>
                </tr>
                @php $i++; @endphp

                {{-- Acompanhantes --}}
                @foreach($reserva->acompanhantes as $acompanhante)
                    @php
                        $acompRefeicao = $existingRefeicoes->where('acompanhante_id', $acompanhante->id)->first();
                    @endphp
                    <tr>
                        <td>{{ $acompanhante->nome }}</td>
                        <td><span class="badge badge-secondary">Acompanhante</span></td>
                        <td class="text-center">
                            <input type="hidden" name="refeicoes[{{ $i }}][hospede_nome]" value="{{ $acompanhante->nome }}">
                            <input type="hidden" name="refeicoes[{{ $i }}][hospede_tipo]" value="acompanhante">
                            <input type="hidden" name="refeicoes[{{ $i }}][acompanhante_id]" value="{{ $acompanhante->id }}">
                            <input type="checkbox" name="refeicoes[{{ $i }}][cafe]" value="1"
                                {{ !$acompRefeicao || $acompRefeicao->cafe ? 'checked' : '' }}>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="refeicoes[{{ $i }}][almoco]" value="1"
                                {{ $acompRefeicao && $acompRefeicao->almoco ? 'checked' : '' }}>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="refeicoes[{{ $i }}][jantar]" value="1"
                                {{ $acompRefeicao && $acompRefeicao->jantar ? 'checked' : '' }}>
                        </td>
                    </tr>
                    @php $i++; @endphp
                @endforeach
            </tbody>
        </table>

        <button
            type="submit"
            formaction="{{ route('admin.reservas.refeicoes', ['id' => $reserva->id]) }}"
            formmethod="POST"
            formnovalidate
            onclick="(function(btn){ var methodInput = btn.form ? btn.form.querySelector('input[name=\'_method\']') : null; if (methodInput) { methodInput.disabled = true; } })(this)"
            class="btn btn-primary"
        >
            <i class="fas fa-save"></i> Salvar Refeições
        </button>
</div>
