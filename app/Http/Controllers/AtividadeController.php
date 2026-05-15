<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AtividadeController extends Controller
{
    public function index()
    {
        $atividades = \App\Models\Atividade::orderBy('created_at', 'desc')->get();
        return response()->json($atividades);
    }
}
