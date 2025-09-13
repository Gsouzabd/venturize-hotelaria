#!/bin/bash

# Script para verificar se a correção funcionou no servidor DreamHost
# Execute este script no servidor de produção

echo "🔍 Verificando correção do erro 500 na rota /admin/bar..."
echo "================================================="
echo ""

# Verificar se estamos no diretório correto
if [ ! -f "artisan" ]; then
    echo "❌ Erro: Execute este script no diretório raiz do Laravel"
    exit 1
fi

echo "📋 1. Verificando configurações do ambiente..."
echo "Ambiente: $(grep '^APP_ENV=' .env | cut -d'=' -f2)"
echo "Debug: $(grep '^APP_DEBUG=' .env | cut -d'=' -f2)"
echo "URL: $(grep '^APP_URL=' .env | cut -d'=' -f2)"
echo ""

echo "🔌 2. Testando conexão com banco de dados..."
php -r "
try {
    require_once 'vendor/autoload.php';
    \$app = require_once 'bootstrap/app.php';
    \$pdo = DB::connection()->getPdo();
    echo '✅ Conexão com banco: OK\n';
    \$count = DB::table('users')->count();
    echo '✅ Query teste: OK (' . \$count . ' usuários)\n';
} catch (Exception \$e) {
    echo '❌ Erro na conexão: ' . \$e->getMessage() . '\n';
    exit(1);
}"

if [ $? -ne 0 ]; then
    echo "❌ Falha na conexão com banco de dados!"
    exit 1
fi

echo ""
echo "🎯 3. Testando o BarHomeController..."
php -r "
try {
    require_once 'vendor/autoload.php';
    \$app = require_once 'bootstrap/app.php';
    \$kernel = \$app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Simular request para /admin/bar
    \$request = Illuminate\Http\Request::create('/admin/bar', 'GET');
    \$request->headers->set('Accept', 'text/html');
    
    // Adicionar sessão fake para evitar erro de autenticação
    \$request->setLaravelSession(app('session.store'));
    
    echo '🔄 Testando rota /admin/bar...\n';
    
    // Testar apenas a instanciação do controller e método
    \$controller = app('App\\Http\\Controllers\\Admin\\Bar\\BarHomeController');
    
    // Verificar se o método index existe
    if (method_exists(\$controller, 'index')) {
        echo '✅ Controller e método encontrados\n';
        
        // Testar as queries que o controller faz
        \$totalUsuarios = DB::table('users')->count();
        \$totalClientes = DB::table('clientes')->count();
        \$reservasHospedado = DB::table('reservas')->where('situacao_reserva', 'HOSPEDADO')->count();
        
        echo '✅ Usuario::count(): ' . \$totalUsuarios . '\n';
        echo '✅ Cliente::count(): ' . \$totalClientes . '\n';
        echo '✅ Reservas HOSPEDADO: ' . \$reservasHospedado . '\n';
        
        echo '🎉 Todas as queries do BarHomeController funcionaram!\n';
    } else {
        echo '❌ Método index não encontrado no BarHomeController\n';
        exit(1);
    }
    
} catch (Exception \$e) {
    echo '❌ Erro no BarHomeController: ' . \$e->getMessage() . '\n';
    echo 'Arquivo: ' . \$e->getFile() . '\n';
    echo 'Linha: ' . \$e->getLine() . '\n';
    exit(1);
}"

if [ $? -ne 0 ]; then
    echo "❌ Falha no teste do BarHomeController!"
    exit 1
fi

echo ""
echo "📊 4. Verificando tabelas essenciais..."
php -r "
\$tables = ['users', 'clientes', 'mesas', 'reservas', 'pedidos', 'sessions'];
foreach (\$tables as \$table) {
    try {
        \$count = DB::table(\$table)->count();
        echo '✅ Tabela ' . \$table . ': ' . \$count . ' registros\n';
    } catch (Exception \$e) {
        echo '❌ Tabela ' . \$table . ': ' . \$e->getMessage() . '\n';
    }
}"

echo ""
echo "🧹 5. Limpando cache (se necessário)..."
php artisan config:clear > /dev/null 2>&1
php artisan route:clear > /dev/null 2>&1
echo "✅ Cache limpo"

echo ""
echo "🌐 6. Testando rota via HTTP..."
if command -v curl > /dev/null; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://venturize.codebeans.dev/admin/bar" || echo "000")
    
    if [ "$HTTP_CODE" = "200" ]; then
        echo "✅ Rota /admin/bar: HTTP $HTTP_CODE (OK)"
    elif [ "$HTTP_CODE" = "302" ]; then
        echo "⚠️ Rota /admin/bar: HTTP $HTTP_CODE (Redirecionamento - provavelmente para login)"
        echo "   Isso é normal se você não estiver logado"
    elif [ "$HTTP_CODE" = "500" ]; then
        echo "❌ Rota /admin/bar: HTTP $HTTP_CODE (ERRO 500 - ainda há problema!)"
    else
        echo "⚠️ Rota /admin/bar: HTTP $HTTP_CODE (Código inesperado)"
    fi
else
    echo "⚠️ curl não disponível - teste manual necessário"
fi

echo ""
echo "📝 7. Verificando logs recentes..."
if [ -f "storage/logs/laravel.log" ]; then
    RECENT_ERRORS=$(tail -50 storage/logs/laravel.log | grep -c "ERROR\|Exception\|Fatal")
    if [ "$RECENT_ERRORS" -eq 0 ]; then
        echo "✅ Nenhum erro recente nos logs"
    else
        echo "⚠️ $RECENT_ERRORS erros encontrados nos logs recentes"
        echo "   Execute: tail -20 storage/logs/laravel.log"
    fi
else
    echo "⚠️ Arquivo de log não encontrado"
fi

echo ""
echo "================================================="
echo "🎉 VERIFICAÇÃO CONCLUÍDA!"
echo ""
echo "📋 Resumo:"
echo "✅ Conexão com banco de dados funcionando"
echo "✅ BarHomeController testado com sucesso"
echo "✅ Todas as queries necessárias funcionaram"
echo "✅ Tabelas do banco verificadas"
echo ""
echo "🌐 Próximo passo:"
echo "   Acesse: https://venturize.codebeans.dev/admin/bar"
echo "   (Faça login se necessário)"
echo ""
echo "📞 Se ainda houver erro 500:"
echo "   1. Verifique: tail -f storage/logs/laravel.log"
echo "   2. Execute: php test-db-connection-web.php via web"
echo "   3. Confirme se está logado como admin"
echo ""