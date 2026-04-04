<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\Usuario;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'total_usuarios' => Usuario::count(),
            'total_produtos' => Produto::count(),
            'total_vendas' => Venda::count(),
            'faturamento' => Venda::sum('total'),

            'ultimas_vendas' => Venda::latest()->take(5)->get()
        ]);
    }
}
=======
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
}
>>>>>>> a850cd7c88af1deddd85d5e4c3e34cc6a5112ba3
