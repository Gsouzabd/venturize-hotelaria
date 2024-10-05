<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\QuartoOpcaoExtra;
use App\Http\Controllers\Controller;

class QuartoOpcaoExtraController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $opcoesExtras = QuartoOpcaoExtra::all();
        return view('admin.quartos-opcoes-extras.list', compact('opcoesExtras'));
    }

    /**
     * Show the form for creating or editing a resource.
     *
     * @param  int|null  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id = null)
    {
        $edit = boolval($id);
        $opcaoExtra = $edit ? QuartoOpcaoExtra::findOrFail($id) : new QuartoOpcaoExtra();

        return view('admin.quartos-opcoes-extras.form', compact('opcaoExtra', 'edit'));
    }

    /**
     * Store or update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'preco' => 'required|numeric|min:0',
        ]);

        if ($request->has('id')) {
            $opcaoExtra = QuartoOpcaoExtra::findOrFail($request->id);
            $opcaoExtra->update($request->all());
        } else {
            QuartoOpcaoExtra::create($request->all());
        }

        return redirect()->route('admin.quartos-opcoes-extras.index')->with('success', 'Opção extra salva com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $opcaoExtra = QuartoOpcaoExtra::findOrFail($id);
        $opcaoExtra->delete();

        return redirect()->route('admin.quartos-opcoes-extras.index')->with('success', 'Opção extra excluída com sucesso!');
    }
}