@extends('layouts.admin.master')
{{-- @php dd($reserva) @endphp --}}

@section('title', ($edit ? 'Editando' : 'Inserindo'). ' Reserva')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.reserva-form save-route="admin.reservas.save" back-route="admin.reservas.index" submit-title="Finalizar" class="reservarForm">
        <div class="col-md-9">
            <ul class="nav nav-tabs mb-4" id="reservaTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="informacoes-gerais-tab" data-toggle="tab" href="#informacoes-gerais" role="tab" aria-controls="informacoes-gerais" aria-selected="true">
                        <i class="fas fa-info-circle"></i> Informações Gerais
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" id="disponibilidade-tab" data-toggle="tab" href="#disponibilidade" role="tab" aria-controls="disponibilidade" aria-selected="false">
                        <i class="fas fa-calendar-alt"></i> Disponibilidade
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
                        andar: quartoAndar
                    }]
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
            const criancas = document.getElementById('criancas').value;

            // Faz a requisição AJAX para verificar a disponibilidade
            fetch('/admin/verificar-disponibilidade', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.APP_CSRF_TOKEN // Token CSRF para segurança
                },
                body: JSON.stringify({
                    data_entrada: dataEntrada,
                    data_saida: dataSaida,
                    tipo_quarto: tipoQuarto,
                    apartamentos: apartamentos,
                    adultos: adultos,
                    criancas: criancas
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarQuartosDisponiveis(data)

                } else if (data.errors) {

                        // Se houver erros de validação, mostra os erros
                        let output = '<h3>Erros de Validação</h3><ul style="list-style-type: none;">';
                        for (const [field, messages] of Object.entries(data.errors)) {
                            messages.forEach(message => {
                                output += `<li class="notice-error">${message}</li>`;
                            });
                        }
                        output += '</ul>';
                        document.getElementById('resultadoDisponibilidade').innerHTML = output;
                    } else {
                        // Se não houver disponibilidade, mostra a mensagem de erro
                        document.getElementById('resultadoDisponibilidade').innerHTML = `<p>${data.message}</p>`;
                    }
                })

            .catch(error => {
                console.error('Erro ao verificar disponibilidade:', error);
                alert('Erro ao verificar disponibilidade.');
            });
        });



        function adicionarQuartoAoCart(quartoId, quartoNumero, quartoAndar, quartoClassificacao, tipoAcomodacao, nome, cpf, dataCheckin, dataCheckout) {
            console.log('Adicionando quarto ao carrinho', quartoId, quartoNumero, quartoAndar, quartoClassificacao, nome, cpf, dataCheckin, dataCheckout);
            // Função para verificar se a data está no formato dd-mm-yyyy
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


            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart.push({ quartoId, quartoNumero, quartoAndar, quartoClassificacao, nome, cpf, dataCheckin, dataCheckout });
            localStorage.setItem('cart', JSON.stringify(cart));
        
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
                        <strong>Responsável:</strong> <input type="text" class="form-control" name="quartos[${quartoId}][responsavel_nome]" value="${nome}">
                    </div>
                    <div class="info">
                        <i class="icon fas fa-id-card"></i>
                        <strong>CPF:</strong> <input type="text" class="form-control cart-items-cpf" name="quartos[${quartoId}][responsavel_cpf]" value="${cpf}">
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
                    <a class="btn btn-danger remove-quarto" data-quarto-id="${quartoId}">Remover</a>
                </div>
            `;
            cartItems.appendChild(cartItem);
     
            $('.cart-items-cpf').mask('000.000.000-00', {reverse: true});
            // Desabilitar o botão "Selecionar Quarto"
            const selectButton = document.querySelector(`.select-quarto[data-quarto-id="${quartoId}"]`);
            if (selectButton) {
                selectButton.classList.add('disabled');
                selectButton.setAttribute('disabled', 'disabled');
            }

            

            // Adicionar evento de clique para o botão de remover quarto
            cartItem.querySelector('.remove-quarto').addEventListener('click', function() {
                const cartItem = this.closest('.cart-item');
                const quartoId = this.getAttribute('data-quarto-id');
                console.log('quartoId', quartoId);
                
                
                cartItem.remove();
                removerQuartoDoCart(quartoId);

                responsaveis = responsaveis.filter(responsavel => responsavel.quartoId !== quartoId);

                // Reabilitar o botão "Selecionar Quarto"
                const selectButton = document.querySelector(`.select-quarto[data-quarto-id="${quartoId}"]`);
                if (selectButton) {
                    selectButton.classList.remove('disabled');
                    selectButton.removeAttribute('disabled');
                }
            });

            // Exibir o Cart Preview com animação
            const cartColumn = document.getElementById('cart-col');
            cartColumn.style.display = 'block'; // Torna o elemento visível
            cartColumn.classList.add('fade-in-right'); // Adiciona a animação de entrada

        }

    // {{-- Script do cart quando for tela de edição --}}
    if(window.location.href.indexOf('edit') > -1) {
        adicionarQuartoAoCart(
            "{{ $reserva->quarto_id ?? '' }}",
            "{{ $reserva->quarto->numero ?? '' }}",
            "{{ $reserva->quarto->andar ?? '' }}",
            "{{ $reserva->quarto->classificacao ?? '' }}",
            "{{ $reserva->tipo_acomodacao ?? '' }}",
            "{{ $reserva->clienteResponsavel->nome ?? '' }}",
            "{{ $reserva->clienteResponsavel->cpf ?? '' }}",
            "{{ $reserva->data_checkin ?? '' }}",
            "{{ $reserva->data_checkout ?? '' }}"
        );
    }


    function abrirModal(quartoId, quartoNumero, quartoAndar, quartoClassificacao) {
        document.getElementById('responsavelModal').setAttribute('data-quarto-id', quartoId);
        document.getElementById('responsavelModal').setAttribute('data-quarto-numero', quartoNumero);
        document.getElementById('responsavelModal').setAttribute('data-quarto-andar', quartoAndar);
        document.getElementById('responsavelModal').setAttribute('data-quarto-classificacao', quartoClassificacao);
        $('#responsavelModal').modal('show');
    }

     // Função para mostrar os quartos disponíveis
     function mostrarQuartosDisponiveis(data) {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        console.log(cart);
        
        
        let output = '<h3>Quartos Disponíveis</h3><div class="row">';
        data.quartos.forEach(quarto => {
            const quartoNoCart = cart.some(item => item.quartoId === String(quarto.id));
            console.log(quartoNoCart);
            console.log(quarto.id);
            
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
                        <a class="btn btn-primary select-quarto ${quartoNoCart ? 'disabled' : ''}" data-quarto-id="${quarto.id}" data-quarto-numero="${quarto.numero}" data-quarto-andar="${quarto.andar}" data-quarto-classificacao="${quarto.classificacao}" >Selecionar Quarto</a>
                    </div>
                </div>`;
        });
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

                

                if (solicitanteHospede && responsaveis.length === 0) {
                    // Se o solicitante é o hóspede e ainda não há responsáveis, use os dados do formulário anterior
                    const nome = document.getElementById('nomeSolicitante').value;
                    const cpf = document.getElementById('cpf').value;
                    responsaveis.push({ quartoId, nome, cpf });
                    adicionarQuartoAoCart(quartoId, quartoNumero, quartoAndar, quartoClassificacao, tipoAcomodacao, nome, cpf, dataCheckin, dataCheckout);
                } else if (solicitanteHospede && responsaveis.length > 0) {
                    // Se o solicitante já é responsável por um quarto, abrir o modal para adicionar novo responsável
                    abrirModal(quartoId, quartoNumero, quartoAndar, quartoClassificacao);
                } else {
                    // Se o solicitante não é o hóspede, abrir o modal para adicionar novo responsável
                    abrirModal(quartoId, quartoNumero, quartoAndar, quartoClassificacao);
                }
            });
        });
    }

    // Função para remover um quarto do carrinho
    function removerQuartoDoCart(quartoId) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        cart = cart.filter(item => item.quartoId !== String(quartoId));
        localStorage.setItem('cart', JSON.stringify(cart));
        console.log(`Quarto ${quartoId} removido do carrinho`);
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
        const cpfJaExiste = responsaveis.some(responsavel => responsavel.cpf === cpf);

        if (cpfJaExiste) {
            alert('CPF já está atrelado a um quarto, informe um novo.');
        } else {
            responsaveis.push({ quartoId, nome, cpf });
            adicionarQuartoAoCart(quartoId, quartoNumero, quartoAndar, quartoClassificacao, tipoAcomodacao, nome, cpf, dataCheckin, dataCheckout);
            
            $('#responsavelModal').modal('hide');
            document.querySelector('.modal-footer .btn.btn-secondary').click();  
        }
    });
})

    </script>







@endsection