<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\PrintController;
// SEMPRE RODAR O COMANDO:
//php artisan cache:clear
//php artisan route:cache
Route::get('/', function () {
    return response()->json(['message' => 'API is running']);
});

Route::post('/login', [LoginController::class, 'login']);

// Rotas da API de Impressão
Route::prefix('print')->name('api.print.')->group(function () {
    // Verificar status de impressão de um pedido
    Route::get('/pedido/{pedidoId}/status', [PrintController::class, 'verificarStatusImpressao'])->name('verificar-status');
    
    // Obter dados do pedido para impressão
    Route::get('/pedido/{pedidoId}', [PrintController::class, 'getPedidoForPrint'])->name('pedido');
    
    // Listar pedidos pendentes de impressão
    Route::get('/pedidos-pendentes', [PrintController::class, 'getPedidosPendentes'])->name('pendentes');
    
    // Marcar pedido como impresso
    Route::post('/pedido/{pedidoId}/impresso', [PrintController::class, 'marcarComoImpresso'])->name('marcar-impresso');
    
    // Registrar tentativa de impressão
    Route::post('/pedido/{pedidoId}/tentativa', [PrintController::class, 'registrarTentativaImpressao'])->name('registrar-tentativa');
    
    // Marcar erro na impressão
    Route::post('/pedido/{pedidoId}/erro', [PrintController::class, 'marcarErroImpressao'])->name('marcar-erro');
    
    // Histórico de impressões de um pedido
    Route::get('/pedido/{pedidoId}/historico', [PrintController::class, 'getHistoricoImpressoes'])->name('historico-impressoes');
    
    // Estatísticas de impressão
    Route::get('/estatisticas', [PrintController::class, 'getEstatisticasImpressao'])->name('estatisticas');
});

// Rota específica para o agente de impressão (compatibilidade)
Route::get('/cupom-parcial/{pedidoId}', [PrintController::class, 'getPedidoForPrint'])->name('api.cupom-parcial');
