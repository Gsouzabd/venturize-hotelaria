<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Bar\MesaController;
use App\Http\Controllers\Admin\Bar\PedidoController;
use App\Http\Controllers\Admin\Bar\BarHomeController;
// SEMPRE RODAR O COMANDO:
//php artisan cache:clear
//php artisan route:cache
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['auth:admin'])->group(function () {

        // Adiciona o prefixo /bar a todas as rotas
        Route::prefix('bar')->group(function () {

            Route::get('/', [BarHomeController::class, 'index'])->name('bar.home');
            Route::get('/pedidos/{idPedido}/cupom-parcial', [PedidoController::class, 'showCupomParcial'])->name('bar.pedidos.cupom-parcial');
            Route::get('/pedidos/{idPedido}/extrato-parcial', [PedidoController::class, 'showExtratoParcial'])->name('bar.pedidos.extrato-parcial');

            $prefixes = [
                'mesas' => MesaController::class,
                'pedidos' => PedidoController::class,
            ];

            foreach ($prefixes as $prefix => $controller) {
                Route::prefix($prefix)->name('bar.' . $prefix . '.')->controller($controller)->group(function ($prefix) {
                    Route::get('/', 'index')->name('index');
                    Route::get('/create', 'edit')->name('create');
                    Route::match(['post', 'put'], '/', 'save')->name('save');
                    if ($prefix != 'estoque') {
                        Route::get('/{id}', 'edit')->name('edit');
                    }
                    Route::delete('/{id}', 'destroy')->name('destroy');
                });
            }
        });

    });
});