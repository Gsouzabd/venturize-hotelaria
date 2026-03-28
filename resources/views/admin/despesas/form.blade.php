@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Despesa')
@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection
@section('content')

<div class="container">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-admin.form save-route="admin.despesas.save" :back-route="!empty($returnTo) ? '' : 'admin.despesas.index'" id="despesa-form" :files-enctype="true">
        @if($edit)
            <input type="hidden" name="id" value="{{ $despesa->id }}">
        @endif
        @if(!empty($returnTo))
            <input type="hidden" name="_return_to" value="{{ $returnTo }}">
        @endif

        {{-- 1. Fornecedor primeiro --}}
        <x-admin.field-group>
            <x-admin.field cols="10">
                <x-admin.label label="Fornecedor" required/>
                <select name="fornecedor_id" id="fornecedor_id" class="custom-select" required>
                    <option value="">Selecione um fornecedor</option>
                    @foreach($fornecedores as $fornecedor)
                        <option value="{{ $fornecedor->id }}" {{ old('fornecedor_id', $despesa->fornecedor_id ?? '') == $fornecedor->id ? 'selected' : '' }}>
                            {{ $fornecedor->nome }}
                        </option>
                    @endforeach
                </select>
            </x-admin.field>
            <x-admin.field cols="2">
                <x-admin.label label="&nbsp;"/>
                <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#novoFornecedorModal">
                    <i class="fas fa-plus"></i> Novo
                </button>
            </x-admin.field>
        </x-admin.field-group>

        {{-- 2. Data e 3. Valor --}}
        <x-admin.field-group>
            <x-admin.field cols="6">
                <x-admin.label label="Data" required/>
                <x-admin.datepicker name="data" :value="old('data', $despesa->data ? $despesa->data->format('d/m/Y') : '')"/>
            </x-admin.field>

            <x-admin.field cols="6">
                <x-admin.label label="Valor Total" required/>
                <x-admin.text name="valor_total_display" id="valor_total_display" :value="old('valor_total', $despesa->valor_total ? number_format($despesa->valor_total, 2, ',', '.') : '')" class="money-mask" required/>
                <input type="hidden" name="valor_total" id="valor_total" value="{{ old('valor_total', $despesa->valor_total ?? '') }}">
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <x-admin.field cols="12">
                <x-admin.label label="Descrição" required/>
                <x-admin.textarea name="descricao" id="descricao" rows="3" :value="old('descricao', $despesa->descricao ?? '')" required/>
                <small class="form-text text-muted">Descreva a despesa de forma clara e objetiva.</small>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <x-admin.field cols="12">
                <x-admin.label label="Arquivo da Nota Fiscal (PDF, JPG, PNG)"/>
                <input type="file" name="arquivo_nota" id="arquivo_nota" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                @if($edit && $despesa->arquivo_nota)
                    <div class="mt-2">
                        <small class="text-muted">Arquivo atual: </small>
                        <a href="{{ asset('storage/' . $despesa->arquivo_nota) }}" target="_blank" class="btn btn-sm btn-info">
                            <i class="fas fa-file"></i> Ver arquivo atual
                        </a>
                        <small class="text-muted"> (deixe em branco para manter o arquivo atual)</small>
                    </div>
                @endif
                <small class="form-text text-muted">Tamanho máximo: 10MB. Formatos aceitos: PDF, JPG, PNG</small>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <x-admin.field cols="12">
                <x-admin.label label="Observações"/>
                <x-admin.textarea name="observacoes" id="observacoes" :value="old('observacoes', $despesa->observacoes ?? '')"/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <x-admin.field cols="12">
                <x-admin.label label="Rateio de Despesas" required/>
                <div class="alert alert-info">
                    <strong>Instruções:</strong> Adicione as categorias e valores para ratear o valor total da nota.
                    A soma dos valores rateados deve ser igual ao valor total da nota.
                </div>
                <table class="table table-bordered" id="rateios-table">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Valor</th>
                            <th>Observações</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($edit && $despesa->despesaCategorias->count() > 0)
                            @foreach($despesa->despesaCategorias as $index => $rateio)
                                <tr>
                                    <td>
                                        <select name="rateios[{{ $index }}][categoria_despesa_id]" class="form-control categoria-select">
                                            <option value="">Sem categoria</option>
                                            @foreach($categorias as $categoria)
                                                <option value="{{ $categoria->id }}" {{ $rateio->categoria_despesa_id == $categoria->id ? 'selected' : '' }}>
                                                    {{ $categoria->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control valor-rateio-display money-mask"
                                               value="{{ number_format($rateio->valor, 2, ',', '.') }}" required>
                                        <input type="hidden" name="rateios[{{ $index }}][valor]" class="valor-rateio"
                                               value="{{ old('rateios.' . $index . '.valor', $rateio->valor) }}">
                                    </td>
                                    <td>
                                        <input type="text" name="rateios[{{ $index }}][observacoes]" class="form-control"
                                               value="{{ old('rateios.' . $index . '.observacoes', $rateio->observacoes) }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-rateio-btn">Remover</button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right"><strong>Total Rateado:</strong></td>
                            <td><strong id="total-rateado">R$ 0,00</strong></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-right"><strong>Valor Total da Nota:</strong></td>
                            <td><strong id="valor-total-nota">R$ 0,00</strong></td>
                            <td></td>
                        </tr>
                        <tr id="diferenca-row" style="display: none;">
                            <td colspan="2" class="text-right"><strong>Diferença:</strong></td>
                            <td><strong id="diferenca-valor" class="text-danger">R$ 0,00</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <button type="button" id="add-rateio-btn" class="btn btn-primary">Adicionar Rateio</button>
            </x-admin.field>
        </x-admin.field-group>

        @if(!empty($returnTo))
            <div class="text-right mt-2">
                <a href="{{ $returnTo }}" class="btn btn-default ml-md-2">Voltar ao Relatório</a>
            </div>
        @endif

    </x-admin.form>
</div>

{{-- Modal para cadastrar novo fornecedor --}}
<div class="modal fade" id="novoFornecedorModal" tabindex="-1" role="dialog" aria-labelledby="novoFornecedorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="novoFornecedorModalLabel">Novo Fornecedor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-fornecedor-errors" class="alert alert-danger" style="display:none;">
                    <ul></ul>
                </div>
                <div class="form-group">
                    <label>Nome <span class="text-danger">*</span></label>
                    <input type="text" id="modal_fornecedor_nome" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>CNPJ</label>
                            <input type="text" id="modal_fornecedor_cnpj" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" id="modal_fornecedor_telefone" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="modal_fornecedor_email" class="form-control">
                </div>
                <div class="form-group">
                    <label>Endereço</label>
                    <textarea id="modal_fornecedor_endereco" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="salvarFornecedorBtn">
                    <i class="fas fa-save"></i> Salvar Fornecedor
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Máscara de dinheiro (vírgula automática)
        function aplicarMascaraDinheiro(el) {
            $(el).mask('#.##0,00', {reverse: true});
        }

        // Converter valor formatado (1.234,56) para float (1234.56)
        function parseValorBR(valor) {
            if (!valor) return 0;
            return parseFloat(valor.replace(/\./g, '').replace(',', '.')) || 0;
        }

        // Formatar float para formato BR
        function formatarValorBR(valor) {
            return valor.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Aplicar máscara nos campos de dinheiro existentes
        aplicarMascaraDinheiro('.money-mask');

        // Sincronizar campo display com hidden do valor total
        $('#valor_total_display').on('keyup change', function() {
            var valorFloat = parseValorBR($(this).val());
            $('#valor_total').val(valorFloat > 0 ? valorFloat : '');
            calcularTotal();
        });

        // Inicializar valor hidden se já tem valor no display
        if ($('#valor_total_display').val()) {
            var valorInicial = parseValorBR($('#valor_total_display').val());
            if (valorInicial > 0) {
                $('#valor_total').val(valorInicial);
            }
        }

        let rateioIndex = {{ $edit && $despesa->despesaCategorias->count() > 0 ? $despesa->despesaCategorias->count() : 0 }};
        const categorias = @json($categorias);

        // Sincronizar valores display dos rateios existentes
        $(document).on('keyup change', '.valor-rateio-display', function() {
            var valorFloat = parseValorBR($(this).val());
            $(this).siblings('.valor-rateio').val(valorFloat > 0 ? valorFloat : '');
            calcularTotal();
        });

        // Adicionar novo rateio
        $('#add-rateio-btn').on('click', function() {
            const row = $('<tr>');
            let categoriaOptions = '<option value="">Sem categoria</option>';
            categorias.forEach(function(categoria) {
                categoriaOptions += `<option value="${categoria.id}">${categoria.nome}</option>`;
            });

            // Pegar o valor total e calcular valor restante para pré-preencher
            var valorTotal = parseValorBR($('#valor_total_display').val());
            var totalRateado = 0;
            $('.valor-rateio').each(function() {
                totalRateado += parseFloat($(this).val()) || 0;
            });
            var valorRestante = valorTotal - totalRateado;
            var valorPreenchido = valorRestante > 0 ? formatarValorBR(valorRestante) : '';
            var valorHidden = valorRestante > 0 ? valorRestante : '';

            row.html(`
                <td>
                    <select name="rateios[${rateioIndex}][categoria_despesa_id]" class="form-control categoria-select">
                        ${categoriaOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control valor-rateio-display money-mask"
                           value="${valorPreenchido}" required>
                    <input type="hidden" name="rateios[${rateioIndex}][valor]" class="valor-rateio"
                           value="${valorHidden}">
                </td>
                <td>
                    <input type="text" name="rateios[${rateioIndex}][observacoes]" class="form-control">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-rateio-btn">Remover</button>
                </td>
            `);

            $('#rateios-table tbody').append(row);
            aplicarMascaraDinheiro(row.find('.money-mask'));
            rateioIndex++;
            calcularTotal();
        });

        // Remover rateio
        $(document).on('click', '.remove-rateio-btn', function() {
            $(this).closest('tr').remove();
            calcularTotal();
        });

        function calcularTotal() {
            let total = 0;
            $('.valor-rateio').each(function() {
                total += parseFloat($(this).val()) || 0;
            });

            const valorTotal = parseFloat($('#valor_total').val()) || 0;
            const diferenca = valorTotal - total;

            $('#total-rateado').text('R$ ' + formatarValorBR(total));
            $('#valor-total-nota').text('R$ ' + formatarValorBR(valorTotal));

            if (Math.abs(diferenca) > 0.01) {
                $('#diferenca-row').show();
                const classe = diferenca > 0 ? 'text-warning' : 'text-danger';
                $('#diferenca-valor').removeClass('text-warning text-danger').addClass(classe);
                $('#diferenca-valor').text('R$ ' + formatarValorBR(Math.abs(diferenca)));
            } else {
                $('#diferenca-row').hide();
            }
        }

        // Calcular total inicial
        calcularTotal();

        // === Modal Novo Fornecedor (salva via AJAX e volta para o form) ===
        $('#salvarFornecedorBtn').on('click', function() {
            var btn = $(this);
            var nome = $('#modal_fornecedor_nome').val().trim();
            if (!nome) {
                $('#modal-fornecedor-errors').show().find('ul').html('<li>O nome do fornecedor é obrigatório.</li>');
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
            $('#modal-fornecedor-errors').hide();

            $.ajax({
                url: '{{ route("admin.fornecedores.save") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    nome: nome,
                    cnpj: $('#modal_fornecedor_cnpj').val(),
                    telefone: $('#modal_fornecedor_telefone').val(),
                    email: $('#modal_fornecedor_email').val(),
                    endereco: $('#modal_fornecedor_endereco').val()
                },
                success: function(response) {
                    // Adicionar novo fornecedor ao select e selecionar
                    if (response.id && response.nome) {
                        var newOption = new Option(response.nome, response.id, true, true);
                        $('#fornecedor_id').append(newOption);
                    }
                    // Limpar e fechar modal
                    $('#modal_fornecedor_nome, #modal_fornecedor_cnpj, #modal_fornecedor_telefone, #modal_fornecedor_email, #modal_fornecedor_endereco').val('');
                    $('#novoFornecedorModal').modal('hide');
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON?.errors || {};
                    var html = '';
                    if (Object.keys(errors).length > 0) {
                        for (var key in errors) {
                            errors[key].forEach(function(msg) {
                                html += '<li>' + msg + '</li>';
                            });
                        }
                    } else {
                        html = '<li>Erro ao salvar fornecedor. Tente novamente.</li>';
                    }
                    $('#modal-fornecedor-errors').show().find('ul').html(html);
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar Fornecedor');
                }
            });
        });
    });
</script>
@endpush
