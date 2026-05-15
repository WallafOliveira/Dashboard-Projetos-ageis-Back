<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificacaoController extends Controller
{
    public function index(Request $request)
    {
        // Pega as notificações não lidas
        // Assumindo que o usuário mockado tem id 1 se não houver auth
        $user = $request->user();
        if (!$user) {
            $notificacoes = \App\Models\Notificacao::where('lida', false)->orderBy('created_at', 'desc')->get();
        } else {
            $notificacoes = $user->notificacoes()->where('lida', false)->orderBy('created_at', 'desc')->get();
        }
        return response()->json($notificacoes, Response::HTTP_OK);
    }

    public function marcarComoLida(\App\Models\Notificacao $notificacao)
    {
        $notificacao->lida = true;
        $notificacao->save();

        return response()->json(['message' => 'Notificação marcada como lida']);
    }
}
