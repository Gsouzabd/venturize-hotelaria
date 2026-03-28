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
                    <input type="text" id="acomp_cpf" class="form-control" placeholder="000.000.000-00">
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

        const body = new URLSearchParams({
            nome, cpf, tipo, email, telefone,
            _token: csrfToken,
        });
        if (nascimentoFormatted) body.append('data_nascimento', nascimentoFormatted);

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
            document.getElementById('acomp_nome').value = '';
            document.getElementById('acomp_cpf').value = '';
            document.getElementById('acomp_tipo').value = 'Adulto';
            document.getElementById('acomp_nascimento').value = '';
            document.getElementById('acomp_email').value = '';
            document.getElementById('acomp_telefone').value = '';
        })
        .catch(() => {
            errorEl.textContent = 'Erro de comunicação.';
            errorEl.style.display = 'block';
        });
    });
});
</script>
