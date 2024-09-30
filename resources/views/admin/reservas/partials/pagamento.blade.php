<div class="tab-pane fade" id="pagamento" role="tabpanel" aria-labelledby="pagamento-tab">
    <h3>Detalhes do Pagamento</h3>
    <p>Selecione o método de pagamento e preencha os detalhes abaixo.</p>

    <x-admin.field-group>
        <!-- Método de Pagamento -->
        <x-admin.field cols="6">
            <x-admin.label label="Método de Pagamento"/>
            <x-admin.select name="metodo_pagamento" id="metodo_pagamento" class="form-control"
                :items="['PIX' => 'Pix', 'DINHEIRO' => 'Dinheiro', 'CARTAO_CREDITO' => 'Cartão de Crédito', 'TRANSFERENCIA' => 'Transferência Bancária']"
                selectedItem="{{ old('metodo_pagamento', $reserva->pagamento->metodo_pagamento ?? '') }}"/>
        </x-admin.field>
    </x-admin.field-group>

    <x-admin.field-group>
        <!-- Valor Total -->
        <x-admin.field cols="6">
            <x-admin.label label="Valor Total"/>
            <x-admin.text id="valor_total" name="valor_total" class="form-control"
                value="{{ old('valor_total', $reserva->pagamento->valor_total ?? 0) }}" placeholder="Valor total da reserva"/>
        </x-admin.field>

        <!-- Valor Sinal -->
        <x-admin.field cols="6">
            <x-admin.label label="Valor Sinal"/>
            <x-admin.text id="valor_sinal" name="valor_sinal" class="form-control"
                value="{{ old('valor_sinal', $reserva->pagamento->valor_sinal ?? 0) }}" placeholder="Valor sinal (se houver pagamento parcial)"/>
        </x-admin.field>
    </x-admin.field-group>

    <x-admin.field-group>
        <!-- Valor Pago -->
        <x-admin.field cols="6">
            <x-admin.label label="Valor Pago"/>
            <x-admin.text id="valor_pago" name="valor_pago" class="form-control"
                value="{{ old('valor_pago', $reserva->pagamento->valor_pago ?? 0) }}" placeholder="Valor já pago"/>
        </x-admin.field>

        <!-- Status do Pagamento -->
        <x-admin.field cols="6">
            <x-admin.label label="Status do Pagamento"/>
            <x-admin.select name="status_pagamento" id="status_pagamento" class="form-control"
                :items="['PAGO' => 'Pago', 'PARCIAL' => 'Parcialmente Pago', 'PENDENTE' => 'Pendente']"
                selectedItem="{{ old('status_pagamento', $reserva->pagamento->status_pagamento ?? 'PENDENTE') }}"/>
        </x-admin.field>
    </x-admin.field-group>

    <x-admin.field-group>
        <!-- Campos específicos de acordo com o método de pagamento -->
        <div id="pixDetails" class="d-none">
            <x-admin.field cols="12">
                <x-admin.label label="Chave PIX"/>
                <x-admin.text id="pix_key" name="pix_key" class="form-control"
                    value="{{ old('pix_key') }}" placeholder="Informe a chave PIX"/>
            </x-admin.field>
        </div>

        <div id="cartaoCreditoDetails" class="d-none">
            <x-admin.field cols="6">
                <x-admin.label label="Número do Cartão"/>
                <x-admin.text id="numero_cartao" name="numero_cartao" class="form-control"
                    value="{{ old('numero_cartao') }}" placeholder="Informe o número do cartão de crédito"/>
            </x-admin.field>

            <x-admin.field cols="6">
                <x-admin.label label="Nome no Cartão"/>
                <x-admin.text id="nome_cartao" name="nome_cartao" class="form-control"
                    value="{{ old('nome_cartao') }}" placeholder="Informe o nome impresso no cartão"/>
            </x-admin.field>
        </div>
    </x-admin.field-group>
</div>

<!-- Adicione scripts para mostrar campos com base no método de pagamento -->
<script>
    document.querySelector('select[name="metodo_pagamento"]').addEventListener('change', function () {
        var metodo = this.value;
        document.getElementById('pixDetails').classList.add('d-none');
        document.getElementById('cartaoCreditoDetails').classList.add('d-none');

        if (metodo === 'PIX') {
            document.getElementById('pixDetails').classList.remove('d-none');
        } else if (metodo === 'CARTAO_CREDITO') {
            document.getElementById('cartaoCreditoDetails').classList.remove('d-none');
        }
    });
</script>
