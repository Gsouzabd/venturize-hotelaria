import sys, socket, requests, paramiko, json
sys.stdout.reconfigure(encoding='utf-8')

BASE_URL = 'https://venturize.com.br'
SSH_HOST, SSH_PORT, SSH_USER = '147.79.94.231', 65002, 'u529148852'
DB_BASE  = '/home/u529148852/domains/venturize.com.br/public_html/laravel'

def check_api(label, method, path, **kwargs):
    url = f'{BASE_URL}{path}'
    try:
        r = getattr(requests, method)(url, timeout=10, **kwargs)
        status = 'OK' if r.ok else f'HTTP {r.status_code}'
        try:
            body = r.json()
        except Exception:
            body = {}
        return status, body, r
    except Exception as e:
        return f'ERRO: {e}', {}, None

def ssh_exec(client, cmd, timeout=20):
    _, out, err = client.exec_command(cmd, timeout=timeout)
    return out.read().decode('utf-8', errors='replace').strip()

# ── SSH para testes que precisam da rede interna ────────────────────────────
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(SSH_HOST, port=SSH_PORT, username=SSH_USER,
               look_for_keys=True, allow_agent=True, timeout=30)

# ── 1. Impressoras ──────────────────────────────────────────────────────────
status, data, _ = check_api('impressoras', 'get', '/api/print/impressoras')
print(f'\n=== Impressoras ({status}) ===')
printers = (data.get('printers')
            or data.get('data')
            or (data if isinstance(data, list) else []))
if printers:
    for p in printers:
        ip, port = p.get('ip', ''), p.get('port') or p.get('porta', 9100)
        # TCP testado do servidor de producao (rede interna)
        tcp_result = ssh_exec(client, f'nc -z -w3 {ip} {port} && echo ok || echo falhou')
        tcp = 'alcancavel' if 'ok' in tcp_result else f'falhou ({tcp_result})'
        print(f"  [{tcp:20}] {p.get('name') or p.get('nome')}  {ip}:{port}")
else:
    print('  Nenhuma impressora retornada. Raw:', json.dumps(data)[:200])

# ── 2. Estatisticas ─────────────────────────────────────────────────────────
status, data, _ = check_api('estatisticas', 'get', '/api/print/estatisticas')
print(f'\n=== Estatisticas ({status}) ===')
stats = data.get('data', data)
for k, v in stats.items():
    print(f'  {k}: {v}')

# ── 3. Pedidos pendentes ────────────────────────────────────────────────────
status, data, _ = check_api('pendentes', 'get', '/api/print/pedidos-pendentes')
print(f'\n=== Pedidos pendentes ({status}) ===')
pendentes = data.get('data', data if isinstance(data, list) else [])
print(f'  Total: {len(pendentes)}')
for p in pendentes[:5]:
    print(f"  Pedido #{p.get('id') or p.get('pedido_id')}  status={p.get('status_impressao') or p.get('status')}")

# ── 4. Fluxo de status ──────────────────────────────────────────────────────
pedido_id = ssh_exec(client,
    f"cd {DB_BASE} && php artisan tinker --execute=\"echo App\\\\Models\\\\Bar\\\\Pedido::latest()->value('id');\"")
client.close()

if pedido_id and pedido_id.isdigit():
    print(f'\n=== Fluxo de status (pedido #{pedido_id}) ===')

    s, d, r = check_api('get-pedido', 'get', f'/api/print/pedido/{pedido_id}')
    impressao_id = (d.get('impressao_id')
                    or d.get('data', {}).get('impressao_id')
                    or d.get('id'))
    print(f'  GET pedido:     {s} | impressao_id={impressao_id}')
    if s != 'OK':
        print('    Body:', json.dumps(d, ensure_ascii=False)[:300])

    s, d, r = check_api('tentativa', 'post', f'/api/print/pedido/{pedido_id}/tentativa',
                        json={'impressao_id': impressao_id})
    print(f'  POST tentativa: {s}')
    if s != 'OK' and r is not None:
        print('    Body:', r.text[:300])

    s, d, r = check_api('impresso', 'post', f'/api/print/pedido/{pedido_id}/impresso',
                        json={'impressao_id': impressao_id, 'agente': 'validacao'})
    print(f'  POST impresso:  {s}')

    s, d, _ = check_api('historico', 'get', f'/api/print/pedido/{pedido_id}/historico')
    print(f'  GET historico:  {s}')
    historico = d if isinstance(d, list) else d.get('data', [])
    for h in historico[:5]:
        print(f"    -> {h.get('status_impressao')} ({h.get('agente_impressao')})")
else:
    print('\n=== Fluxo de status ===')
    print('  Nenhum pedido encontrado.')

print('\nValidacao concluida.')
