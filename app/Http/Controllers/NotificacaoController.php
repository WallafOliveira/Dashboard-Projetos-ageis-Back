<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificacaoController extends Controller
{
    public function index(Request $request)
    {
        $notificacoes = $request->user()->notificacoes()->orderBy('created_at', 'desc')->get();
        return response()->json($notificacoes, Response::HTTP_OK);
    }
}
