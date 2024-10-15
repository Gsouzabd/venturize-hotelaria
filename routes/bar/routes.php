<?php

use App\Http\Controllers\Admin\Bar\BarHomeController;
use App\Http\Controllers\Admin\Bar\MesaController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['auth:admin'])->group(function () {

        // Adiciona o prefixo /bar a todas as rotas
        Route::prefix('bar')->group(function () {

            Route::get('/', [BarHomeController::class, 'index'])->name('bar.home');

            $prefixes = [
                'mesas' => MesaController::class,
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