<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\Usuario;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

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

            if ($usuario->tipo != 'administrador'&& $usuario->tipo != 'gerente') {
                throw new Exception('Usuário sem permissão para acessar o recurso.');
            }

            Auth::guard('admin')->login($usuario);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }


        return response()->json([
            'message' => config('app.messages.logged_in'),
            'redirect_to' => route('admin.home'),
        ]);
    }

    public function logout()
    {
        Auth::guard('admin')->logout(); // Ensure you're using the correct guard
        return redirect()->route('admin.login'); // Redirect to the admin login page
    }
}
