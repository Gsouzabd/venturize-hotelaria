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
// SEMPRE RODAR O COMANDO:
//php artisan cache:clear
//php artisan route:cache
include 'bar/routes.php';
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::middleware(['auth:admin'])->group(function () {

        Route::get('logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('/produtos/search', [ProdutoController::class, 'search'])->name('admin.produtos.search');
        Route::get('/clientes/search', [ClienteController::class, 'search'])->name('admin.clientes.search');

        Route::match(['post', 'put'], '/estoque/movimentacoes/', [MovimentacaoEstoqueController::class, 'save'])->name('movimentacoes-estoque.save');

        Route::get('/estoque/movimentacoes/transf', [MovimentacaoEstoqueController::class, 'edit'])->name('movimentacoes-estoque.transf');
        Route::get('/estoque/movimentacoes', [MovimentacaoEstoqueController::class, 'index'])->name('movimentacoes-estoque.index');
        Route::get('/estoque/movimentacoes/{id}', [MovimentacaoEstoqueController::class, 'edit'])->name('movimentacoes-estoque.edit');
        Route::get('/estoque/movimentacoes/create', [MovimentacaoEstoqueController::class, 'edit'])->name('movimentacoes-estoque.create');
        Route::get('/reservas/mapa', [ReservaController::class, 'mapa'])->name('reservas.mapa');

        Route::get('/reservas/{id}/gerar-extrato', [ReservaController::class, 'gerarExtrato'])->name('reservas.gerar-extrato');

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
    

        Route::get('/estoque/{local_estoque_id}/edit/{id}', [EstoqueController::class, 'edit'])->name('admin.estoque.edit');


    });
});