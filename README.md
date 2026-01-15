# Venturize Hotelaria

Sistema de gerenciamento hoteleiro desenvolvido em Laravel 11.

## 📋 Pré-requisitos

- **PHP 8.2 ou superior**
- **Composer** (gerenciador de dependências PHP)
- **Node.js 18+** e **npm** (para assets do frontend)
- **Git** (para clonar o repositório)

## 🚀 Instalação e Configuração

### 1. Clone o repositório
```bash
git clone <url-do-repositorio>
cd venturize-hotelaria
```

### 2. Instale as dependências PHP
```bash
composer install
```

### 3. Configure o arquivo de ambiente
```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Gere a chave da aplicação
php artisan key:generate
```

### 4. Configure o banco de dados


#### Para MySQL/PostgreSQL:
Edite o arquivo `.env` e configure:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=venturize_hotelaria
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 5. Execute as migrações (CASO O BANCO ESTEJA ZERADO)
```bash
php artisan migrate
```

### 6. Execute os seeders (CASO PRECISE POPULAR)
```bash
php artisan db:seed
```

### 7. Instale as dependências do Node.js
```bash
npm install
```

### 8. Compile os assets do frontend
```bash
# Para produção
npm run build

# Para desenvolvimento (com hot-reload)
npm run dev
```

**Importante:** Sempre execute `npm run build` após clonar o repositório ou quando os assets do frontend não estiverem disponíveis. Isso gera o arquivo `manifest.json` necessário para o Laravel carregar os assets corretamente.

### 9. Configure as permissões (Linux/Mac)
```bash
# Dê permissões de escrita para storage e bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 10. Inicie o servidor de desenvolvimento
```bash
php artisan serve
```

O projeto estará disponível em: `http://localhost:8000`

## 🔧 Configurações Adicionais

### Configuração do Pusher (para notificações em tempo real)
Edite o arquivo `.env`:
```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=seu_app_id
PUSHER_APP_KEY=sua_app_key
PUSHER_APP_SECRET=seu_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=sua_cluster
```

### Configuração de Email
Edite o arquivo `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=seu_smtp_host
MAIL_PORT=587
MAIL_USERNAME=seu_email
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu_email
MAIL_FROM_NAME="${APP_NAME}"
```

## 📁 Estrutura do Projeto

```
venturize-hotelaria/
├── app/
│   ├── Http/Controllers/     # Controladores
│   ├── Models/              # Modelos Eloquent
│   ├── Services/            # Serviços de negócio
│   └── Events/              # Eventos
├── database/
│   ├── migrations/          # Migrações do banco
│   └── seeders/             # Dados iniciais
├── resources/
│   ├── views/               # Views Blade
│   ├── css/                 # Estilos
│   └── js/                  # JavaScript
├── routes/                  # Definição de rotas
└── public/                  # Arquivos públicos
```

## 🎯 Funcionalidades Principais

- **Gestão de Reservas**: Sistema completo de reservas de quartos
- **Gestão de Quartos**: Cadastro e controle de quartos
- **Sistema de Bar**: Controle de mesas e pedidos
- **Gestão de Estoque**: Controle de produtos e movimentações
- **Relatórios**: Geração de relatórios em PDF
- **API REST**: Endpoints para integração com aplicações móveis

## 🛠️ Comandos Úteis

```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recriar banco de dados
php artisan migrate:fresh --seed

# Gerar documentação da API
php artisan route:list

# Executar testes
php artisan test

# Criar novo controller
php artisan make:controller NomeController

# Criar nova migration
php artisan make:migration nome_da_migration

# Compilar assets do frontend
npm run build        # Para produção
npm run dev          # Para desenvolvimento (com hot-reload)
```

## 🔍 Troubleshooting

### Erro de permissão no storage
```bash
chmod -R 775 storage bootstrap/cache
```

### Erro de dependências
```bash
composer install --no-dev
```

### Erro "Vite manifest not found"
Se você encontrar o erro `Vite manifest not found at: public/build/manifest.json`, execute:
```bash
npm install
npm run build
```

Isso compilará os assets do frontend e gerará o arquivo `manifest.json` necessário.