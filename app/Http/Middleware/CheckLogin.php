<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('usuario_id')) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        return $next($request);
    }
}
