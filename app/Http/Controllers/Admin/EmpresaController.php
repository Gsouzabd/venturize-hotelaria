<?php
namespace App\Http\Controllers\Admin;

use App\Models\Empresa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmpresaController extends Controller
{
    public function buscarPorCnpj($cnpj)
    {

        //insere os pontos e traços no CNPJ
        $cnpj = substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);

        
        $empresa = Empresa::where('cnpj', $cnpj)->first();

        if ($empresa) {
            return response()->json($empresa);
        } else {
            return response()->json(['error' => 'Nenhuma empresa encontrada com o CNPJ informado.'], 404);
        }
    }
}