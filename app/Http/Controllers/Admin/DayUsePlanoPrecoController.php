<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\DayUsePlanoPreco;
use App\Http\Controllers\Controller;

class DayUsePlanoPrecoController extends Controller
{
    /**
     * Lista todos os planos de preço de Day Use.
     */
    public function index()
    {
        $planos = DayUsePlanoPreco::orderByDesc('is_default')
            ->orderBy('data_inicio')
            ->get();

        return view('admin.day-use-precos.index', compact('planos'));
    }

    /**
     * Formulário de criação/edição.
     */
    public function edit($id = null)
    {
        $edit = (bool) $id;
        $plano = $edit ? DayUsePlanoPreco::findOrFail($id) : new DayUsePlanoPreco();

        return view('admin.day-use-precos.form', compact('plano', 'edit'));
    }

    /**
     * Salva (cria/atualiza) um plano de Day Use.
     */
    public function save(Request $request)
    {
        // Checkbox envia "on" quando marcado; normalizar para boolean antes de validar
        $request->merge(['is_default' => $request->boolean('is_default')]);

        $validated = $request->validate([
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'is_default' => 'sometimes|boolean',
            'preco_segunda' => 'nullable|numeric|min:0',
            'preco_terca' => 'nullable|numeric|min:0',
            'preco_quarta' => 'nullable|numeric|min:0',
            'preco_quinta' => 'nullable|numeric|min:0',
            'preco_sexta' => 'nullable|numeric|min:0',
            'preco_sabado' => 'nullable|numeric|min:0',
            'preco_domingo' => 'nullable|numeric|min:0',
            'preco_cafe_semana' => 'nullable|numeric|min:0',
            'preco_cafe_fim_semana' => 'nullable|numeric|min:0',
        ]);

        $id = $request->get('id');
        $plano = $id ? DayUsePlanoPreco::findOrFail($id) : new DayUsePlanoPreco();

        $plano->fill($validated);
        $plano->save();

        return redirect()
            ->route('admin.day-use-precos.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    /**
     * Remove um plano de Day Use.
     */
    public function destroy($id)
    {
        $plano = DayUsePlanoPreco::findOrFail($id);
        $plano->delete();

        return redirect()
            ->route('admin.day-use-precos.index')
            ->with('notice', config('app.messages.delete'));
    }
}

