<?php

namespace App\Http\Controllers;

use App\Models\Relatorio;
use App\Services\RelatoriosService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RelatoriosController extends Controller
{
    public function __construct(protected RelatoriosService $relatoriosService) {}

    public function index(): JsonResponse
    {
        $relatorios = $this->relatoriosService->buscaTodosRelatorios();
        return response()->json($relatorios, Response::HTTP_OK);
    }

    public function show(Relatorio $relatorio): JsonResponse
    {
        return response()->json($relatorio, Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_relatorio' => 'required|date',
        ]);

        $relatorio = $this->relatoriosService->criarRelatorio($validated);
        return response()->json($relatorio, Response::HTTP_CREATED);
    }

    public function update(Request $request, Relatorio $relatorio): JsonResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_relatorio' => 'required|date',
        ]);

        $relatorio = $this->relatoriosService->atualizarRelatorio($relatorio, $validated);
        return response()->json($relatorio, Response::HTTP_OK);
    }

    public function destroy(Relatorio $relatorio): JsonResponse
    {
        $this->relatoriosService->deletarRelatorio($relatorio);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
