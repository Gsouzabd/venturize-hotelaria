// Função para adicionar um quarto ao carrinho
async function adicionarQuartoAoCart(
    quartoId, quartoNumero, quartoAndar, 
    quartoClassificacao, tipoAcomodacao,
    nome, cpf, dataCheckin, dataCheckout, precosDiariosSelect, totalSelect,
    criancas_ate_7 = null, criancas_mais_7 = null, adultos = null, acompanhantes = null, quartoComposicao = null) {

    console.log('acompanhantes', acompanhantes);

    criancas_ate_7 = criancas_ate_7 || document.getElementById('criancas_ate_7').value;
    criancas_mais_7 = criancas_mais_7 || document.getElementById('criancas_mais_7').value;
    adultos = adultos || document.getElementById('adultos').value;

    criancas_ate_7 = criancas_ate_7 !== '' ? parseInt(criancas_ate_7) : 0;
    criancas_mais_7 = criancas_mais_7 !== '' ? parseInt(criancas_mais_7) : 0;
    adultos = adultos !== '' ? parseInt(adultos) : 0;

    if(!quartoComposicao){  quartoComposicao = document.querySelector('select[name="composicao_quarto"] ').value; }


    console.log('adicionarQuartoAoCart', quartoId, quartoNumero, quartoAndar, quartoClassificacao, tipoAcomodacao, nome, cpf, dataCheckin, dataCheckout, precosDiariosSelect, totalSelect);
 

    const precosData = await obterPlanosPrecos(quartoId, dataCheckin, dataCheckout, quartoComposicao);
    var precosDiarios = precosDiariosSelect ?? precosData.precosDiarios;
    const totalf = totalSelect || precosData.total;
    const total = parseFloat(totalf); // Garantir que total seja um número
    console.log(precosDiariosSelect);
    // console.log(totalSelect);
    // console.log(precosDiarios);

    const isFormattedDate = (dateStr) => {
        const regex = /^\d{2}-\d{2}-\d{4}$/;
        return regex.test(dateStr);
    };

    // Função para formatar a data de yyyy-mm-dd hh:mm:ss para dd-mm-yyyy
    const formatDate = (dateStr) => {
        if (typeof dateStr !== 'string') {
            dateStr = String(dateStr);
        }
        if (dateStr.includes('-')) {
            const [year, month, day] = dateStr.split('-');
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

    // Atualizar o localStorage do carrinho
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let reservaIdInput = '';
    const isEdit = document.querySelector('input[name="is_edit"]').value;
    var reservaId = document.querySelector('input[name="reserva_id"]').value;



    if(isEdit == '1' && cart.length == 0){
        reservaIdInput = `<input type="hidden" name="quartos[${quartoId}][reserva_id]" value="${reservaId}">`;
    }else{
        reservaId = ''
    }


    console.log('adicionando ao carrinho', quartoId, quartoNumero, quartoAndar, quartoClassificacao, nome, cpf, criancas_ate_7, criancas_mais_7, adultos, dataCheckin, dataCheckout, precosDiarios, total,quartoComposicao);
    
    // Create a deep copy of the object to avoid reference issues (acompanhantes: só quando vêm do servidor na edição)
    const itemToAddPayload = {
        quartoId,
        quartoNumero,
        quartoAndar,
        quartoClassificacao,
        nome,
        cpf,
        criancas_ate_7,
        criancas_mais_7,
        adultos,
        dataCheckin,
        dataCheckout,
        precosDiarios,
        total,
        reservaId,
        quartoComposicao,
    };
    if (acompanhantes && Array.isArray(acompanhantes) && acompanhantes.length > 0) {
        itemToAddPayload.acompanhantes = JSON.parse(JSON.stringify(acompanhantes));
    }
    const itemToAdd = JSON.parse(JSON.stringify(itemToAddPayload));
    
    cart.push(itemToAdd);
    localStorage.setItem('cart', JSON.stringify(cart));
    console.log('cart depois da adição', cart)
    // Atualizar o total geral
    atualizarValorTotalDoCart();
    if (!Array.isArray(precosDiarios)) {
        precosDiarios = Object.entries(precosDiarios).map(([data, preco]) => ({
            data,
            preco
        }));
    }

    const renderPrecosDiarios = (precosDiarios, quartoId, dataCheckin, dataCheckout) => {
        let html = '';
        for (let i = 0; i < precosDiarios.length; i += 3) {
            html += '<div class="row">';
            for (let j = i; j < i + 3 && j < precosDiarios.length; j++) {
                const item = precosDiarios[j];
                html += `
                    <div class="col-md-4 subtotal-forday">
                        <span>${formatDate(item.data)}:</span>
                        <input type="hidden" name="quartos[${quartoId}][precos_diarios][${j}][data]" value="${item.data}"> 
                        <input type="number" class="preco-diario form-group" data-date="${item.data}" data-index="${j}" data-quarto-id="${quartoId}" data-checkin="${dataCheckin}" data-checkout="${dataCheckout}" value="${parseFloat(item.preco).toFixed(2)}" step="0.01" min="0">
                    </div>
                `;
            }
            html += '</div>';
        }
        return html;
    };


    const renderAcompanhantes = (acompanhantes, quartoId, edit = null) => {
        let acompanhantesHtml = '';
        acompanhantes.forEach((acompanhante, index) => {
            // console.log(acompanhante)
            const { tipo, nome, cpf, data_nascimento, email, telefone } = acompanhante;
    
            // Ignorar o primeiro índice se o tipo for "Adulto"
            if (tipo === 'Adulto' && index === 0 && !edit) {
                return;
            }
            const acompIdAttr = acompanhante.id
                ? ` data-acompanhante-id="${String(parseInt(acompanhante.id, 10))}"`
                : '';
            acompanhantesHtml += `
            <div class="acompanhante"${acompIdAttr}>
                <h5>(${index + 1}) ${tipo} </h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="info">
                            <i class="icon fas fa-user"></i>
                            <span>Nome:</span>
                            <input type="text" class="form-control" name="quartos[${quartoId}][acompanhantes][${tipo}][${index}][nome]" value="${nome ?? ''}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <i class="icon fas fa-id-card"></i>
                            <span>CPF:</span>
                            <input type="text" class="form-control cpf-mask" name="quartos[${quartoId}][acompanhantes][${tipo}][${index}][cpf]" value="${cpf}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <i class="icon fas fa-calendar"></i>
                            <span>Data Nascimento:</span>
                            <input type="date" class="form-control" name="quartos[${quartoId}][acompanhantes][${tipo}][${index}][data_nascimento]" value="${data_nascimento}">
                        </div>
                    </div>
                    ${tipo === 'Adulto' ? `
                    <div class="col-md-4">
                        <div class="info">
                            <i class="icon fas fa-envelope"></i>
                            <span>Email:</span>
                            <input type="email" class="form-control" name="quartos[${quartoId}][acompanhantes][${tipo}][${index}][email]" value="${email ?? ''}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <i class="icon fas fa-phone"></i>
                            <span>Telefone:</span>
                            <input type="text" class="form-control" name="quartos[${quartoId}][acompanhantes][${tipo}][${index}][telefone]" value="${telefone ?? ''}">
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
            `;
        });
        return acompanhantesHtml;
    };

    let acompanhantesHtml = '';
    if (acompanhantes && acompanhantes.length > 0) {
        acompanhantesHtml = renderAcompanhantes(acompanhantes, quartoId, true);
    } else {
        // Renderizar acompanhantes calculados dinamicamente
        acompanhantesHtml += renderAcompanhantes(Array(adultos).fill({ tipo: 'Adulto' }), quartoId);
        acompanhantesHtml += renderAcompanhantes(Array(criancas_mais_7).fill({ tipo: 'Criança 8 a 12 anos' }), quartoId);
        acompanhantesHtml += renderAcompanhantes(Array(criancas_ate_7).fill({ tipo: 'Criança até 7 anos' }), quartoId);
    }

    


    // Adicionar o item ao carrinho visualmente
    const cartItems = document.getElementById('cart-items');
    const cartItem = document.createElement('div');
    cartItem.classList.add('cart-item');
    cartItem.innerHTML = `
    <div class="card">
        ${reservaIdInput}
        <!-- UH Section -->
        <div class="row border-bottom mb-3">
            <div class="col-md-12">
                <h5>UH</h5>
            </div>
            <div class="col-md-4">
                <div class="info">
                    <i class="icon fas fa-door-closed"></i>
                    <strong>Quarto:</strong> 
                    <span style="float:right;">${quartoNumero}</span>
                    <input type="hidden" name="quartos[${quartoId}][numero]" value="${quartoNumero}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="info">
                    <i class="icon fas fa-building"></i>
                    <strong>Andar:</strong> 
                    <span style="float:right;">${quartoAndar}</span>
                    <input type="hidden" name="quartos[${quartoId}][andar]" value="${quartoAndar}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="info">
                    <i class="icon fas fa-star"></i>
                    <strong>Tipo:</strong> 
                    <span style="float:right;">${quartoClassificacao} (${quartoComposicao})</span>
                    <input type="hidden" name="quartos[${quartoId}][classificacao]" value="${quartoClassificacao}">
                    <input type="hidden" name="quartos[${quartoId}][composicao]" value="${quartoComposicao}">

                </div>
            </div>
        </div>
        <!-- Quantidade Hospede(s) Section -->
        <div class="row border-bottom mb-3">
            <div class="col-md-12">
                <h5>Quantidade Hospede(s)</h5>
            </div>
            <div class="col-md-4">
                <div class="info">
                    <i class="icon fas fa-user"></i>
                    <strong>Adultos:</strong> 
                    <input type="number" class="form-control" name="quartos[${quartoId}][adultos]" value="${adultos}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info">
                    <i class="icon fas fa-child"></i>
                    <strong>Crianças até 7:</strong> 
                    <input type="number" class="form-control" name="quartos[${quartoId}][criancas_ate_7]" value="${criancas_ate_7}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info">
                    <i class="icon fas fa-child"></i>
                    <strong>Crianças 7+:</strong> 
                    <input type="number" class="form-control" name="quartos[${quartoId}][criancas_mais_7]" value="${criancas_mais_7}" readonly>
                </div>
            </div>
        </div>
        <!-- Responsável Section -->
        <div class="row border-bottom mb-3">
            <div class="col-md-12">
                <h5>Responsável</h5>
            </div>
            <div class="col-md-6">
                <div class="info">
                    <i class="icon fas fa-user"></i>
                    <strong>Nome:</strong> 
                    <input type="text" class="form-control" name="quartos[${quartoId}][responsavel_nome]" value=${nome}>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info">
                    <i class="icon fas fa-id-card"></i>
                    <strong>CPF:</strong> 
                    <input type="text" class="form-control cart-items-cpf" name="quartos[${quartoId}][responsavel_cpf]" value="${cpf}">
                </div>
            </div>
        </div>
        <!-- Acompanhantes Section -->
        <div class="row border-bottom mb-3">
            <div class="col-md-12">
                <div class="info">
                    <i class="icon fas fa-users"></i>
                    <strong>Acompanhantes:</strong>
                    <div class="acompanhantes" id="acompanhantes-container">
                        ${acompanhantesHtml}
                    </div>
                </div>
            </div>
        </div>
        <!-- Check-in/Check-out Section -->
        <div class="row border-bottom mb-3">
            <div class="col-md-12">
                <h5>Check-in/Check-out</h5>
            </div>
            <div class="col-md-6">
                <div class="info">
                    <i class="icon fas fa-calendar-check"></i>
                    <strong>Check-in:</strong>
                    <input type="date" class="form-control mt-1 cart-date-checkin" data-quarto-id="${quartoId}"
                        name="quartos[${quartoId}][data_checkin]"
                        value="${(function(d){ var p=d.split('-'); return p.length===3&&p[0].length===2 ? p[2]+'-'+p[1]+'-'+p[0] : d; })(formattedCheckin)}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="info">
                    <i class="icon fas fa-calendar-check"></i>
                    <strong>Check-out:</strong>
                    <input type="date" class="form-control mt-1 cart-date-checkout" data-quarto-id="${quartoId}"
                        name="quartos[${quartoId}][data_checkout]"
                        value="${(function(d){ var p=d.split('-'); return p.length===3&&p[0].length===2 ? p[2]+'-'+p[1]+'-'+p[0] : d; })(formattedCheckout)}">
                </div>
            </div>
        </div>
        <!-- Preços Diários Section -->
        <div class="row border-bottom mb-3">
            <div class="col-md-12">
                <div class="info">
                    <i class="icon fas fa-calendar-alt"></i>
                    <strong>Preços Diários:</strong>
                    <div id="precos-diarios-container-${quartoId}">
                        ${renderPrecosDiarios(precosDiarios, quartoId, dataCheckin, dataCheckout)}
                    </div>
                </div>
            </div>
        </div>
        <!-- Valor Total Section -->
        <div class="row border-bottom mb-3">
            <div class="col-md-12">
                <div class="info">
                    <i class="icon fas fa-dollar-sign"></i>
                    <strong>Valor Total:</strong> 
                    <span id="valor-total-${quartoId}" style="float:right; padding-top: 4px;">R$ ${total.toFixed(2).replace('.', ',')}</span>
                    <input type="hidden" name="quartos[${quartoId}][total]" id="input-valor-total-${quartoId}" value="${total.toFixed(2)}">
                </div>
            </div>
        </div>

        <!-- Remover Button Section -->
        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-danger remove-quarto" data-quarto-id="${quartoId}" data-checkin="${dataCheckin}" data-checkout="${dataCheckout}">Remover</a>
            </div>
        </div>
    </div>
`;



    cartItems.appendChild(cartItem);

    // Adicionar máscara ao campo CPF
    $('.cart-items-cpf').mask('000.000.000-00', {reverse: true});
    $(".cpf-mask").mask('000.000.000-00', {reverse: true});


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
            console.log('cartItem.precosDiarios', input.value);

            const valorDiario = parseFloat(input.value) || 0;
            total += valorDiario;

            // Atualiza o item correspondente no carrinho
            const quartoId = input.getAttribute('data-quarto-id');
            const dataCheckin = input.getAttribute('data-checkin');
            const dataCheckout = input.getAttribute('data-checkout');
            const dataIndex = input.getAttribute('data-index');
            const data = input.getAttribute('data-date');

            if (!dataCheckin || !dataCheckout || !data) {
                console.error('Data inválida encontrada:', { dataCheckin, dataCheckout, data });
                return;
            }

            const cartItem = cart.find(item => 
                item.quartoId === quartoId && 
                formatDate(item.dataCheckin) === formatDate(dataCheckin) && 
                formatDate(item.dataCheckout) === formatDate(dataCheckout)
            );

            if (cartItem) {
                // Inicializa precosDiarios como um array se não estiver definido
                if (!cartItem.precosDiarios) {
                    cartItem.precosDiarios = [];
                }

                if (!Array.isArray(cartItem.precosDiarios)) {
                    // Converte o objeto em um array de valores
                    cartItem.precosDiarios = Object.entries(cartItem.precosDiarios).map(([key, value]) => {
                        if (typeof value === 'object' && value !== null && 'preco' in value) {
                            return value;
                        } else {
                            return { data: key, preco: value };
                        }
                    });
                }
                console.log('cartItem.precosDiariosDepois', cartItem.precosDiarios);

                // Atualiza o valor diário no objeto precosDiarios, marcando como override manual
                const precoDiario = { data: formatDate(data), preco: valorDiario.toFixed(2), precoManual: true };
                cartItem.precosDiarios[dataIndex] = precoDiario;

                // Recalcula o total do item
                cartItem.total = cartItem.precosDiarios.reduce((acc, val) => acc + parseFloat(val.preco), 0).toFixed(2);
            }
        });

        // Atualiza o localStorage com o novo total
        localStorage.setItem('cart', JSON.stringify(cart));

        valorTotalSpan.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
        inputValorTotal.value = total.toFixed(2);

        // Chama a função para atualizar o total geral do carrinho
        atualizarValorTotalDoCart();

        //verifique quantos quartos no cart do localstorage e altere o numero do span #numero-do-quarto
        const numeroQuartos = cart.length + 1;
        const quantidadeDeApartamentos = document.getElementById('apartamentos').value;
        if(numeroQuartos > quantidadeDeApartamentos){
            document.getElementById('avancarPagamento').removeAttribute('disabled');
            document.getElementById('avancarPagamento').classList.remove('disabled');
        }else{
            document.getElementById('numero-do-quarto').textContent = numeroQuartos;
        }
    }

    // Adiciona o evento de input para atualizar o valor total
    precoDiarioInputs.forEach(input => {
        input.addEventListener('input', atualizarValorTotal);
    });

    atualizarValorTotal();

    // Event listeners para editar as datas diretamente no cart
    const cartCheckinInput  = cartItem.querySelector('.cart-date-checkin');
    const cartCheckoutInput = cartItem.querySelector('.cart-date-checkout');

    let recalcDebounceTimer = null;
    async function recalcularAoMudarDatas() {
        const newCheckinIso  = cartCheckinInput  ? cartCheckinInput.value  : '';
        const newCheckoutIso = cartCheckoutInput ? cartCheckoutInput.value : '';
        if (!newCheckinIso || !newCheckoutIso || newCheckoutIso <= newCheckinIso) return;

        // Converter yyyy-mm-dd → dd-mm-yyyy para a API
        const toFmt = (iso) => { const [y,m,d] = iso.split('-'); return `${d}-${m}-${y}`; };
        const checkinFmt  = toFmt(newCheckinIso);
        const checkoutFmt = toFmt(newCheckoutIso);

        const precosDiariosContainer = document.getElementById(`precos-diarios-container-${quartoId}`);

        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const cartEntry = cart.find(item => String(item.quartoId) === String(quartoId));

        // Ajuste imediato client-side: recalcular quantidade de diárias pelo número de dias
        const numDias = Math.round((new Date(newCheckoutIso) - new Date(newCheckinIso)) / 86400000);
        if (precosDiariosContainer && numDias > 0) {
            const precosExistentes = {};
            (cartEntry?.precosDiarios || []).forEach(p => { if (p && p.data) precosExistentes[p.data] = p; });
            const ultimoPreco = cartEntry?.precosDiarios?.slice(-1)[0]?.preco ?? 0;

            const tempPrecos = [];
            for (let i = 0; i < numDias; i++) {
                const d = new Date(newCheckinIso);
                d.setDate(d.getDate() + i);
                const iso = d.toISOString().split('T')[0];
                const fmt = toFmt(iso);
                const existente = precosExistentes[fmt];
                if (existente) {
                    tempPrecos.push({ data: fmt, preco: existente.preco, ...(existente.precoManual ? { precoManual: true } : {}) });
                } else {
                    tempPrecos.push({ data: fmt, preco: ultimoPreco });
                }
            }
            precosDiariosContainer.innerHTML = renderPrecosDiarios(tempPrecos, quartoId, checkinFmt, checkoutFmt);

            // Atualizar localStorage imediatamente com a contagem correta
            if (cartEntry) {
                cartEntry.dataCheckin  = checkinFmt;
                cartEntry.dataCheckout = checkoutFmt;
                cartEntry.precosDiarios = tempPrecos;
                cartEntry.total = tempPrecos.reduce((acc, p) => acc + parseFloat(p.preco), 0).toFixed(2);
                localStorage.setItem('cart', JSON.stringify(cart));
            }
            atualizarValorTotalDoCart();
        }

        if (precosDiariosContainer) {
            const loadingEl = document.createElement('div');
            loadingEl.className = 'text-muted py-1 recalc-loading';
            loadingEl.innerHTML = '<small>Atualizando preços...</small>';
            precosDiariosContainer.prepend(loadingEl);
        }

        try {
            const precosData = await obterPlanosPrecos(quartoId, checkinFmt, checkoutFmt, quartoComposicao);
            if (!precosData || !precosData.precosDiarios) {
                if (precosDiariosContainer) precosDiariosContainer.innerHTML = '<div class="text-danger"><small>Erro ao buscar preços.</small></div>';
                return;
            }
            let novosPrecoDiarios = Array.isArray(precosData.precosDiarios)
                ? precosData.precosDiarios
                : Object.entries(precosData.precosDiarios).map(([data, preco]) => ({ data, preco }));

            // Preservar dias com preço editado manualmente (precoManual: true)
            const manuaisExistentes = {};
            (cartEntry?.precosDiarios || []).forEach(p => {
                if (p && p.precoManual) manuaisExistentes[p.data] = p.preco;
            });
            novosPrecoDiarios = novosPrecoDiarios.map(p => {
                if (manuaisExistentes[p.data] !== undefined) {
                    return { data: p.data, preco: manuaisExistentes[p.data], precoManual: true };
                }
                return p;
            });
            const novoTotal = novosPrecoDiarios.reduce((acc, p) => acc + parseFloat(p.preco), 0);

            // Atualizar container de preços diários diretamente pelo ID
            if (precosDiariosContainer) {
                const loadingEl = precosDiariosContainer.querySelector('.recalc-loading');
                if (loadingEl) loadingEl.remove();
                precosDiariosContainer.innerHTML = renderPrecosDiarios(novosPrecoDiarios, quartoId, checkinFmt, checkoutFmt);
                // Re-attach event listeners nos novos inputs
                precosDiariosContainer.querySelectorAll('.preco-diario').forEach(inp => {
                    inp.addEventListener('input', function () {
                        let t = 0;
                        cartItem.querySelectorAll('.preco-diario').forEach(i => { t += parseFloat(i.value) || 0; });
                        valorTotalSpan.textContent = `R$ ${t.toFixed(2).replace('.', ',')}`;
                        inputValorTotal.value = t.toFixed(2);
                        atualizarValorTotalDoCart();
                    });
                });
            }

            // Atualizar total display
            valorTotalSpan.textContent = `R$ ${novoTotal.toFixed(2).replace('.', ',')}`;
            inputValorTotal.value = novoTotal.toFixed(2);

            // Atualizar localStorage
            if (cartEntry) {
                cartEntry.dataCheckin  = checkinFmt;
                cartEntry.dataCheckout = checkoutFmt;
                cartEntry.precosDiarios = novosPrecoDiarios;
                cartEntry.total = novoTotal.toFixed(2);
                localStorage.setItem('cart', JSON.stringify(cart));
            }
            atualizarValorTotalDoCart();
        } catch (e) {
            console.error('Erro ao recalcular preços para novas datas:', e);
            if (precosDiariosContainer) {
                const loadingEl = precosDiariosContainer.querySelector('.recalc-loading');
                if (loadingEl) loadingEl.remove();
            }
        }
    }

    function recalcDebounced() {
        clearTimeout(recalcDebounceTimer);
        recalcDebounceTimer = setTimeout(recalcularAoMudarDatas, 400);
    }

    if (cartCheckinInput)  { cartCheckinInput.addEventListener('change',  recalcularAoMudarDatas); cartCheckinInput.addEventListener('input',   recalcDebounced); }
    if (cartCheckoutInput) { cartCheckoutInput.addEventListener('change', recalcularAoMudarDatas); cartCheckoutInput.addEventListener('input',  recalcDebounced); }

    // Recalcular preços ao carregar o cart apenas em reservas novas.
    // Em edição, os preços salvos (incluindo override manual) são autoritativos.
    if (isEdit !== '1') {
        recalcularAoMudarDatas();
    }

    // Exibir o Cart Preview com animação
    const cartColumn = document.getElementById('cart-col');
    cartColumn.style.display = 'block'; // Torna o elemento visível
    cartColumn.classList.add('fade-in-right'); // Adiciona a animação de entrada


}


const saveInfoButton = document.getElementById('saveInfoButton');
const disponibilidadeTabLink = document.getElementById('disponibilidade-tab');
const formFields = document.querySelectorAll('#informacoes-gerais input, #informacoes-gerais textarea, #informacoes-gerais select');
const situacao_reserva = document.querySelector('select[name="situacao_reserva"]');

// Garantir que RG nunca seja obrigatório no front
const rgInput = document.getElementById('rg');
if (rgInput) {
    rgInput.removeAttribute('required');
    rgInput.setCustomValidity('');
}

saveInfoButton.addEventListener('click', function () {
    if (situacao_reserva && situacao_reserva.value == 'RESERVADO') {
        if (validateInformacoesGerais()) {
            if (disponibilidadeTabLink) disponibilidadeTabLink.classList.remove('disabled');
            if (typeof $ !== 'undefined' && $('#disponibilidade-tab').length) {
                $('#disponibilidade-tab').tab('show');
            } else if (disponibilidadeTabLink) {
                disponibilidadeTabLink.click();
            }
        }
    } else {
        if (disponibilidadeTabLink) disponibilidadeTabLink.classList.remove('disabled');
        if (typeof $ !== 'undefined' && $('#disponibilidade-tab').length) {
            $('#disponibilidade-tab').tab('show');
        } else if (disponibilidadeTabLink) {
            disponibilidadeTabLink.click();
        }
    }
});

// Atualizar rótulo da aba Disponibilidade conforme tipo de reserva (Day Use vs Disponibilidade)
document.addEventListener('DOMContentLoaded', function () {
    const tipoReservaSelect = document.querySelector('select[name="tipo_reserva"]');
    const tabLabel = document.getElementById('disponibilidade-tab-label');
    const tabLink = document.getElementById('disponibilidade-tab');
    function updateDisponibilidadeTabLabel() {
        if (!tabLabel || !tipoReservaSelect || !tabLink) return;
        const label = tipoReservaSelect.value === 'DAY_USE' ? (tabLink.getAttribute('data-label-dayuse') || 'Day Use') : (tabLink.getAttribute('data-label-default') || 'Disponibilidade');
        tabLabel.textContent = label;
    }
    updateDisponibilidadeTabLabel();
    if (tipoReservaSelect) tipoReservaSelect.addEventListener('change', updateDisponibilidadeTabLabel);
});

function validateInformacoesGerais() {
    let isValid = true;
    formFields.forEach(field => {
        // Ignorar completamente o campo RG na validação de front
        if (field.id === 'rg' || field.name === 'rg') {
            field.classList.remove('is-invalid');
            field.setCustomValidity('');
            return;
        }

        if (!field.checkValidity()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.setCustomValidity(''); // Limpa a mensagem de erro personalizada
            field.classList.remove('is-invalid');
        }
    });

    if (isValid) {
        // Habilita a tab de Disponibilidade
        disponibilidadeTabLink.classList.remove('disabled');
        disponibilidadeTabLink.click(); // Alterna para a tab de Disponibilidade
    } else {
        // Exibe uma mensagem de erro genérica se o formulário não for válido
        alert('Por favor, preencha todos os campos obrigatórios corretamente.');
    }

    return isValid;
}

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
    // $('#responsavelReservaModal').modal('show');

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
    document.querySelector('select[name="tipo_quarto"]').value = quartoClassificacao;


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
        var situacao_reserva = document.getElementById('tipoReserva').value;
        nome = document.getElementById('responsavelReservaNome').value;
        cpf = document.getElementById('responsavelReservaCpf').value;
        document.getElementById('nomeSolicitante').value = nome;
        document.getElementById('cpf').value = cpf;
        const solicitanteHospedeCheckbox = document.getElementById('solicitanteHospedeCheckbox');
        if (solicitanteHospedeCheckbox) {
            solicitanteHospedeCheckbox.checked = solicitanteHospedeModal;
        }
        document.querySelector('select[name="situacao_reserva"]').value = situacao_reserva;
        if(!solicitanteHospedeModal){
            nome = '';
            cpf = '';
        }

        // responsaveis.push({ quartoId, nome, cpf });
        // adicionarQuartoAoCart(
        //     quartoId, 
        //     quartoNumero, 
        //     quartoAndar, 
        //     quartoClassificacao, 
        //     '',
        //     nome, 
        //     cpf, 
        //     formatDate(urlParams.get('data_checkin')), 
        //     formatDate(urlParams.get('data_checkout')),
        // );     
    });
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
}






document.getElementById('verificarDisponibilidade').addEventListener('click', function() {
    // Obtém os valores do formulário
    const dataEntrada = document.querySelector('input[name="data_entrada"] ').value;
    const dataSaida = document.querySelector('input[name="data_saida"] ').value;
    const tipoQuarto = document.querySelector('select[name="tipo_quarto"] ').value;
    const quartoComposicao = document.querySelector('select[name="composicao_quarto"] ').value;

    const apartamentos = document.getElementById('apartamentos').value;
    const adultos = document.getElementById('adultos').value;
    const criancas_ate_7 = document.getElementById('criancas_ate_7').value;
    const criancas_mais_7 = document.getElementById('criancas_mais_7').value;

    const reservaIdEl = document.querySelector('input[name="reserva_id"]');
    const reservaId = reservaIdEl ? reservaIdEl.value : null;

    const data = {
        dataEntrada,
        dataSaida,
        tipoQuarto,
        apartamentos,
        adultos,
        criancas_ate_7,
        criancas_mais_7,
        quartoComposicao,
        reservaId
    };

    mostrarQuartosDisponiveis(data)
});


// Função para obter planos de preços de um quarto
async function obterPlanosPrecos(quartoId, dataEntrada, dataSaida, composicaoQuarto) {
    let url = `/admin/quartos/${quartoId}/planos-preco?data_entrada=${dataEntrada}&data_saida=${dataSaida}`;
    if (composicaoQuarto) url += `&composicao_quarto=${encodeURIComponent(composicaoQuarto)}`;
    const response = await fetch(url);
    const data = await response.json();
    return data;
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

    const numeroQuartos = cart.length + 1;
    const quantidadeDeApartamentos = document.getElementById('apartamentos').value;
    if(numeroQuartos > quantidadeDeApartamentos){
        document.getElementById('avancarPagamento').removeAttribute('disabled');
        document.getElementById('avancarPagamento').classList.remove('disabled');
    }else{
        document.getElementById('avançarPagamento').setAttribute('disabled', 'disabled');
        document.getElementById('numero-do-quarto').classList.add('disabled');
        document.getElementById('numero-do-quarto').textContent = numeroQuartos;
    }
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
            // apartamentos: data.apartamentos,
            adultos: data.adultos,
            criancas_ate_7: data.criancas_ate_7,
            criancas_mais_7: data.criancas_mais_7,
            composicao_quarto : data.quartoComposicao,
            reserva_id: data.reservaId || null
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
    console.log('quartos disponíveis:', quartos);
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    let output = '<h3>Quartos Disponíveis</h3><div class="row">';

    const quartosArray = Object.values(quartos);
    
    for (const quarto of quartosArray) {
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
            const solicitanteHospedeCheckbox = document.getElementById('solicitanteHospedeCheckbox');
            const solicitanteHospede = solicitanteHospedeCheckbox ? solicitanteHospedeCheckbox.checked : false;
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

    console.log('precosdiarios que vem do modal', precosDiarios);
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
            criancas_mais_7,
        );
        
        $('#responsavelModal').modal('hide');
        document.querySelector('.modal-footer .btn.btn-secondary').click();  
    }
});







// ---> CHECKIN  <--- //
    document.addEventListener('DOMContentLoaded', function () {
        const situacao_reserva = document.querySelector('select[name="situacao_reserva"]');
        const camposNuncaObrigatorios = ['rg', 'telefone', 'passaporte', 'orgao_expedidor', 'profissao', 'estado_civil', 'nacionalidade', 'complemento'];

        function definirRequireds(){
            console.log('situacao_reserva.value', situacao_reserva.value);
            const camposReservado = document.querySelectorAll('#pre-reserva-hide input, #pre-reserva-hide select');
            if (situacao_reserva.value == 'RESERVADO') {
                console.log('irei definir os campos como required');
                camposReservado.forEach(campo => {
                    if (camposNuncaObrigatorios.includes(campo.id) || camposNuncaObrigatorios.includes(campo.name)) {
                        campo.removeAttribute('required');
                        return;
                    }
                    campo.setAttribute('required', 'required');
                });
            } else {
                camposReservado.forEach(campo => campo.removeAttribute('required'));
            }
        }  
    
        definirRequireds();
        situacao_reserva.addEventListener('change', definirRequireds);
        
        const checkinTab = document.querySelector('a#checkin-tab');
        if (!checkinTab) {
            console.error('Elemento com ID checkin-tab não encontrado no DOM');
            return;
        }
        function validateCartItems() {
            const cartItemsContainer = document.getElementById('cart-items');
            const cartItems = cartItemsContainer.querySelectorAll('.cart-item');
            const messages = [];
            let firstInvalidField = null;
            const situacaoReserva = document.querySelector('select[name="situacao_reserva"]');

            if(situacaoReserva.value == 'PRÉ RESERVA'){
                alert('Para finalizar a reserva, é necessário passar a situação de reserva para "RESERVADO".');
                document.querySelector('select[name="situacao_reserva"]').focus();
                document.querySelector('select[name="situacao_reserva"]').classList.add('missing-to-checkin', 'zoom-in');
                document.querySelector('select[name="situacao_reserva"]').addEventListener('input', removeMissingClass);

                return false;
            }

    
            cartItems.forEach((cartItem, index) => {
                const quartoId = cartItem.querySelector('input[name^="quartos["]').name.match(/\d+/)[0];
                const responsavelNome = cartItem.querySelector(`input[name="quartos[${quartoId}][responsavel_nome]"]`);
                const responsavelCpf = cartItem.querySelector(`input[name="quartos[${quartoId}][responsavel_cpf]"]`);
    
                // Validação do responsável
                if (!responsavelNome.value.trim() || !responsavelCpf.value.trim()) {
                    messages.push(`Quarto ${quartoId}: Preencha o nome e CPF do responsável.`);
                    if (!firstInvalidField) {
                        firstInvalidField = !responsavelNome.value.trim() ? responsavelNome : responsavelCpf;
                    }
                    if (!responsavelNome.value.trim()) {
                        responsavelNome.classList.add('missing-to-checkin', 'zoom-in');
                        responsavelNome.addEventListener('input', removeMissingClass);
                    }
                    if (!responsavelCpf.value.trim()) {
                        responsavelCpf.classList.add('missing-to-checkin', 'zoom-in');
                        responsavelCpf.addEventListener('input', removeMissingClass);
                    }
                }
    
                // Validação dos acompanhantes
                const acompanhantes = cartItem.querySelectorAll('.acompanhante');
                acompanhantes.forEach((acompanhante, acompIndex) => {
                    const nome = acompanhante.querySelector('input[name*="[nome]"]');
                    const cpf = acompanhante.querySelector('input[name*="[cpf]"]');
                    const dataNascimento = acompanhante.querySelector('input[name*="[data_nascimento]"]');
    
                    if (!nome.value.trim() || !cpf.value.trim() || !dataNascimento.value.trim()) {
                        messages.push(`Quarto ${quartoId}, Acompanhante ${acompIndex + 1}: Preencha todos os campos (nome, CPF, data de nascimento).`);
                        if (!firstInvalidField) {
                            firstInvalidField = !nome.value.trim() ? nome : (!cpf.value.trim() ? cpf : dataNascimento);
                        }
                        if (!nome.value.trim()) {
                            nome.classList.add('missing-to-checkin', 'zoom-in');
                            nome.addEventListener('input', removeMissingClass);
                        }
                        if (!cpf.value.trim()) {
                            cpf.classList.add('missing-to-checkin', 'zoom-in');
                            cpf.addEventListener('input', removeMissingClass);
                        }
                        if (!dataNascimento.value.trim()) {
                            dataNascimento.classList.add('missing-to-checkin', 'zoom-in');
                            dataNascimento.addEventListener('input', removeMissingClass);
                        }
                    }
                });
            });
    
            if (messages.length > 0) {
                alert(messages.join('\n'));
                if (firstInvalidField) {
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalidField.focus();
                }
    
                // Remove the zoom-in class after the animation completes
                document.querySelectorAll('.zoom-in').forEach(field => {
                    field.addEventListener('animationend', function () {
                        field.classList.remove('zoom-in');
                    }, { once: true });
                });
    
                return false;
            }
            return true;
        }
    
        function removeMissingClass(event) {
            event.target.classList.remove('missing-to-checkin');
        }
    
        checkinTab.addEventListener('click', function (e) {
            if (!validateCartItems() || !validateInformacoesGerais()) {
                e.preventDefault(); // Previne a navegação
                e.stopPropagation(); // Previne que a biblioteca ou outro manipulador processe o evento
            }

        });
    });


        document.getElementById('buscarCepButton').addEventListener('click', function() {
        var cep = document.getElementById('cep').value.replace(/\D/g, '');
    
        if (cep.length !== 8) {
            alert('Por favor, insira um CEP válido.');
            return;
        }
    
        var url = `https://viacep.com.br/ws/${cep}/json/`;
    
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.erro) {
                    document.getElementById('cepError').style.display = 'block';
                    clearAddressFields();
                } else {
                    document.getElementById('cepError').style.display = 'none';
                    document.getElementById('endereco').value = data.logradouro;
                    document.getElementById('bairro').value = data.bairro;
                    document.getElementById('cidade').value = data.localidade;
                    document.getElementById('estado').value = data.uf;
                    document.getElementById('pais').value = 'Brasil'; // Assuming the country is Brazil
                }
            })
            .catch(error => {
                console.error('Error fetching address:', error);
                document.getElementById('cepError').style.display = 'block';
                clearAddressFields();
            });
    });
    
    function clearAddressFields() {
        document.getElementById('endereco').value = '';
        document.getElementById('bairro').value = '';
        document.getElementById('cidade').value = '';
        document.getElementById('estado').value = '';
        document.getElementById('pais').value = '';
    }

    document.getElementById('cpf').addEventListener('input', function() {
        var cpf = document.getElementById('cpf').value.replace(/\D/g, '');
        var cpfField = document.getElementById('cpf');
    
        if (validateCPF(cpf)) {
            document.getElementById('cpfValidateError').style.display = 'none';
            document.getElementById('cpfvalidateRight').style.display = 'block';
            cpfField.style.backgroundColor = 'rgb(220 255 221)';
        } else {
            document.getElementById('cpfValidateError').style.display = 'block';
            cpfField.reportValidity(); // Exibe a mensagem de erro nativa do navegador
            cpfField.focus(); // Foca no campo inválido
            cpfField.style.backgroundColor = '#f8d7da';
            document.getElementById('cpfvalidateRight').style.display = 'none';

        }
    });
    
    function validateCPF(cpf) {
        if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) {
            return false;
        }
    
        var sum = 0;
        var remainder;
    
        for (var i = 1; i <= 9; i++) {
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
    
        for (var i = 1; i <= 10; i++) {
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

document.addEventListener('DOMContentLoaded', function () {
    function activateTabFallback(tabLink) {
        const target = tabLink.getAttribute('href');
        const targetPane = document.querySelector(target);
        if (!targetPane) return;

        const navContainer = tabLink.closest('.nav');
        if (navContainer) {
            navContainer.querySelectorAll('.nav-link').forEach(function (link) {
                link.classList.remove('active');
                link.setAttribute('aria-selected', 'false');
            });
        }

        const tabContent = document.getElementById('reservaTabContent');
        if (tabContent) {
            tabContent.querySelectorAll('.tab-pane').forEach(function (pane) {
                pane.classList.remove('show', 'active');
            });
        }

        tabLink.classList.add('active');
        tabLink.setAttribute('aria-selected', 'true');
        targetPane.classList.add('show', 'active');
    }

    function showTabByHash(hash) {
        if (!hash) return;
        const tabLink = document.querySelector(`a[data-toggle="tab"][href="${hash}"]`);
        if (!tabLink) return;

        if (typeof $ !== 'undefined' && $(tabLink).tab) {
            $(tabLink).tab('show');
            return;
        }

        activateTabFallback(tabLink);
    }

    // Forca comportamento de aba (evita navegar apenas por ancora)
    ['#refeicoes-tab', '#transferencia-tab'].forEach(function (tabId) {
        const tabLink = document.querySelector(tabId);
        if (!tabLink) return;

        tabLink.addEventListener('click', function (event) {
            event.preventDefault();

            if (typeof $ !== 'undefined' && $(this).tab) {
                $(this).tab('show');
                return;
            }

            activateTabFallback(this);
        });

        tabLink.addEventListener('shown.bs.tab', function () {
            const target = this.getAttribute('href');
            if (target && history.replaceState) {
                history.replaceState(null, '', target);
            }
        });
    });

    // Reabre a aba correta ao voltar com hash (ex.: salvar refeicoes)
    if (window.location.hash) {
        showTabByHash(window.location.hash);
    }
});

function _escapeAttrAcomp(s) {
    return String(s ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

function _nextAcompanhanteIndexForTipo(quartoId, tipo) {
    const needle = `[acompanhantes][${tipo}][`;
    const inputs = document.querySelectorAll('#acompanhantes-container input[name*="[nome]"]');
    let max = -1;
    inputs.forEach(function (inp) {
        const n = inp.name || '';
        const pos = n.indexOf(needle);
        if (pos === -1) {
            return;
        }
        const rest = n.slice(pos + needle.length);
        const m = rest.match(/^(\d+)\]\[nome\]$/);
        if (m) {
            max = Math.max(max, parseInt(m[1], 10));
        }
    });
    return max + 1;
}

/** Sincroniza o painel "Reserva Atual" (cart preview) após POST na aba Acompanhantes. */
window.appendAcompanhanteToCartPreview = function (acompanhante) {
    const removeBtn = document.querySelector('#cart-items .remove-quarto');
    if (!removeBtn) {
        return;
    }
    const quartoId = removeBtn.getAttribute('data-quarto-id');
    if (!quartoId) {
        return;
    }
    const container = document.querySelector('#cart-items #acompanhantes-container');
    if (!container) {
        return;
    }

    const tipo = acompanhante.tipo || 'Adulto';
    const index = _nextAcompanhanteIndexForTipo(quartoId, tipo);

    let dataNasc = acompanhante.data_nascimento || '';
    if (dataNasc && dataNasc.indexOf('T') !== -1) {
        dataNasc = dataNasc.split('T')[0];
    }

    const idAttr = acompanhante.id
        ? ` data-acompanhante-id="${String(parseInt(acompanhante.id, 10))}"`
        : '';

    const emailTelefoneHtml =
        tipo === 'Adulto'
            ? `
                    <div class="col-md-4">
                        <div class="info">
                            <i class="icon fas fa-envelope"></i>
                            <span>Email:</span>
                            <input type="email" class="form-control" name="quartos[${quartoId}][acompanhantes][${tipo}][${index}][email]" value="${_escapeAttrAcomp(acompanhante.email)}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <i class="icon fas fa-phone"></i>
                            <span>Telefone:</span>
                            <input type="text" class="form-control" name="quartos[${quartoId}][acompanhantes][${tipo}][${index}][telefone]" value="${_escapeAttrAcomp(acompanhante.telefone)}">
                        </div>
                    </div>`
            : '';

    const block = `
            <div class="acompanhante"${idAttr}>
                <h5>(${index + 1}) ${tipo} </h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="info">
                            <i class="icon fas fa-user"></i>
                            <span>Nome:</span>
                            <input type="text" class="form-control" name="quartos[${quartoId}][acompanhantes][${tipo}][${index}][nome]" value="${_escapeAttrAcomp(acompanhante.nome)}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <i class="icon fas fa-id-card"></i>
                            <span>CPF:</span>
                            <input type="text" class="form-control cpf-mask" name="quartos[${quartoId}][acompanhantes][${tipo}][${index}][cpf]" value="${_escapeAttrAcomp(acompanhante.cpf)}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <i class="icon fas fa-calendar"></i>
                            <span>Data Nascimento:</span>
                            <input type="date" class="form-control" name="quartos[${quartoId}][acompanhantes][${tipo}][${index}][data_nascimento]" value="${_escapeAttrAcomp(dataNasc)}">
                        </div>
                    </div>
                    ${emailTelefoneHtml}
                </div>
            </div>
            `;

    container.insertAdjacentHTML('beforeend', block);
    if (typeof $ !== 'undefined' && $.fn.mask) {
        $(container).find('.cpf-mask').last().mask('000.000.000-00', { reverse: true });
    }

    try {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const item = cart.find(function (c) {
            return String(c.quartoId) === String(quartoId);
        });
        if (item) {
            if (!Array.isArray(item.acompanhantes)) {
                item.acompanhantes = [];
            }
            item.acompanhantes.push({
                id: acompanhante.id,
                tipo: acompanhante.tipo,
                nome: acompanhante.nome,
                cpf: acompanhante.cpf,
                data_nascimento: dataNasc,
                email: acompanhante.email || null,
                telefone: acompanhante.telefone || null,
            });
            localStorage.setItem('cart', JSON.stringify(cart));
        }
    } catch (e) {
        console.warn('appendAcompanhanteToCartPreview: localStorage', e);
    }
};

window.removeAcompanhanteFromCartPreview = function (acompanhanteId) {
    const id = String(acompanhanteId);
    const el = document.querySelector(
        '#cart-items #acompanhantes-container .acompanhante[data-acompanhante-id="' + id + '"]',
    );
    if (el) {
        el.remove();
    }
    try {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        cart.forEach(function (c) {
            if (!Array.isArray(c.acompanhantes)) {
                return;
            }
            c.acompanhantes = c.acompanhantes.filter(function (a) {
                return String(a.id) !== id;
            });
        });
        localStorage.setItem('cart', JSON.stringify(cart));
    } catch (e) {
        console.warn('removeAcompanhanteFromCartPreview: localStorage', e);
    }
};