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

    <x-admin.form save-route="admin.despesas.save" back-route="admin.despesas.index" id="despesa-form" :files-enctype="true">
        @if($edit)
            <input type="hidden" name="id" value="{{ $despesa->id }}">
        @endif
        
        <x-admin.field-group>
            <x-admin.field cols="4">
                <x-admin.label label="Número da Nota Fiscal"/>
                <x-admin.text name="numero_nota_fiscal" id="numero_nota_fiscal" :value="old('numero_nota_fiscal', $despesa->numero_nota_fiscal ?? '')"/>
            </x-admin.field>

            <x-admin.field cols="4">
                <x-admin.label label="Data" required/>
                <x-admin.text name="data" id="data" :value="old('data', $despesa->data ? $despesa->data->format('d/m/Y') : '')" class="date-mask" required/>
            </x-admin.field>

            <x-admin.field cols="4">
                <x-admin.label label="Valor Total" required/>
                <x-admin.number name="valor_total" id="valor_total" :value="old('valor_total', $despesa->valor_total ?? '0.00')" step="0.01" min="0.01" required/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <x-admin.field cols="12">
                <x-admin.label label="Fornecedor"/>
                <x-admin.select2 
                    name="fornecedor_id" 
                    id="fornecedor_id"
                    remoteUrl="{{ route('admin.fornecedores.search') }}"
                    minInputLength="3"
                    placeholder="Buscar fornecedor (opcional)"
                    remoteUrlSelectedValue="{{ old('fornecedor_id', $despesa->fornecedor_id ?? '') }}"
                    remoteUrlSelectedText="{{ ($edit && $despesa->fornecedor) ? $despesa->fornecedor->nome : '' }}"
                />
                <div class="mt-2">
                    <a href="{{ route('admin.fornecedores.create') }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus"></i> CADASTRAR NOVO FORNECEDOR
                    </a>
                </div>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <x-admin.field cols="12">
                <x-admin.label label="Descrição" required/>
                <x-admin.textarea name="descricao" id="descricao" rows="3" required>{{ old('descricao', $despesa->descricao ?? '') }}</x-admin.textarea>
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
                <x-admin.textarea name="observacoes" id="observacoes">{{ old('observacoes', $despesa->observacoes ?? '') }}</x-admin.textarea>
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
                                        <input type="number" name="rateios[{{ $index }}][valor]" class="form-control valor-rateio" 
                                               value="{{ old('rateios.' . $index . '.valor', $rateio->valor) }}" 
                                               step="0.01" min="0.01" required>
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

    </x-admin.form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.date-mask').mask('00/00/0000');
        
        // Configurar Select2 para fornecedor com AJAX
        // Aguardar um pouco para garantir que o componente tenha inicializado
        setTimeout(function() {
            var $select = $('#fornecedor_id');
            var selectedValue = $select.val();
            var selectedText = $select.find('option:selected').text();
            
            // Se já foi inicializado pelo componente, destruir e reinicializar com AJAX
            if ($select.data('select2')) {
                $select.select2('destroy');
            }
            
            // Configurar Select2 com AJAX
            $select.select2({
                dropdownParent: $select.parent(),
                language: 'pt-BR',
                ajax: {
                    url: '{{ route('admin.fornecedores.search') }}',
                    dataType: 'json',
                    delay: 500,
                    cache: false,
                    data: function (params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results || []
                        };
                    }
                },
                minimumInputLength: 3,
                escapeMarkup: function (markup) {
                    // Não fazer escape adicional - o texto já vem correto do servidor
                    return markup;
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }
                    // Retornar texto sem escape adicional
                    return data.text;
                },
                templateSelection: function(data) {
                    // Retornar texto sem escape adicional para exibição
                    return data.text || data.id;
                }
            });
            
            // Se há valor selecionado, garantir que está selecionado
            if (selectedValue && selectedValue !== '' && selectedText && selectedText.trim() !== '') {
                // Verificar se a opção já existe
                if ($select.find('option[value="' + selectedValue + '"]').length === 0) {
                    // Adicionar opção se não existir
                    var option = new Option(selectedText, selectedValue, true, true);
                    $select.append(option);
                }
                $select.val(selectedValue).trigger('change');
            }
        }, 300);
        
        let rateioIndex = {{ $edit && $despesa->despesaCategorias->count() > 0 ? $despesa->despesaCategorias->count() : 0 }};
        const categorias = @json($categorias);
        
        // Adicionar novo rateio
        $('#add-rateio-btn').on('click', function() {
            const row = $('<tr>');
            let categoriaOptions = '<option value="">Sem categoria</option>';
            categorias.forEach(function(categoria) {
                categoriaOptions += `<option value="${categoria.id}">${categoria.nome}</option>`;
            });
            
            row.html(`
                <td>
                    <select name="rateios[${rateioIndex}][categoria_despesa_id]" class="form-control categoria-select">
                        ${categoriaOptions}
                    </select>
                </td>
                <td>
                    <input type="number" name="rateios[${rateioIndex}][valor]" class="form-control valor-rateio" 
                           step="0.01" min="0.01" required>
                </td>
                <td>
                    <input type="text" name="rateios[${rateioIndex}][observacoes]" class="form-control">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-rateio-btn">Remover</button>
                </td>
            `);
            
            $('#rateios-table tbody').append(row);
            rateioIndex++;
            calcularTotal();
        });
        
        // Remover rateio
        $(document).on('click', '.remove-rateio-btn', function() {
            $(this).closest('tr').remove();
            calcularTotal();
        });
        
        // Calcular total quando valores mudarem
        $(document).on('input', '.valor-rateio', function() {
            calcularTotal();
        });
        
        $('#valor_total').on('input', function() {
            calcularTotal();
        });
        
        function calcularTotal() {
            let total = 0;
            $('.valor-rateio').each(function() {
                const valor = parseFloat($(this).val()) || 0;
                total += valor;
            });
            
            const valorTotal = parseFloat($('#valor_total').val()) || 0;
            const diferenca = valorTotal - total;
            
            $('#total-rateado').text('R$ ' + total.toFixed(2).replace('.', ','));
            $('#valor-total-nota').text('R$ ' + valorTotal.toFixed(2).replace('.', ','));
            
            if (Math.abs(diferenca) > 0.01) {
                $('#diferenca-row').show();
                const classe = diferenca > 0 ? 'text-warning' : 'text-danger';
                $('#diferenca-valor').removeClass('text-warning text-danger').addClass(classe);
                $('#diferenca-valor').text('R$ ' + Math.abs(diferenca).toFixed(2).replace('.', ','));
            } else {
                $('#diferenca-row').hide();
            }
        }
        
        // Calcular total inicial
        calcularTotal();
    });
</script>
@endpush

