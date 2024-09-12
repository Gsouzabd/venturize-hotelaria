<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportarUsuarioRequest;
use App\Mail\ContaCriada;
use App\Models\Plano;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;
use Throwable;

class ImportarUsuarioController extends Controller
{
    public function index()
    {
        return view('admin.importar-usuarios.form');
    }

    public function store(ImportarUsuarioRequest $request)
    {
        $cabecalhoEsperado = ['Nome', 'E-mail', 'Plano'];
        $cabecalhoTraduzido = ['nome', 'email', 'plano'];

        $csvReader = SimpleExcelReader::create($request->file('arquivo'), 'csv')
            ->useDelimiter(config('app.csv_delimiter'))
            ->useHeaders($cabecalhoTraduzido);

        $cabecalho = $csvReader->getOriginalHeaders();
        $registros = $csvReader->getRows()->toArray();

        if (empty($registros)) {
            return back()->withErrors('Arquivo não pode estar vazio.');
        }

        if (implode('', $cabecalho) !== implode('', $cabecalhoEsperado)) {
            return back()->withErrors('Cabeçalho do arquivo é inválido.');
        }

        unset($cabecalhoEsperado, $cabecalhoTraduzido, $cabecalho);

        $registrosProcessados = [];
        $planos = Plano::all();

        DB::beginTransaction();

        try {
            foreach ($registros as $registro) {
                $registro['nome'] = trim($registro['nome']);
                $registro['plano'] = trim($registro['plano']);
                $registro['fl_nova_conta'] = false;

                if (!$registro['nome']) {
                    $registrosProcessados[] = [...$registro, ...[
                        'situacao' => 'Descartado',
                        'mensagem' => 'Nome é requerido.',
                    ]];

                    continue;
                }

                if (filter_var($registro['email'], FILTER_VALIDATE_EMAIL) === false) {
                    $registrosProcessados[] = [...$registro, ...[
                        'situacao' => 'Descartado',
                        'mensagem' => 'E-mail inválido.',
                    ]];

                    continue;
                }

                if (!$registro['plano'] || !$plano = $planos->firstWhere('nome', $registro['plano'])) {
                    $registrosProcessados[] = [...$registro, ...[
                        'situacao' => 'Descartado',
                        'mensagem' => 'Plano não encontrado.',
                    ]];

                    continue;
                }

                if (!$usuario = Usuario::where('email', $registro['email'])->first()) {
                    $registro['fl_nova_conta'] = true;
                    $registro['senha'] = Str::random(8);

                    $usuario = Usuario::create([
                        'nome' => $registro['nome'],
                        'email' => $registro['email'],
                        'senha' => $registro['senha'],
                        'tipo' => 'cliente',
                        'fl_ativo' => 1,
                    ]);

                    $usuario->associacoes()->create(['ref_plano' => $plano->id, 'fl_ativo' => 1]);

                    $registrosProcessados[] = [...$registro, ...[
                        'situacao' => 'Processado',
                        'mensagem' => 'Usuário criado. Associação inserida.',
                    ]];
                } else {
                    if ($associacao = $usuario->associacoes()->where('ref_plano', $plano->id)->first()) {
                        $associacao->update(['fl_ativo' => 1]);
                        $usuario->associacoes()->where('id', '<>', $associacao->id)->update(['fl_ativo' => 0]);

                        $registrosProcessados[] = [...$registro, ...[
                            'situacao' => 'Processado',
                            'mensagem' => 'Associação com esse plano já existe. A mesma foi ativada. Demais associações desativadas.',
                        ]];

                        continue;
                    }

                    $usuario->associacoes()->update(['fl_ativo' => 0]);
                    $usuario->associacoes()->create(['ref_plano' => $plano->id, 'fl_ativo' => 1]);

                    $registrosProcessados[] = [...$registro, ...[
                        'situacao' => 'Processado',
                        'mensagem' => 'Associação inserida. Demais associações desativadas.',
                    ]];
                }
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        foreach ($registrosProcessados as $registro) {
            if ($registro['fl_nova_conta']) {
                Mail::to($registro['email'])
                    ->send(new ContaCriada($registro['nome'], $registro['email'], $registro['senha']));
            }
        }

        return view('admin.importar-usuarios.show')
            ->with('registros', $registrosProcessados)
            ->with('notice', 'Importação finalizada com sucesso.');
    }
}
