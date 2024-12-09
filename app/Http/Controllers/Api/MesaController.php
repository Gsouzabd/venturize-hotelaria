<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bar\Mesa;

class MesaController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:sanctum');
    // }

    public function index()
    {
        // return 'teste';
        $mesas = Mesa::all();
        return response()->json($mesas);
    }

    public function store(Request $request)
    {
        $mesa = Mesa::create($request->all());
        return response()->json($mesa, 201);
    }

    public function show($id)
    {
        $mesa = Mesa::findOrFail($id);
        return response()->json($mesa);
    }

    public function update(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->update($request->all());
        return response()->json($mesa);
    }

    public function destroy($id)
    {
        Mesa::destroy($id);
        return response()->json(null, 204);
    }
}