<?php

namespace App\Http\Controllers;

use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Models\Usuario;
use App\Services\UsuariosService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsuariosController extends Controller
{
    public function __construct(protected UsuariosService $usuariosService) {}

    public function index()
    {
        $usuarios = $this->usuariosService->buscaTodosUsuarios();
        return response()->json($usuarios, Response::HTTP_OK);
    }

    public function store(StoreUsuarioRequest $request){
        $validated = $request->validated();
        $usuario = $this->usuariosService->criarUsuario($validated);
        return response()->json($usuario, Response::HTTP_CREATED);
    }

    public function show(Usuario $usuario)
    {
        // $usuario = $this->usuariosService->buscaUsuarioPorId($usuario->id);
        // return response()->json($usuario, Response::HTTP_OK);
        return $usuario;
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        $validated = $request->validated();
        $usuario = $this->usuariosService->atualizarUsuario($usuario, $validated);
        return response()->json($usuario, Response::HTTP_OK);
    }

    public function destroy(Usuario $usuario)
    {
        $this->usuariosService->deletarUsuario($usuario);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
