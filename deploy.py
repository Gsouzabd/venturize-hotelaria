import paramiko, subprocess, os, sys

HOST   = '147.79.94.231'
PORT   = 65002
USER   = 'u529148852'
BASE_R = '/home/u529148852/domains/venturize.com.br/public_html/laravel'
BASE_L = os.path.dirname(os.path.abspath(__file__))

EXCLUDE_PREFIXES = (
    'node_modules/', 'vendor/', 'storage/', 'tests/', '.git',
    'resources/js/', 'resources/css/', 'database/seeders/',
    'printingAgent/', 'bitz-exports/', 'bootstrap/cache/',
    '.env', '.claude', 'make-deploy', 'deploy.py',
)

def get_changed_files(ref='origin/main'):
    result = subprocess.run(
        ['git', 'diff', '--name-only', f'{ref}...HEAD'],
        capture_output=True, text=True, cwd=BASE_L
    )
    status = subprocess.run(
        ['git', 'status', '--porcelain'],
        capture_output=True, text=True, cwd=BASE_L
    )
    files = set(result.stdout.strip().splitlines())
    for line in status.stdout.strip().splitlines():
        if not line.strip():
            continue
        code = line[:2]
        f = line[3:].strip()
        # inclui modificados, adicionados e não-trackeados; exclui apenas ignorados (!!)
        if code.strip() and '!' not in code:
            files.add(f)
    return sorted(files)

def should_deploy(path):
    if any(path.startswith(p) for p in EXCLUDE_PREFIXES):
        return False
    full = os.path.join(BASE_L, path.replace('/', os.sep))
    return os.path.isfile(full)

ref = sys.argv[1] if len(sys.argv) > 1 else 'origin/main'
files = [f for f in get_changed_files(ref) if should_deploy(f)]

if not files:
    print('Nenhum arquivo para enviar.')
    sys.exit(0)

print(f'Arquivos a enviar ({len(files)}):')
for f in files:
    print(f'  {f}')

confirm = input('\nConfirmar deploy? [s/N] ')
if confirm.lower() != 's':
    print('Cancelado.')
    sys.exit(0)

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, look_for_keys=True, allow_agent=True, timeout=30)

sftp = client.open_sftp()
for f in files:
    local  = os.path.join(BASE_L, f.replace('/', os.sep))
    remote = f'{BASE_R}/{f}'
    try:
        sftp.put(local, remote)
        print(f'OK: {f}')
    except Exception as e:
        print(f'ERRO: {f} — {e}')
sftp.close()

POST_CMDS = [
    f'cd {BASE_R} && php artisan migrate --force',
    f'cd {BASE_R} && php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear',
    f'cd {BASE_R} && php artisan route:cache && php artisan config:cache',
]
for cmd in POST_CMDS:
    print(f'\n$ {cmd}')
    _, out, err = client.exec_command(cmd, timeout=120)
    stdout = out.read().decode('utf-8', errors='replace')
    stderr = err.read().decode('utf-8', errors='replace')
    if stdout: print(stdout)
    if stderr: print('STDERR:', stderr)

client.close()
print('\nDeploy concluído.')
