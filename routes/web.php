<?php

use App\Models\Empresa;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\QuartoController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\EmpresaController;
use App\Http\Controllers\Admin\EstoqueController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\Admin\ReservaController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\LocalEstoqueController;
use App\Http\Controllers\Admin\DisponibilidadeController;
use App\Http\Controllers\Admin\ImportarUsuarioController;
use App\Http\Controllers\Admin\QuartoOpcaoExtraController;
use App\Http\Controllers\Admin\QuartoPlanoPrecoController;
use App\Http\Controllers\Admin\MovimentacaoEstoqueController;
use App\Http\Controllers\Admin\ImpressoraController;
use App\Http\Controllers\Admin\FornecedorController;
// SEMPRE RODAR O COMANDO:
//php artisan cache:clear; php artisan route:cache;
include 'bar/routes.php';
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::middleware(['auth:admin'])->group(function () {

        Route::get('logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('/produtos/search', [ProdutoController::class, 'search'])->name('produtos.search');
        Route::get('/clientes/search', [ClienteController::class, 'search'])->name('clientes.search');
        Route::get('/fornecedores/search', [FornecedorController::class, 'search'])->name('fornecedores.search');

        Route::match(['post', 'put'], '/estoque/movimentacoes/', [MovimentacaoEstoqueController::class, 'save'])->name('movimentacoes-estoque.save');

        Route::get('/estoque/movimentacoes/transf', [MovimentacaoEstoqueController::class, 'edit'])->name('movimentacoes-estoque.transf');
        Route::get('/estoque/movimentacoes', [MovimentacaoEstoqueController::class, 'index'])->name('movimentacoes-estoque.index');
        Route::get('/estoque/movimentacoes/{id}', [MovimentacaoEstoqueController::class, 'edit'])->name('movimentacoes-estoque.edit');
        Route::get('/estoque/movimentacoes/create', [MovimentacaoEstoqueController::class, 'edit'])->name('movimentacoes-estoque.create');
        Route::get('/reservas/mapa', [ReservaController::class, 'mapa'])->name('reservas.mapa');

        Route::get('/reservas/{id}/gerar-extrato', [ReservaController::class, 'gerarExtrato'])->name('reservas.gerar-extrato');

        // Rotas específicas para despesas (devem vir ANTES do loop para não serem capturadas por /{id})
        Route::get('/despesas/relatorios', [\App\Http\Controllers\Admin\DespesaController::class, 'relatorios'])->name('despesas.relatorios');

        $prefixes = [
            'usuarios' => UsuarioController::class,
            'clientes' => ClienteController::class,
            'categorias' => CategoriaController::class,
            'quartos' => QuartoController::class,
            'reservas' => ReservaController::class,
            'quartos-opcoes-extras' => QuartoOpcaoExtraController::class,
            'produtos' => ProdutoController::class,
            'estoque' => EstoqueController::class,
            'locais-estoque' => LocalEstoqueController::class,
            'categorias-despesas' => \App\Http\Controllers\Admin\CategoriaDespesaController::class,
            'fornecedores' => \App\Http\Controllers\Admin\FornecedorController::class,
            'impressoras' => ImpressoraController::class,
        ];

        

        foreach ($prefixes as $prefix => $controller) {
            Route::prefix($prefix)->name($prefix . '.')->controller($controller)->group(function ($prefix) {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'edit')->name('create');
                Route::match(['post', 'put'], '/', 'save')->name('save');
                if ( $prefix != 'estoque' ) {
                    Route::get('/{id}', 'edit')->name('edit');
                }
                Route::delete('/{id}', 'destroy')->name('destroy');
            });
        }

        Route::get('/importar-usuarios', [ImportarUsuarioController::class, 'index'])->name('importar-usuarios.index');
        Route::post('/importar-usuarios', [ImportarUsuarioController::class, 'store'])->name('importar-usuarios.store');
        Route::get('/usuarios/{id}/resend-password', [UsuarioController::class, 'resendPassword'])->name('usuarios.resend-password');

        // Route::get('/reservas/{id}/status/{status}', [ReservaController::class, 'updateSituacaoReserva'])->name('reservas.updateSituacaoReserva');

        Route::get('/clientes/{id}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
        Route::get('/clientes/{id}', [ClienteController::class, 'findById'])->name('admin.clientes.findById');
        Route::get('/clientes/cpf/{cpf}', [ClienteController::class, 'findByCpf'])->name('admin.clientes.findByCpf');

        Route::get('quartos/{quartoId?}/planos-preco/edit/{id?}', [QuartoPlanoPrecoController::class, 'edit'])->name('quartos.planos-preco.edit');
        Route::post('quartos/planos-preco/save/{id?}', [QuartoPlanoPrecoController::class, 'save'])->name('quartos.planos-preco.save');      Route::delete('quartos/planos-preco/delete/{id}', [QuartoPlanoPrecoController::class, 'delete'])->name('quartos.planos-preco.delete');
        
        Route::get('/empresa/cnpj/cnpj', function($cpf) {
            $empresa = Empresa::where('cpf', $cpf)->firstOrFail();
            return response()->json($empresa);
        });        
        Route::get('/buscar-empresa/{cnpj}', [EmpresaController::class, 'buscarPorCnpj'])->name('buscar.empresa');
        
        Route::post('/verificar-disponibilidade', [DisponibilidadeController::class, 'verificar'])->name('verificar.disponibilidade');
        Route::get('/quartos/{quartoId}/planos-preco', [DisponibilidadeController::class, 'obterPlanosPrecos'])->name('obter-planos-preco');    


        Route::get('/reservas/{id}/gerar-ficha-nacional', [ReservaController::class, 'gerarFichaNacional'])->name('reserva.gerarFichaNacional');
        Route::get('/reservas/{id}/remover-taxa-servico', [ReservaController::class, 'removerTaxaServico'])->name('reserva.removerTaxaServico');

        Route::get('/estoque/{local_estoque_id}/edit/{id}', [EstoqueController::class, 'edit'])->name('admin.estoque.edit');

        // Rota para testar conectividade da impressora
        Route::post('/impressoras/{id}/testar', [ImpressoraController::class, 'testar'])->name('impressoras.testar');

        // Rotas de despesas (definidas manualmente para evitar conflito com rotas específicas)
        Route::prefix('despesas')->name('despesas.')->controller(\App\Http\Controllers\Admin\DespesaController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'edit')->name('create');
            Route::match(['post', 'put'], '/', 'save')->name('save');
            Route::get('/relatorios/exportar-consolidado', 'exportarConsolidado')->name('relatorios.exportar-consolidado');
            Route::get('/relatorios/exportar-detalhado', 'exportarDetalhado')->name('relatorios.exportar-detalhado');
            Route::get('/{id}/show', 'show')->name('show');
            Route::get('/{id}', 'edit')->name('edit');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

    });
});

// Rotas de teste para impressoras (fora do middleware de autenticação)
Route::get('/test-printers', function () {
    $printerService = new \App\Services\PrinterService();
    $status = $printerService->checkPrintersStatus();
    
    return response()->json([
        'message' => 'Status das impressoras configuradas',
        'printers' => $status
    ]);
})->name('test.printers');

Route::get('/test-print', function () {
    $printerService = app(\App\Services\PrinterService::class);
    
    // Usar um ID de pedido válido que existe na base de dados
    $testPedidoId = 174;
    
    try {
        $results = $printerService->printHtmlToAllPrinters($testPedidoId);
        
        return response()->json([
            'message' => 'Teste de impressão executado',
            'pedido_id' => $testPedidoId,
            'results' => $results
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erro no teste de impressão',
            'error' => $e->getMessage(),
            'pedido_id' => $testPedidoId
        ], 500);
    }
});

// Rota de teste para verificar status das impressoras
Route::get('/test-printer-status', function () {
    $printerService = app(\App\Services\PrinterService::class);
    
    try {
        $status = $printerService->checkPrintersStatus();
        return response()->json($status);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});

// Rota de teste para debug de impressão
Route::get('/test-mike42-print', function () {
    try {
        $printerService = new \App\Services\PrinterService();
        
        // Configurar uma impressora de teste
        $printer = [
            'name' => 'Impressora Bar',
            'ip' => '192.168.1.81'
        ];
        
        $testContent = "=== TESTE DE IMPRESSÃO ===\n";
        $testContent .= "Data: " . now()->format('d/m/Y H:i:s') . "\n";
        $testContent .= "Teste da biblioteca mike42/escpos-php\n";
        $testContent .= "================================\n";
        
        // Usar reflexão para acessar o método privado
        $reflection = new \ReflectionClass($printerService);
        $method = $reflection->getMethod('printTextToThermalPrinter');
        $method->setAccessible(true);
        
        $result = $method->invoke($printerService, $printer, $testContent);
        
        return response()->json([
            'status' => 'success',
            'printer' => $printer,
            'result' => $result,
            'message' => 'Teste de impressão executado com mike42/escpos-php'
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('test.print');

// Rota para testar impressão simulada (sem impressora real)
Route::get('/test-print-simulation', function () {
    try {
        $printerService = new \App\Services\PrinterService();
        
        // Simular impressão sem impressora real
        $pedidoId = 174;
        $pedido = \App\Models\Bar\Pedido::with(['mesa', 'reserva.quarto', 'cliente', 'itens.produto'])->findOrFail($pedidoId);
        
        // Gerar conteúdo do cupom
        $reflection = new \ReflectionClass($printerService);
        $method = $reflection->getMethod('generateCupomContent');
        $method->setAccessible(true);
        $cupomContent = $method->invoke($printerService, $pedido);
        
        // Simular comandos ESC/POS que seriam enviados
        $escPos = "\x1B\x40"; // ESC @ - Inicializar
        $escPos .= $cupomContent;
        $escPos .= "\x0A\x0A\x0A"; // 3 quebras de linha
        $escPos .= "\x1D\x56\x41\x03"; // Corte parcial
        
        return response()->json([
            'status' => 'simulation_success',
            'cupom_content' => $cupomContent,
            'cupom_length' => strlen($cupomContent),
            'escpos_length' => strlen($escPos),
            'escpos_hex' => bin2hex($escPos),
            'message' => 'Simulação de impressão concluída - dados que seriam enviados para impressora'
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('test.print.simulation');

// Teste com conteúdo longo para verificar se a impressão completa funciona
Route::get('/test-long-print', function () {
    $printerService = new \App\Services\PrinterService();
    
    // Gerar conteúdo longo de teste
    $longContent = "TESTE DE IMPRESSÃO LONGA\n";
    $longContent .= str_repeat("=", 40) . "\n";
    
    for ($i = 1; $i <= 30; $i++) {
        $longContent .= sprintf("Linha %02d: Item de teste muito longo para verificar se a impressão completa %s\n", $i, str_repeat("*", 10));
    }
    
    $longContent .= str_repeat("=", 40) . "\n";
    $longContent .= "FIM DO TESTE - TOTAL: 30 LINHAS\n";
    
    // Tentar imprimir o conteúdo longo usando o método interno
    $printer = ['name' => 'Impressora Bar', 'ip' => '192.168.1.81'];
    $reflection = new \ReflectionClass($printerService);
    $method = $reflection->getMethod('printTextToThermalPrinter');
    $method->setAccessible(true);
    $result = $method->invoke($printerService, $printer, $longContent);
    
    return response()->json([
        'test_result' => $result,
        'content_length' => strlen($longContent),
        'line_count' => substr_count($longContent, "\n"),
        'message' => 'Teste de impressão longa concluído'
    ]);
});

// Rota para debug - visualizar o texto formatado
Route::get('/debug-print-text', function () {
    $pedidoId = 174;
    $pedido = \App\Models\Bar\Pedido::with(['mesa', 'reserva.quarto', 'cliente', 'itens.produto'])->findOrFail($pedidoId);
    
    // Usar reflexão para acessar o método privado generateCupomContent
    $printerService = new \App\Services\PrinterService();
    $reflection = new \ReflectionClass($printerService);
    $method = $reflection->getMethod('generateCupomContent');
    $method->setAccessible(true);
    
    $textContent = $method->invoke($printerService, $pedido);
    
    // Mostrar informações detalhadas sobre o conteúdo
    $info = [
        'content_length' => strlen($textContent),
        'line_count' => substr_count($textContent, "\n"),
        'content' => $textContent,
        'content_hex' => bin2hex($textContent)
    ];
    
    return response()->json($info, 200, [
        'Content-Type' => 'application/json; charset=utf-8'
    ]);
})->name('debug.print.text');