<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use UsuariosService;

class UsuariosController extends Controller
{
    public function __construct(protected UsuariosService $usuariosService) {}

    public function index()
    {
        $usuarios = $this->usuariosService->buscaTodosTopicos();
        return response()->json($usuarios, Response::HTTP_OK);
    }
}
