<?php

namespace App\Http\Controllers;

use App\Models\PerfilAcesso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerfilAcessoController extends Controller
{
    public function index(): JsonResponse
    {
        $perfis = PerfilAcesso::all();
        return response()->json($perfis, Response::HTTP_OK);
    }

    public function show(PerfilAcesso $perfilAcesso): JsonResponse
    {
        return response()->json($perfilAcesso, Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $perfil = PerfilAcesso::create($validated);
        return response()->json($perfil, Response::HTTP_CREATED);
    }

    public function update(Request $request, PerfilAcesso $perfilAcesso): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $perfilAcesso->update($validated);
        return response()->json($perfilAcesso, Response::HTTP_OK);
    }

    public function destroy(PerfilAcesso $perfilAcesso): JsonResponse
    {
        $perfilAcesso->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
