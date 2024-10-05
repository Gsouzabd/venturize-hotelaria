@extends('layouts.admin.master')
{{-- @php dd($reserva) @endphp --}}

@section('title', ($edit ? 'Editando' : 'Inserindo'). ' Reserva')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.reserva-form save-route="admin.reservas.save" back-route="admin.reservas.index" submit-title="Finalizar" class="reservarForm">
        <div class="col-md-12">
            <ul class="nav nav-tabs mb-4" id="reservaTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="informacoes-gerais-tab" data-toggle="tab" href="#informacoes-gerais" role="tab" aria-controls="informacoes-gerais" aria-selected="true">
                        <i class="fas fa-info-circle"></i> Informações Gerais
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{$edit ? '' : 'disabled'}}" id="disponibilidade-tab" data-toggle="tab" href="#disponibilidade" role="tab" aria-controls="disponibilidade" aria-selected="false">
                        <i class="fas fa-calendar-alt"></i> Disponibilidade
                    </a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link {{$edit ? '' : 'disabled'}}" id="pagamento-tab" data-toggle="tab" href="#pagamento" role="tab" aria-controls="pagamento" aria-selected="false">
                        <i class="fas fa-credit-card"></i> Pagamento
                    </a>
                </li>
                
            </ul>

            <!-- Conteúdo das tabs -->
            <div class="tab-content " id="reservaTabContent">
                <!-- Tab 1: Informações Gerais -->
                @if($edit)
                    <input type="hidden" name="reserva_id" value="{{ $reserva->id }}">
                @endif
                @include('admin.reservas.partials.informacoes-gerais')

                <!-- Tab 2: Disponibilidade -->
                @include('admin.reservas.partials.disponibilidade')

                <!-- Tab 3: Pagamento -->
                @include('admin.reservas.partials.pagamento')

            </div>
        </div>

    </x-admin.reserva-form>
    
    <!-- Modal -->
    <div class="modal fade" id="responsavelModal" tabindex="-1" role="dialog" aria-labelledby="responsavelModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responsavelModalLabel">Informações do Responsável pelo Quarto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="responsavelNome">Nome</label>
                        <input type="text" class="form-control" id="responsavelNome" name="responsavelNome" required>
                    </div>
                    <div class="form-group" id="cpfGroup">
                        <label for="responsavelCpf">CPF</label>
                        <input type="text" class="form-control" id="responsavelCpf" name="responsavelCpf" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="saveResponsavel">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="responsavelReservaModal" tabindex="-1" role="dialog" aria-labelledby="responsavelModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responsavelReservaModalLabel">Informações do Responsável pela Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tipoReserva">Tipo de Reserva</label>
                        <select class="form-control" id="tipoReserva" name="tipoReserva" required>
                            <option value="pre-reserva">Pré-Reserva</option>
                            <option value="reserva">Reserva</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="responsavelNome">Nome</label>
                        <input type="text" class="form-control" id="responsavelReservaNome" name="responsavelReservaNome" required>
                    </div>
                    <div class="form-group" id="cpfGroup2" style="display: none;">
                        <label for="responsavelCpf">CPF</label>
                        <input type="text" class="form-control" id="responsavelReservaCpf" name="responsavelReservaCpf" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="solicitanteHospedeCheckboxModal">
                        <label class="form-check-label" for="solicitanteHospedeCheckboxModal">
                            Solicitante será o hóspede?
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="saveResponsavelReserva" data-dismiss="modal">Salvar</button>
                </div>
            </div>
        </div>
    </div>
    

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            localStorage.removeItem('cart');
    
            // Script do cart quando for tela de edição
            if (window.location.href.indexOf('edit') > -1) {
                const quartoId = "{{ $reserva->quarto_id ?? '' }}";
                const dataCheckin = "{{ $reserva->data_checkin ?? '' }}";
                const dataCheckout = "{{ $reserva->data_checkout ?? '' }}";
                obterPlanosPrecos(quartoId, dataCheckin, dataCheckout).then(precosData => {
                console.log('precosData', precosData);
                const precosDiarios = Object.entries(precosData.precosDiarios)
                .map(([data, preco]) => {
                    const [year, month, day] = data.split('-');  // Divide a string no formato 'YYYY-MM-DD'
                    return {
                        data: `${day}/${month}/${year}`,          // Reorganiza no formato 'DD/MM/YYYY'
                        preco: parseFloat(preco).toFixed(2)      // Garante que o preço tenha duas casas decimais
                    };
                });

                const total = parseFloat(precosData.total).toFixed(2);
                console.log('precosDiarios', precosDiarios);



    adicionarQuartoAoCart(
        quartoId,
        "{{ $reserva->quarto->numero ?? '' }}",
        "{{ $reserva->quarto->andar ?? '' }}",
        "{{ $reserva->quarto->classificacao ?? '' }}",
        "{{ $reserva->tipo_acomodacao ?? '' }}",
        "{{ $reserva->clienteResponsavel->nome ?? '' }}",
        "{{ $reserva->clienteResponsavel->cpf ?? '' }}",
        dataCheckin,
        dataCheckout,
        precosDiarios,
        total
    );
}).catch(error => {
    console.error('Erro ao obter planos de preços:', error);
});


            }
            const saveInfoButton = document.getElementById('saveInfoButton');
            const disponibilidadeTabLink = document.getElementById('disponibilidade-tab');
            const formFields = document.querySelectorAll('#informacoes-gerais input, #informacoes-gerais textarea, #informacoes-gerais select');
        
            saveInfoButton.addEventListener('click', function () {
                let formIsValid = true;
        
                formFields.forEach(function (field) {
                    console.log(field.value); // Log do valor do campo para depuração
                    if ((field.hasAttribute('required') || field.tagName.toLowerCase() === 'select') && field.value.trim() === '') {
                        formIsValid = false;
                        field.setCustomValidity('Este campo é obrigatório.');
                        field.reportValidity(); // Exibe a mensagem de erro nativa do navegador
                        field.focus(); // Foca no campo inválido
                    } else {
                        field.setCustomValidity(''); // Limpa a mensagem de erro personalizada
                    }
                });
        
                if (formIsValid) {
                    // Habilita a tab de Disponibilidade
                    disponibilidadeTabLink.classList.remove('disabled');
                    disponibilidadeTabLink.click(); // Alterna para a tab de Disponibilidade
                } else {
                    // Exibe uma mensagem de erro genérica se o formulário não for válido
                    alert('Por favor, preencha todos os campos obrigatórios.');
                }
            });
 
    


            // Verificar os parâmetros da URL
            const urlParams = new URLSearchParams(window.location.search);
            const quartoId = urlParams.get('quarto_id');
            const quartoNumero = urlParams.get('quarto_numero');
            const quartoClassificacao = urlParams.get('quarto_classificacao');
            const quartoAndar = urlParams.get('quarto_andar');
            const formatDate = (dateString) => {
                const [year, month, day] = dateString.split('-');
                return `${day}-${month}-${year}`;
            };

            const dataCheckin = urlParams.get('data_checkin') ? formatDate(urlParams.get('data_checkin')) : '';
            
            const dataCheckout = urlParams.get('data_checkout') ? formatDate(urlParams.get('data_checkout')) : ''
            // Adicionar evento de clique para os botões de seleção de quarto
            let responsaveis = [];


            if (quartoId && quartoNumero && quartoClassificacao && quartoAndar && dataCheckin && dataCheckout) {
                $('#responsavelReservaModal').modal('show');

                mostrarQuartosDisponiveis({
                    quartos: [{
                        id: quartoId,
                        numero: quartoNumero,
                        classificacao: quartoClassificacao,
                        andar: quartoAndar,
                    }],
                    dataEntrada: dataCheckin, // Exemplo de data de entrada
                    dataSaida: dataCheckout, // Exemplo de data de saída
                });
                const disponibilidadeTabLink = document.getElementById('disponibilidade-tab');
                const infoGeraisTabLink = document.getElementById('informacoes-gerais-tab');

                // define as datas da url nos inputs
                document.querySelector('input[name="data_entrada"]').value = dataCheckin;
                document.querySelector('input[name="data_saida"]').value = dataCheckout;


                disponibilidadeTabLink.classList.add('disabled');
                infoGeraisTabLink.click(); // Alterna para a tab de Disponibilidade
            


                const tipoReservaSelect = document.getElementById('tipoReserva');

                // Função para atualizar a visibilidade dos campos
                function atualizarCampos() {
                    const cpfGroup = document.getElementById('cpfGroup2');
                    console.log(tipoReservaSelect.value);
                
                    if (tipoReservaSelect.value == 'pre-reserva') {
                        cpfGroup.style.display = 'none';
                        document.getElementById('responsavelReservaCpf').removeAttribute('required');
                    } else {
                        cpfGroup.style.display = 'block';
                        document.getElementById('responsavelReservaCpf').setAttribute('required', 'required');
                    }
                }

                // Atualiza os campos ao carregar a página
                atualizarCampos();

                // Adiciona um evento de mudança ao select
                tipoReservaSelect.addEventListener('change', atualizarCampos);

                // Evento de clique para salvar o responsável pela reserva
                document.getElementById('saveResponsavelReserva').addEventListener('click', function() {
                    $('#responsavelReservaModal').modal('hide');

                    const solicitanteHospedeModal = document.getElementById('solicitanteHospedeCheckboxModal').checked;
                
                
                    // Preencher os campos nomeSolicitante e cpf
                    var nome = '';
                    var cpf = '';
                    nome = document.getElementById('responsavelReservaNome').value;
                    cpf = document.getElementById('responsavelReservaCpf').value;
                    document.getElementById('nomeSolicitante').value = nome;
                    document.getElementById('cpf').value = cpf;
                    document.getElementById('solicitanteHospedeCheckbox').checked = solicitanteHospedeModal;
    
                    if(!solicitanteHospedeModal){
                        nome = '';
                        cpf = '';
                    }
                    responsaveis.push({ quartoId, nome, cpf });
                    adicionarQuartoAoCart(
                        quartoId, 
                        quartoNumero, 
                        quartoAndar, 
                        quartoClassificacao, 
                        '',
                        nome, 
                        cpf, 
                        formatDate(urlParams.get('data_checkin')), 
                        formatDate(urlParams.get('data_checkout')),
                    );
                    
                });
            }
            $(document).ready(function() {
                // Inicializa os datepickers
                $('#data_entrada').datepicker({
                    dateFormat: 'dd/mm/yy',
                    minDate: 0, // Desabilita datas passadas
                    onSelect: function(selectedDate) {
                        // Define a data mínima da saída para um dia após a data de entrada
                        var minDate = $('#data_entrada').datepicker('getDate');
                        minDate.setDate(minDate.getDate() + 1);
                        $('#data_saida').datepicker('x.admin-option', 'minDate', minDate);
                    }
                });

                $('#data_saida').datepicker({
                    dateFormat: 'dd/mm/yy',
                    minDate: 1, // Saída deve ser ao menos um dia após a entrada
                });
            });



       document.getElementById('verificarDisponibilidade').addEventListener('click', function() {
            // Obtém os valores do formulário
            const dataEntrada = document.querySelector('input[name="data_entrada"] ').value;
            const dataSaida = document.querySelector('input[name="data_saida"] ').value;
            const tipoQuarto = document.querySelector('select[name="tipo_quarto"] ').value;

            const apartamentos = document.getElementById('apartamentos').value;
            const adultos = document.getElementById('adultos').value;
            const criancas_ate_7 = document.getElementById('criancas_ate_7').value;
            const criancas_mais_7 = document.getElementById('criancas_mais_7').value;


            const data = {
                dataEntrada,
                dataSaida,
                tipoQuarto,
                apartamentos,
                adultos,
                criancas_ate_7,
                criancas_mais_7
            };

            mostrarQuartosDisponiveis(data)
        });



        // Função para obter planos de preços de um quarto
        async function obterPlanosPrecos(quartoId, dataEntrada, dataSaida) {
            const response = await fetch(`/admin/quartos/${quartoId}/planos-preco?data_entrada=${dataEntrada}&data_saida=${dataSaida}`);                    
            const data = await response.json();
            return data;
        }
    
        // Função para adicionar um quarto ao carrinho
            async function adicionarQuartoAoCart(
                quartoId, quartoNumero, quartoAndar, 
                quartoClassificacao, tipoAcomodacao,
                nome, cpf, dataCheckin, dataCheckout, precosDiariosSelect, totalSelect,
                criancas_ate_7 = null, criancas_mais_7 = null) {

                criancas_ate_7 = criancas_ate_7 || document.getElementById('criancas_ate_7').value;
                criancas_mais_7 = criancas_mais_7 || document.getElementById('criancas_mais_7').value;
                adultos = document.getElementById('adultos').value;
        
                console.log('adicionarQuartoAoCart', quartoId, quartoNumero, quartoAndar, quartoClassificacao, tipoAcomodacao, nome, cpf, dataCheckin, dataCheckout, precosDiariosSelect, totalSelect);
                console.log(precosDiariosSelect);
                console.log(totalSelect);
        
                const precosData = await obterPlanosPrecos(quartoId, dataCheckin, dataCheckout, criancas_ate_7, criancas_mais_7);
                var precosDiarios = precosDiariosSelect || precosData.precosDiarios;
                const totalf = totalSelect || precosData.total;
                const total = parseFloat(totalf); // Garantir que total seja um número

                
                console.log(precosDiarios);
        
                const isFormattedDate = (dateStr) => {
                    const regex = /^\d{2}-\d{2}-\d{4}$/;
                    return regex.test(dateStr);
                };
        
                // Função para formatar a data de yyyy-mm-dd hh:mm:ss para dd-mm-yyyy
                const formatDate = (dateStr) => {
                    if (dateStr.includes('-')) {
                        const [datePart] = dateStr.split(' ');
                        const [year, month, day] = datePart.split('-');
                        return `${day}-${month}-${year}`;
                    } else if (dateStr.includes('/')) {
                        const [day, month, year] = dateStr.split('/');
                        return `${day}-${month}-${year}`;
                    }
                    return dateStr; // Retorna a string original se não corresponder a nenhum formato esperado
                };
        
                // Verificar e formatar as datas se necessário
                var formattedCheckin = isFormattedDate(dataCheckin) ? dataCheckin : formatDate(dataCheckin);
                var formattedCheckout = isFormattedDate(dataCheckout) ? dataCheckout : formatDate(dataCheckout);
        
                console.log('Adicionando quarto ao carrinho', quartoId, quartoNumero, quartoAndar, quartoClassificacao, nome, cpf, dataCheckin, dataCheckout, precosDiarios, total);
        
                // Atualizar o localStorage do carrinho
                let cart = JSON.parse(localStorage.getItem('cart')) || [];
                cart.push({ quartoId, quartoNumero, quartoAndar, quartoClassificacao, nome, cpf, criancas_ate_7, criancas_mais_7, adultos, dataCheckin, dataCheckout, precosDiarios, total });
                localStorage.setItem('cart', JSON.stringify(cart));
        
                // Atualizar o total geral
                atualizarValorTotalDoCart();
                console.log(precosDiarios);
                if (!Array.isArray(precosDiarios)) {
                    precosDiarios = Object.entries(precosDiarios).map(([data, preco]) => ({
                        data,
                        preco
                    }));
                }
                
                   // Remove o segundo índice
                console.log('antes', precosDiarios);
                // if (precosDiarios.length > 1) {
                //     precosDiarios.splice(1, 1);
                // }
                console.log('depois', precosDiarios);
                
                const renderPrecosDiarios = (precosDiarios, quartoId, dataCheckin, dataCheckout) => {
                    return precosDiarios.map((item, index) => `
                        <div class='subtotal-forday'>
                            <span>${formatDate(item.data)}:</span>
                            <input type="number" class="preco-diario form-group" data-index="${index}" data-quarto-id="${quartoId}" data-checkin="${dataCheckin}" data-checkout="${dataCheckout}" value="${parseFloat(item.preco).toFixed(2)}" step="0.01" min="0">
                        </div>
                    `).join('');
                };
                // Adicionar o item ao carrinho visualmente
                const cartItems = document.getElementById('cart-items');
                const cartItem = document.createElement('div');
                cartItem.classList.add('cart-item');
            
                cartItem.innerHTML = `
                    <div class="card">
                        <div class="info">
                            <i class="icon fas fa-door-closed"></i>
                            <strong>Quarto Número:</strong> 
                            <span style="float:right;">${quartoNumero}</span>
                            <input type="hidden" name="quartos[${quartoId}][numero]" value="${quartoNumero}">
                        </div>
                        <div class="info">
                            <i class="icon fas fa-building"></i>
                            <strong>Andar:</strong> 
                            <span style="float:right;">${quartoAndar}</span>
                            <input type="hidden" name="quartos[${quartoId}][andar]" value="${quartoAndar}">
                        </div>
                        <div class="info">
                            <i class="icon fas fa-star"></i>
                            <strong>Classificação:</strong> 
                            <span style="float:right;">${quartoClassificacao}</span>
                            <input type="hidden" name="quartos[${quartoId}][classificacao]" value="${quartoClassificacao}">
                        </div>
                        <div class="info">
                            <i class="icon fas fa-bed"></i>
                            <strong>Tipo de Acomodação:</strong> 
                            <select name="quartos[${quartoId}][tipo_acomodacao]" class="form-control">
                                <option value="Solteiro" ${tipoAcomodacao === 'Solteiro' || tipoAcomodacao === '' ? 'selected' : ''}>
                                    Solteiro
                                </option>   
                                <option value="Casal" ${tipoAcomodacao === 'Casal' ? 'selected' : ''}>Casal</option>
                            </select>
                        </div>
                            <div class="info">
                                <i class="icon fas fa-user"></i>
                                <strong>Adultos:</strong> 
                                <input type="number" class="form-control" name="quartos[${quartoId}][adultos]" value="${adultos}" readonly>
                            </div>
                            <div class="info">
                                <i class="icon fas fa-child"></i>
                                <strong>Crianças até 7 anos:</strong> 
                                <input type="number" class="form-control" name="quartos[${quartoId}][criancas_ate_7]" value="${criancas_ate_7}" readonly>
                            </div>
                            <div class="info">
                                <i class="icon fas fa-child"></i>
                                <strong>Crianças mais de 7 anos:</strong> 
                                <input type="number" class="form-control" name="quartos[${quartoId}][criancas_mais_7]" value="${criancas_mais_7}" readonly>
                            </div>
                        
                        <div class="info">
                            <i class="icon fas fa-user"></i>
                            <strong>Responsável:</strong> 
                            <input type="text" class="form-control" name="quartos[${quartoId}][responsavel_nome]" value="${nome}">
                        </div>
                        <div class="info">
                            <i class="icon fas fa-id-card"></i>
                            <strong>CPF:</strong> 
                            <input type="text" class="form-control cart-items-cpf" name="quartos[${quartoId}][responsavel_cpf]" value="${cpf}">
                        </div>
                        <div class="info">
                            <i class="icon fas fa-calendar-check"></i>
                            <strong>Check-in:</strong> 
                            <span style="float:right;">${formattedCheckin}</span>
                            <input type="hidden" name="quartos[${quartoId}][data_checkin]" value="${formattedCheckin}">
                        </div>
                        <div class="info">
                            <i class="icon fas fa-calendar-check"></i>
                            <strong>Check-out:</strong>
                            <span style="float:right;">${formattedCheckout}</span>
                            <input type="hidden" name="quartos[${quartoId}][data_checkout]" value="${formattedCheckout}">
                        </div>
                        <div class="info">
                            <i class="icon fas fa-calendar-alt"></i>
                            <strong>Preços Diários:</strong>
                                ${renderPrecosDiarios(precosDiarios, quartoId, dataCheckin, dataCheckout)}
                        </div>
                        <div class="info">
                            <i class="icon fas fa-dollar-sign"></i>
                            <strong>Valor Total:</strong> 
                            <span id="valor-total-${quartoId}" style="float:right;">R$ ${total.toFixed(2).replace('.', ',')}</span>
                            <input type="hidden" name="quartos[${quartoId}][total]" id="input-valor-total-${quartoId}" value="${total.toFixed(2)}">
                        </div>
                        <a class="btn btn-danger remove-quarto" data-quarto-id="${quartoId}" data-checkin="${dataCheckin}" data-checkout="${dataCheckout}">Remover</a>   
                    </div>
                `;

            cartItems.appendChild(cartItem);
        
        
            // Adicionar máscara ao campo CPF
            $('.cart-items-cpf').mask('000.000.000-00', {reverse: true});
        
            // Desabilitar o botão "Selecionar Quarto"
            const selectButton = document.querySelector(`.select-quarto[data-quarto-id="${quartoId}"]`);
            if (selectButton) {
                selectButton.classList.add('disabled');
                selectButton.setAttribute('disabled', 'disabled');
            }
        
            // Adicionar event listener para remover o quarto do carrinho
                        document.querySelectorAll('.remove-quarto').forEach(button => {
                button.addEventListener('click', function() {
                    const cartItem = this.closest('.cart-item');
                    const quartoId = this.getAttribute('data-quarto-id');
                    const dataCheckin = this.getAttribute('data-checkin');
                    const dataCheckout = this.getAttribute('data-checkout');
                    console.log(`Removendo quartoId: ${quartoId}, dataCheckin: ${dataCheckin}, dataCheckout: ${dataCheckout}`);
                    cartItem.remove();
                    removerQuartoDoCart(quartoId, dataCheckin, dataCheckout);
                    
                    responsaveis = responsaveis.filter(responsavel => responsavel.quartoId !== quartoId);
            
                    // Reabilitar o botão "Selecionar Quarto"
                    const selectButton = document.querySelector(`.select-quarto[data-quarto-id="${quartoId}"]`);
                    if (selectButton) {
                        const dataEntrada = document.querySelector('input[name="data_entrada"]').value;
                        let dataEntradaDate;
                        if (dataEntrada.includes('/')) {
                            // Formato DD/MM/YYYY
                            const [day, month, year] = dataEntrada.split('/');
                            dataEntradaDate = new Date(`${year}-${month}-${day}`);
                        } else if (dataEntrada.includes(' ')) {
                            dataEntradaDate = new Date(dataEntrada.split(' ')[0]);
                        } else {
                            dataEntradaDate = new Date(dataEntrada);
                        }
            
                        const cart = JSON.parse(localStorage.getItem('cart')) || [];
                        const dataCheckoutConflict = cart.some(item => {
                            let itemDataCheckout;
                            if (item.dataCheckout.includes('/')) {
                                // Formato DD/MM/YYYY
                                const [day, month, year] = item.dataCheckout.split('/');
                                itemDataCheckout = new Date(`${year}-${month}-${day}`);
                            } else {
                                // Formato YYYY-MM-DD HH:MM:SS
                                itemDataCheckout = new Date(item.dataCheckout.split(' ')[0]);
                            }
                            console.log(`Comparando dataEntradaDate: ${dataEntradaDate} com itemDataCheckout: ${itemDataCheckout} |  item.dataCheckout: ${item.dataCheckout}`);
                            return item.quartoId === String(quartoId) && dataEntradaDate <= itemDataCheckout;
                        });
            
                        if (!dataCheckoutConflict) {
                            selectButton.classList.remove('disabled');
                            selectButton.removeAttribute('disabled');
                        }
                    }
            
                    // Atualizar o total geral após a remoção
                    atualizarValorTotalDoCart();
                });
            });

            // Adicionar event listeners para atualizar o valor total ao alterar os preços diários
            const precoDiarioInputs = cartItem.querySelectorAll('.preco-diario');
            const valorTotalSpan = document.getElementById(`valor-total-${quartoId}`);
            const inputValorTotal = document.getElementById(`input-valor-total-${quartoId}`);
        
                       function atualizarValorTotal() {
                let total = 0;
                let cart = JSON.parse(localStorage.getItem('cart')) || [];
            
                precoDiarioInputs.forEach(input => {
                    const valorDiario = parseFloat(input.value) || 0;
                    total += valorDiario;
            
                    // Atualiza o item correspondente no carrinho
                    const quartoId = input.getAttribute('data-quarto-id');
                    const dataCheckin = input.getAttribute('data-checkin');
                    const dataCheckout = input.getAttribute('data-checkout');
                    const dataIndex = input.getAttribute('data-index');
            
                    const cartItem = cart.find(item => 
                        item.quartoId === quartoId && 
                        formatDate(item.dataCheckin) === formatDate(dataCheckin) && 
                        formatDate(item.dataCheckout) === formatDate(dataCheckout)
                    );
            
                    if (cartItem) {
                        // Atualiza o valor diário no objeto precosDiarios
                        const dataKey = Object.keys(cartItem.precosDiarios)[dataIndex];
                        cartItem.precosDiarios[dataKey] = valorDiario.toFixed(2);
            
                        // Recalcula o total do item
                        cartItem.total = Object.values(cartItem.precosDiarios).reduce((acc, val) => acc + parseFloat(val), 0).toFixed(2);
                    }
                });
            
                // Atualiza o localStorage com o novo total
                localStorage.setItem('cart', JSON.stringify(cart));
            
                valorTotalSpan.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
                inputValorTotal.value = total.toFixed(2);
            
                // Chama a função para atualizar o total geral do carrinho
                atualizarValorTotalDoCart();
            }
            
            
            // Adiciona o evento de input para atualizar o valor total
            precoDiarioInputs.forEach(input => {
                input.addEventListener('input', atualizarValorTotal);
            });
            
            atualizarValorTotal();
            

        
            // Exibir o Cart Preview com animação
            const cartColumn = document.getElementById('cart-col');
            cartColumn.style.display = 'block'; // Torna o elemento visível
            cartColumn.classList.add('fade-in-right'); // Adiciona a animação de entrada
        }
        
        // Função para remover o quarto do carrinho
            function removerQuartoDoCart(quartoId, dataCheckin, dataCheckout) {
                let cart = JSON.parse(localStorage.getItem('cart')) || [];
                console.log('Cart antes da remoção:', cart);
                cart = cart.filter(item => {
                    const match = item.quartoId === String(quartoId) && item.dataCheckin === dataCheckin && item.dataCheckout === dataCheckout;
                    console.log(`Verificando item: ${JSON.stringify(item)}, match: ${match}`);
                    return !match;
                });
                console.log('Cart após a remoção:', cart);
                localStorage.setItem('cart', JSON.stringify(cart));
                console.log(`Quarto ${quartoId} removido do carrinho`);
                
                // Atualizar o total geral após a remoção
                atualizarValorTotalDoCart();
            }
        
        // Função para atualizar o valor total do carrinho
        function atualizarValorTotalDoCart() {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let totalGeral = cart.reduce((acc, item) => acc + parseFloat(item.total), 0);
        
            // Verifica se o elemento total-cart-value existe
            const totalField = document.getElementById('total-cart-value');
            if (totalField) {
                // Atualiza o campo do total no carrinho se ele existir
                totalField.innerHTML = `R$ ${totalGeral.toFixed(2).replace('.', ',')}`;
            } else {
                console.warn('Elemento com ID total-cart-value não encontrado no DOM');
            }
        }



    function abrirModal(quartoId, quartoNumero, quartoAndar, quartoClassificacao, precosDiarios, total) {
        document.getElementById('responsavelModal').setAttribute('data-quarto-id', quartoId);
        document.getElementById('responsavelModal').setAttribute('data-quarto-numero', quartoNumero);
        document.getElementById('responsavelModal').setAttribute('data-quarto-andar', quartoAndar);
        document.getElementById('responsavelModal').setAttribute('data-quarto-classificacao', quartoClassificacao);
        document.getElementById('responsavelModal').setAttribute('data-precos-diarios', JSON.stringify(precosDiarios));
        document.getElementById('responsavelModal').setAttribute('data-total', total);
        
        $('#responsavelModal').modal('show');
    }

     // Função para mostrar os quartos disponíveis
    function mostrarQuartosDisponiveis(data) {
        console.log('data no mostrarQuartosDisponiveis', data);
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        console.log(cart);
    
        // Verifica se já existem quartos na data para evitar requisição AJAX
        if (data.quartos && data.quartos.length > 0) {
            montarOutputQuartosDisponiveis(data.quartos, data.dataEntrada, data.dataSaida);
            return; // Não faz a requisição AJAX se já houver quartos
        }
    
        // Faz a requisição AJAX para verificar a disponibilidade
        fetch('/admin/verificar-disponibilidade', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.APP_CSRF_TOKEN // Token CSRF para segurança
            },
            body: JSON.stringify({
                data_entrada: data.dataEntrada,
                data_saida: data.dataSaida,
                tipo_quarto: data.tipoQuarto,
                apartamentos: data.apartamentos,
                adultos: data.adultos,
                criancas_ate_7: data.criancas_ate_7,
                criancas_mais_7: data.criancas_mais_7
            })
        })
        .then(response => response.json())
        .then(resp => {
            if (resp.success) {
                montarOutputQuartosDisponiveis(resp.quartos, data.dataEntrada, data.dataSaida);
            } else if (resp.errors) {
                // Se houver erros de validação, mostra os erros
                let output = '<h3>Erros de Validação</h3><ul style="list-style-type: none;">';
                for (const [field, messages] of Object.entries(resp.errors)) {
                    messages.forEach(message => {
                        output += `<li class="notice-error">${message}</li>`;
                    });
                }
                output += '</ul>';
                document.getElementById('resultadoDisponibilidade').innerHTML = output;
            } else {
                // Se não houver disponibilidade, mostra a mensagem de erro
                document.getElementById('resultadoDisponibilidade').innerHTML = `<p>${resp.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Erro ao verificar disponibilidade:', error);
            alert('Erro ao verificar disponibilidade.');
        });
    }
    
    async function montarOutputQuartosDisponiveis(quartos, dataEntrada, dataSaida) {
        console.log('datas no output', dataEntrada, dataSaida);
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        let output = '<h3>Quartos Disponíveis</h3><div class="row">';
    
        for (const quarto of quartos) {
            const quartoNoCart = cart.some(item => item.quartoId === String(quarto.id));
            console.log(quartoNoCart);
            console.log(quarto.id);
    
            let precosDiarios = quarto.precosDiarios || {};
            let total = 0;
            let count = Object.keys(precosDiarios).length;
            let index = 0;
            let precosHTML = '';
            let precosDiariosArray = [];
    
            // Se o quarto não tiver plano de preço, requisitar utilizando a função obterPlanosPrecos
            if (count === 0) {
                try {
                    const precosData = await obterPlanosPrecos(quarto.id, dataEntrada, dataSaida);
                    precosDiarios = precosData.precosDiarios;
                    total = parseFloat(precosData.total).toFixed(2);
                } catch (error) {
                    console.error('Erro ao obter planos de preços:', error);
                    alert('Erro ao obter planos de preços.');
                    continue; // Pula para o próximo quarto
                }
            }
    
            console.log(precosDiarios);
    
            for (const [data, preco] of Object.entries(precosDiarios)) {
                if (index < count ) {
                    total += parseFloat(preco);
                    let dateParts = data.split('-');
                    let formattedDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]).toLocaleDateString('pt-BR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    console.log(formattedDate);
                    precosHTML += `<div class="d-flex justify-content-between"><span>${formattedDate}:</span> <span>R$ ${parseFloat(preco).toFixed(2).replace('.', ',')}</span></div>`;
                    precosDiariosArray.push({ data: formattedDate, preco: parseFloat(preco).toFixed(2) });
                }
                index++;
            }
    
            total = parseFloat(total);
    
            // Verificar se a dataEntrada é maior que a dataCheckout de qualquer quarto no carrinho
            let dataEntradaDate;
            if (dataEntrada.includes('/')) {
                    // Formato DD/MM/YYYY
                    const [day, month, year] = dataEntrada.split('/');
                    dataEntradaDate = new Date(`${year}-${month}-${day}`);
                } else if (dataEntrada.includes(' ')) {
                    dataEntradaDate = new Date(dataEntrada.split(' ')[0]);
                } else {
                    dataEntradaDate = new Date(dataEntrada);
             }
    
            console.log(`dataEntrada: ${dataEntrada}, dataEntradaDate: ${dataEntradaDate}`);

            const dataCheckoutConflict = cart.some(item => {
                let itemDataCheckout;
                if (item.dataCheckout.includes('/')) {
                    // Formato DD/MM/YYYY
                    const [day, month, year] = item.dataCheckout.split('/');
                    itemDataCheckout = new Date(`${year}-${month}-${day}`);
                } else if (item.dataCheckout.includes('-')) {
                    // Formato YYYY-MM-DD ou DD-MM-YYYY
                    const parts = item.dataCheckout.split('-');
                    if (parts[0].length === 4) {
                        // Formato YYYY-MM-DD
                        itemDataCheckout = new Date(item.dataCheckout.split(' ')[0]);
                    } else {
                        // Formato DD-MM-YYYY
                        const [day, month, year] = parts;
                        itemDataCheckout = new Date(`${year}-${month}-${day}`);
                    }
                } else {
                    // Formato desconhecido
                    itemDataCheckout = new Date(item.dataCheckout);
                }
            
                console.log(`Comparando dataEntradaDate: ${dataEntradaDate} com itemDataCheckout: ${itemDataCheckout} |  item.dataCheckout: ${item.dataCheckout}`);
                return item.quartoId === String(quarto.id) && dataEntradaDate <= itemDataCheckout;
            });
            const isDisabled = quartoNoCart && dataCheckoutConflict;

    
            output += `
            <div class="col-md-4">
                <div class="card">
                    <div class="info">
                        <i class="icon fas fa-door-closed"></i>
                        <strong>Quarto Número:</strong> 
                        ${quarto.numero}
                    </div>
                    <div class="info">
                        <i class="icon fas fa-building"></i>
                        <strong>Andar:</strong> ${quarto.andar}
                    </div>
                    <div class="info">
                        <i class="icon fas fa-star"></i>
                        <strong>Classificação:</strong> ${quarto.classificacao}
                    </div>
                    <div class="info"> 
                        <i class="icon fas fa-bed"></i>
                        <strong>Tabela de Preço:</strong> 
                        ${precosHTML}
                    </div>
                    <div class="info">
                        <i class="icon fas fa-dollar-sign"></i>
                        <strong>Valor Total:</strong> R$ ${total.toFixed(2).replace('.', ',')}
                    </div>
                    <a class="btn btn-primary select-quarto ${isDisabled ? 'disabled' : ''}" data-quarto-id="${quarto.id}" data-quarto-numero="${quarto.numero}" data-quarto-andar="${quarto.andar}" data-quarto-classificacao="${quarto.classificacao}" data-precos-diarios='${JSON.stringify(precosDiariosArray)}' data-total="${total.toFixed(2)}">
                        Selecionar Quarto
                    </a>
                </div>
            </div>`;
        }
    
        output += '</div>';
        document.getElementById('resultadoDisponibilidade').innerHTML = output;
    

        document.querySelectorAll('.select-quarto').forEach(button => {
            button.addEventListener('click', function() {
                const solicitanteHospede = document.getElementById('solicitanteHospedeCheckbox').checked;
                const quartoId = this.getAttribute('data-quarto-id');
                const quartoNumero = this.getAttribute('data-quarto-numero');
                const quartoAndar = this.getAttribute('data-quarto-andar');
                const quartoClassificacao = this.getAttribute('data-quarto-classificacao');
                const tipoAcomodacao = document.querySelector('select[name="tipo_acomodacao"]').value;
                const dataCheckin = document.querySelector('input[name="data_entrada"]').value;
                const dataCheckout = document.querySelector('input[name="data_saida"]').value;
                const precosDiarios = JSON.parse(this.getAttribute('data-precos-diarios'));
                const total = this.getAttribute('data-total');
                const criancas_ate_7 = document.getElementById('criancas_ate_7').value;
                const criancas_mais_7 = document.getElementById('criancas_mais_7').value;

                if (solicitanteHospede && responsaveis.length === 0) {
                    const nome = document.getElementById('nomeSolicitante').value;
                    const cpf = document.getElementById('cpf').value;
                    responsaveis.push({ quartoId, nome, cpf });
                    adicionarQuartoAoCart(
                        quartoId, quartoNumero, quartoAndar, 
                        quartoClassificacao, tipoAcomodacao, 
                        nome, cpf, dataCheckin, dataCheckout, 
                        precosDiarios, total, criancas_ate_7, criancas_mais_7);
                } else if (solicitanteHospede && responsaveis.length > 0) {
                    abrirModal(quartoId, quartoNumero, quartoAndar, quartoClassificacao, precosDiarios, total);
                } else {
                    abrirModal(quartoId, quartoNumero, quartoAndar, quartoClassificacao, precosDiarios, total);
                }
            });
        });
    }



        document.getElementById('saveResponsavel').addEventListener('click', function() {
            const nome = document.getElementById('responsavelNome').value;
            const cpf = document.getElementById('responsavelCpf').value;
            const quartoId = document.getElementById('responsavelModal').getAttribute('data-quarto-id');
            const quartoNumero = document.getElementById('responsavelModal').getAttribute('data-quarto-numero');
            const quartoAndar = document.getElementById('responsavelModal').getAttribute('data-quarto-andar');
            const quartoClassificacao = document.getElementById('responsavelModal').getAttribute('data-quarto-classificacao');
            const tipoAcomodacao = document.querySelector('select[name="tipo_acomodacao"]').value;
    
            const dataCheckin = document.querySelector('input[name="data_entrada"]').value;
            const dataCheckout = document.querySelector('input[name="data_saida"]').value;
            const precosDiarios = JSON.parse(document.getElementById('responsavelModal').getAttribute('data-precos-diarios'));
            const total = document.getElementById('responsavelModal').getAttribute('data-total');

            const criancas_ate_7 = document.getElementById('criancas_ate_7').value;
            const criancas_mais_7 = document.getElementById('criancas_mais_7').value;
    
            const cpfJaExiste = responsaveis.some(responsavel => responsavel.cpf === cpf);
    
            if (cpfJaExiste && cpf != '') {
                alert('CPF já está atrelado a um quarto, informe um novo.');
            } else {
                console.log(precosDiarios, total);
                responsaveis.push({ quartoId, nome, cpf });
                adicionarQuartoAoCart(
                    quartoId, 
                    quartoNumero, 
                    quartoAndar, 
                    quartoClassificacao, 
                    tipoAcomodacao, 
                    nome, 
                    cpf, 
                    dataCheckin, 
                    dataCheckout, 
                    precosDiarios, 
                    total,
                    criancas_ate_7,
                    criancas_mais_7
                );
                
                $('#responsavelModal').modal('hide');
                document.querySelector('.modal-footer .btn.btn-secondary').click();  
            }
        });
    })

    </script>







@endsection