<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\Usuario;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        try {
            if (!$usuario = Usuario::where('email', $request->get('email'))->first()) {
                throw new Exception('Usuário não encontrado.');
            }

            if (!Hash::check($request->get('password'), $usuario->senha)) {
                throw new Exception('Usuário e/ou senha inválido.');
            }

            if (!$usuario->fl_ativo) {
                throw new Exception('Usuário inativo.');
            }

            if ($usuario->tipo != 'administrador' && $usuario->tipo != 'gerente') {
                throw new Exception('Usuário sem permissão para acessar o recurso.');
            }

            $token = $usuario->createToken('API Token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $usuario,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successful']);
    }
}