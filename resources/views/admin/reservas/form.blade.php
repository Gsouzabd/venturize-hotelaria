@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo'). ' Reserva')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.reserva-form save-route="admin.reservas.save" back-route="admin.reservas.index" submit-title="Finalizar" class="reservarForm">
        <div class="col-md-9">
            <!-- Seu formulário existente aqui -->
            <!-- Tabs para as seções -->
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
                // Verificar os parâmetros da URL
                const urlParams = new URLSearchParams(window.location.search);
                const quartoId = urlParams.get('quarto_id');
                const quartoNumero = urlParams.get('quarto_numero');
                const quartoClassificacao = urlParams.get('quarto_classificacao');
                const quartoAndar = urlParams.get('quarto_andar');
                const formatDate = (dateString) => {
                const date = new Date(dateString);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
                const year = date.getFullYear();
                return `${day}-${month}-${year}`;
                };

                const dataCheckin = formatDate(urlParams.get('data_checkin'));
                const dataCheckout = formatDate(urlParams.get('data_checkout'));
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
                   
                    localStorage.removeItem('cart');
                   
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
                    adicionarQuartoAoCart(quartoId, quartoNumero, quartoAndar, quartoClassificacao, nome, cpf);
                    
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



    function adicionarQuartoAoCart(quartoId, quartoNumero, quartoAndar, quartoClassificacao, nome, cpf) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        cart.push({ quartoId, quartoNumero, quartoAndar, quartoClassificacao, nome, cpf });
        localStorage.setItem('cart', JSON.stringify(cart));
   
        const cartItems = document.getElementById('cart-items');
        const cartItem = document.createElement('div');
        cartItem.classList.add('cart-item');
        cartItem.innerHTML = `
            <div class="card">
                <div class="info">
                    <i class="icon fas fa-door-closed"></i>
                    <strong>Quarto Número:</strong> ${quartoNumero}
                    <input type="hidden" name="quartos[${quartoId}][numero]" value="${quartoNumero}">
                </div>
                <div class="info">
                    <i class="icon fas fa-building"></i>
                    <strong>Andar:</strong> ${quartoAndar}
                    <input type="hidden" name="quartos[${quartoId}][andar]" value="${quartoAndar}">
                </div>
                <div class="info">
                    <i class="icon fas fa-star"></i>
                    <strong>Classificação:</strong> ${quartoClassificacao}
                    <input type="hidden" name="quartos[${quartoId}][classificacao]" value="${quartoClassificacao}">
                </div>
                <div class="info">
                    <i class="icon fas fa-user"></i>
                    <strong>Responsável:</strong> <input type="text" class="form-control" name="quartos[${quartoId}][responsavel_nome]" value="${nome}">

                </div>
                <div class="info">
                    <i class="icon fas fa-id-card"></i>
                    <strong>CPF:</strong> <input type="text" class="form-control cart-items-cpf" name="quartos[${quartoId}][responsavel_cpf]" value="${cpf}">

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
                            <strong>Quarto Número:</strong> ${quarto.numero}
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

                if (solicitanteHospede && responsaveis.length === 0) {
                    // Se o solicitante é o hóspede e ainda não há responsáveis, use os dados do formulário anterior
                    const nome = document.getElementById('nomeSolicitante').value;
                    const cpf = document.getElementById('cpf').value;
                    responsaveis.push({ quartoId, nome, cpf });
                    adicionarQuartoAoCart(quartoId, quartoNumero, quartoAndar, quartoClassificacao, nome, cpf);
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

        responsaveis.push({ quartoId, nome, cpf });
        adicionarQuartoAoCart(quartoId, quartoNumero, quartoAndar, quartoClassificacao, nome, cpf);
        $('#responsavelModal').modal('hide');
        document.querySelector('.modal-footer .btn.btn-secondary').click();
        
    });

            const saveInfoButton = document.getElementById('saveInfoButton');
            const disponibilidadeTabLink = document.getElementById('disponibilidade-tab');
            const formFields = document.querySelectorAll('#informacoes-gerais input, #informacoes-gerais textarea');

            saveInfoButton.addEventListener('click', function () {
                let formIsValid = true;

                formFields.forEach(function (field) {
                    if (!field.checkValidity()) {
                        formIsValid = false;
                        field.reportValidity();
                    }
                });

                if (formIsValid) {
                    // Habilita a tab de Disponibilidade
                    disponibilidadeTabLink.classList.remove('disabled');
                    disponibilidadeTabLink.click(); // Alterna para a tab de Disponibilidade
                }
            });

            const buscarCpfButton = document.getElementById('buscarCpfButton');
            const cpfInput = document.getElementById('cpf');
            const clienteInfo = document.getElementById('clienteInfo');
            const modalElement = document.getElementById('criarClienteModal');
            const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
            $('#cpf').mask('000.000.000-00', {reverse: true});
            $('#responsavelCpf').mask('000.000.000-00', {reverse: true});
            $('#responsavelReservaCpf').mask('000.000.000-00', {reverse: true});


            buscarCpfButton.addEventListener('click', function () {
                const cpf = cpfInput.value;

                // Faz uma requisição AJAX para buscar o cliente pelo CPF
                fetch(`/admin/clientes/cpf/${cpf}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            // Preenche os campos do cliente se encontrado
                            document.getElementById('nomeSolicitante').value = data.nome ?? '';
                            document.getElementById('cpf').value = data.cpf ?? '';
                            document.getElementById('rg').value = data.rg ?? '';
                            document.getElementById('modal_email').value = data.email ?? '';
                            document.getElementById('celular').value = data.celular ?? '';
                        }
                    })
                    .catch(error => {
                        // console.error('Erro ao buscar o cliente:', error);
                        cpfError.style.display = 'block'; // Mostra a mensagem de erro
                        console.log(error);
                        

                        // Esconde a mensagem de erro após 5 segundos
                        setTimeout(() => {
                            cpfError.style.display = 'none';
                        }, 2000);
                    });
            });

            const verificarCnpjFaturamentoButton = document.getElementById('verificarCnpjFaturamento');
            const verificarCnpjSolicitanteButton = document.getElementById('verificarCnpjSolicitante');
            const cnpjFaturamentoError = document.getElementById('cnpjFaturamentoError');

            const cnpjSolicitanteError = document.getElementById('cnpjSolicitanteError');

            verificarCnpjFaturamentoButton.addEventListener('click', function () {
                let cnpj = document.getElementById('cnpj_faturamento').value;
                cnpj = cnpj.replace(/[^\d]+/g, '');

                fetch(`/admin/buscar-empresa/${cnpj}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.id) {
                            document.getElementById('nome_fantasia_faturamento').value = data.nome_fantasia || data.razao_social;
                            document.getElementById('empresa_faturamento_id').value = data.id;
                            
                            
                            cnpjFaturamentoError.style.display = 'none'; // Esconde a mensagem de erro
                        } else {
                            cnpjFaturamentoError.style.display = 'block'; // Mostra a mensagem de erro
                            setTimeout(() => {
                                cnpjFaturamentoError.style.display = 'none';
                            }, 5000); // Esconde a mensagem de erro após 5 segundos
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar a empresa:', error);
                        cnpjFaturamentoError.style.display = 'block'; // Mostra a mensagem de erro
                        setTimeout(() => {
                            cnpjFaturamentoError.style.display = 'none';
                        }, 5000); // Esconde a mensagem de erro após 5 segundos
                    });
            });

            verificarCnpjSolicitanteButton.addEventListener('click', function () {
                let cnpj = document.getElementById('cnpj_solicitante').value;
                console.log(cnpj);
               
                cnpj = cnpj.replace(/[^\d]+/g, '');

                fetch(`/admin/buscar-empresa/${cnpj}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.id){
                            document.getElementById('nome_fantasia_solicitante').value = data.nome_fantasia || data.razao_social;
                            document.getElementById('empresa_solicitante_id').value = data.id;
                        
                           
                            cnpjSolicitanteError.style.display = 'none'; // Esconde a mensagem de erro
                        } else {
                            cnpjSolicitanteError.style.display = 'block'; // Mostra a mensagem de erro
                            setTimeout(() => {
                                cnpjSolicitanteError.style.display = 'none';
                            }, 5000); // Esconde a mensagem de erro após 5 segundos
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar a empresa:', error);
                        cnpjSolicitanteError.style.display = 'block'; // Mostra a mensagem de erro
                        setTimeout(() => {
                            cnpjSolicitanteError.style.display = 'none';
                        }, 5000); // Esconde a mensagem de erro após 5 segundos
                    });
                
            });


   
        });

        function validateCPF(cpf) {
            cpf = cpf.replace(/[^\d]+/g, ''); // Remove all non-numeric characters

            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                return false; // Invalid CPF length or all digits are the same
            }

            let sum = 0;
            let remainder;

            for (let i = 1; i <= 9; i++) {
                sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            }

            remainder = (sum * 10) % 11;

            if (remainder === 10 || remainder === 11) {
                remainder = 0;
            }

            if (remainder !== parseInt(cpf.substring(9, 10))) {
                return false;
            }

            sum = 0;

            for (let i = 1; i <= 10; i++) {
                sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            }

            remainder = (sum * 10) % 11;

            if (remainder === 10 || remainder === 11) {
                remainder = 0;
            }

            if (remainder !== parseInt(cpf.substring(10, 11))) {
                return false;
            }

            return true;
        }
    </script>
@endsection