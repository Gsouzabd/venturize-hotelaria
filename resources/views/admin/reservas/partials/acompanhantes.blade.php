<div class="tab-pane fade" id="acompanhantes" role="tabpanel" aria-labelledby="acompanhantes-tab">
    <h5><i class="fas fa-users"></i> Acompanhantes</h5>

    <table class="table table-bordered table-striped" id="tabela-acompanhantes">
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Tipo</th>
                <th>Nascimento</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reserva->acompanhantes as $acomp)
            <tr id="row-acomp-{{ $acomp->id }}">
                <td>
                    @if($acomp->cliente_id)
                        <a href="{{ route('admin.clientes.edit', ['id' => $acomp->cliente_id]) }}" target="_blank">
                            {{ $acomp->nome }}
                            <i class="fas fa-external-link-alt fa-xs"></i>
                        </a>
                    @else
                        {{ $acomp->nome }}
                    @endif
                </td>
                <td>{{ $acomp->cpf }}</td>
                <td>{{ $acomp->tipo }}</td>
                <td>{{ $acomp->data_nascimento ? \Carbon\Carbon::parse($acomp->data_nascimento)->format('d/m/Y') : '' }}</td>
                <td>{{ $acomp->email }}</td>
                <td>{{ $acomp->telefone }}</td>
                <td>
                    <button type="button"
                        class="btn btn-danger btn-sm btn-remover-acomp"
                        data-id="{{ $acomp->id }}"
                        data-url="{{ route('admin.reservas.acompanhantes.remove', ['id' => $reserva->id, 'aid' => $acomp->id]) }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr>
    <h6><i class="fas fa-user-plus"></i> Adicionar Acompanhante</h6>
    <div id="form-add-acomp">
        <div class="form-group">
            <label>Buscar Cliente Cadastrado</label>
            <select id="acomp_busca_cliente" style="width:100%">
                <option value=""></option>
            </select>
            <small class="form-text text-muted">Digite o nome ou CPF para buscar um cliente já cadastrado e preencher os campos automaticamente.</small>
        </div>
        <input type="hidden" id="acomp_cliente_id">

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nome <span class="text-danger">*</span></label>
                    <input type="text" id="acomp_nome" class="form-control" placeholder="Nome completo">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>CPF</label>
                    <div class="input-group">
                        <input type="text" id="acomp_cpf" class="form-control" placeholder="000.000.000-00">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary" id="btn-buscar-cpf-acomp">Buscar</button>
                        </div>
                    </div>
                    <div id="acomp-cpf-error" class="text-danger mt-1" style="display:none;">Nenhum cliente encontrado com este CPF.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tipo <span class="text-danger">*</span></label>
                    <select id="acomp_tipo" class="form-control">
                        <option value="Adulto">Adulto</option>
                        <option value="Criança 8 a 12 anos">Criança 8 a 12 anos</option>
                        <option value="Criança até 7 anos">Criança até 7 anos</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Data de Nascimento</label>
                    <input type="text" id="acomp_nascimento" class="form-control" placeholder="dd/mm/aaaa">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="acomp_email" class="form-control" placeholder="email@exemplo.com">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" id="acomp_telefone" class="form-control" placeholder="(00) 00000-0000">
                </div>
            </div>
        </div>
        <div id="acomp-error" class="text-danger mb-2" style="display:none;"></div>
        <button type="button" class="btn btn-success" id="btn-salvar-acomp">
            <i class="fas fa-save"></i> Salvar Acompanhante
        </button>
        <a href="{{ route('admin.clientes.create') }}?reserva_id={{ $reserva->id }}" target="_blank" class="btn btn-outline-primary ml-2">
            <i class="fas fa-id-card"></i> Ficha Completa
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addUrl = '{{ route("admin.reservas.acompanhantes.add", ["id" => $reserva->id]) }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || '{{ csrf_token() }}';

    // Máscaras
    if (typeof $ !== 'undefined' && $.fn.mask) {
        $('#acomp_cpf').mask('000.000.000-00', { reverse: true });
        $('#acomp_nascimento').mask('00/00/0000');
    }

    // Preenche os campos com dados de um cliente
    function preencherCamposAcomp(cliente) {
        document.getElementById('acomp_cliente_id').value = cliente.id || '';
        document.getElementById('acomp_nome').value       = cliente.nome || '';
        document.getElementById('acomp_cpf').value        = cliente.cpf || '';
        document.getElementById('acomp_email').value      = cliente.email || '';
        document.getElementById('acomp_telefone').value   = cliente.telefone || cliente.celular || '';
        if (cliente.data_nascimento) {
            const p = cliente.data_nascimento.split('-');
            document.getElementById('acomp_nascimento').value = p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : cliente.data_nascimento;
        } else {
            document.getElementById('acomp_nascimento').value = '';
        }
    }

    // Select2 AJAX busca por nome/CPF
    setTimeout(function () {
        if (typeof $ === 'undefined' || !$.fn.select2) return;
        var $busca = $('#acomp_busca_cliente');
        $busca.select2({
            dropdownParent: $busca.parent(),
            language: 'pt-BR',
            ajax: {
                url: '{{ route("admin.clientes.search") }}',
                dataType: 'json',
                delay: 400,
                cache: false,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) { return { results: data.results || [] }; }
            },
            minimumInputLength: 2,
            placeholder: 'Digite o nome ou CPF para buscar...',
            allowClear: true,
        });
        $busca.on('select2:select', function (e) {
            preencherCamposAcomp(e.params.data);
        });
        $busca.on('select2:clear', function () {
            document.getElementById('acomp_cliente_id').value = '';
        });
    }, 300);

    // Busca por CPF
    document.getElementById('btn-buscar-cpf-acomp').addEventListener('click', function () {
        const cpf = document.getElementById('acomp_cpf').value.trim();
        const errEl = document.getElementById('acomp-cpf-error');
        errEl.style.display = 'none';
        if (!cpf) return;
        fetch('/admin/clientes/cpf/' + encodeURIComponent(cpf), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => preencherCamposAcomp(data))
        .catch(() => { errEl.style.display = 'block'; });
    });

    // Remover acompanhante
    document.getElementById('tabela-acompanhantes').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remover-acomp');
        if (!btn) return;
        if (!confirm('Remover este acompanhante?')) return;

        const url = btn.dataset.url;
        const aid = btn.dataset.id;

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('row-acomp-' + aid);
                if (row) row.remove();
            }
        })
        .catch(() => alert('Erro ao remover acompanhante.'));
    });

    // Adicionar acompanhante
    document.getElementById('btn-salvar-acomp').addEventListener('click', function () {
        const nome = document.getElementById('acomp_nome').value.trim();
        const cpf  = document.getElementById('acomp_cpf').value.trim();
        const tipo = document.getElementById('acomp_tipo').value;
        const nascimento = document.getElementById('acomp_nascimento').value;
        const email = document.getElementById('acomp_email').value.trim();
        const telefone = document.getElementById('acomp_telefone').value.trim();
        const errorEl = document.getElementById('acomp-error');

        errorEl.style.display = 'none';
        if (!nome) {
            errorEl.textContent = 'Nome é obrigatório.';
            errorEl.style.display = 'block';
            return;
        }

        // Converter dd/mm/yyyy para yyyy-mm-dd
        let nascimentoFormatted = '';
        if (nascimento) {
            const parts = nascimento.split('/');
            if (parts.length === 3) {
                nascimentoFormatted = parts[2] + '-' + parts[1] + '-' + parts[0];
            } else {
                nascimentoFormatted = nascimento;
            }
        }

        const clienteId = document.getElementById('acomp_cliente_id').value;
        const body = new URLSearchParams({
            nome, cpf, tipo, email, telefone,
            _token: csrfToken,
        });
        if (nascimentoFormatted) body.append('data_nascimento', nascimentoFormatted);
        if (clienteId) body.append('cliente_id', clienteId);

        fetch(addUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body,
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                errorEl.textContent = 'Erro ao salvar.';
                errorEl.style.display = 'block';
                return;
            }
            const a = data.acompanhante;
            const nascFormatted = a.data_nascimento
                ? a.data_nascimento.split('-').reverse().join('/')
                : '';
            const nomeCell = data.edit_url
                ? `<a href="${data.edit_url}" target="_blank">${a.nome} <i class="fas fa-external-link-alt fa-xs"></i></a>`
                : a.nome;

            const tbody = document.querySelector('#tabela-acompanhantes tbody');
            const removeUrl = addUrl + '/' + a.id;
            const tr = document.createElement('tr');
            tr.id = 'row-acomp-' + a.id;
            tr.innerHTML = `
                <td>${nomeCell}</td>
                <td>${a.cpf ?? ''}</td>
                <td>${a.tipo}</td>
                <td>${nascFormatted}</td>
                <td>${a.email ?? ''}</td>
                <td>${a.telefone ?? ''}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm btn-remover-acomp"
                        data-id="${a.id}"
                        data-url="${removeUrl}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);

            // Limpar campos
            document.getElementById('acomp_cliente_id').value = '';
            document.getElementById('acomp_nome').value = '';
            document.getElementById('acomp_cpf').value = '';
            document.getElementById('acomp_tipo').value = 'Adulto';
            document.getElementById('acomp_nascimento').value = '';
            document.getElementById('acomp_email').value = '';
            document.getElementById('acomp_telefone').value = '';
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#acomp_busca_cliente').val(null).trigger('change');
            }
        })
        .catch(() => {
            errorEl.textContent = 'Erro de comunicação.';
            errorEl.style.display = 'block';
        });
    });
});
</script>
