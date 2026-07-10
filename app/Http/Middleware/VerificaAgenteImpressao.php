<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificaAgenteImpressao
{
    /**
     * Libera a requisição se vier com o token compartilhado do PrintingAgent
     * (header X-Print-Agent-Token) ou de uma sessão admin autenticada
     * (fluxo atual do painel web, que já roda dentro do guard 'admin').
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.print_agent.token');

        if ($token && hash_equals($token, (string) $request->header('X-Print-Agent-Token', ''))) {
            return $next($request);
        }

        if (auth('admin')->check()) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Não autorizado',
            'error_code' => 'UNAUTHORIZED',
        ], 401);
    }
}
