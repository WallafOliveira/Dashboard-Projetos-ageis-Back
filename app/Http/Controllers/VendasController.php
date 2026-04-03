<?php

namespace App\Http\Controllers;

use App\Http\Requests\Venda\StoreVendaRequest;
use App\Http\Requests\Venda\UpdateVendaRequest;
use App\Models\Venda;
use App\Services\VendasService;
use Symfony\Component\HttpFoundation\Response;

class VendasController extends Controller
{
    public function __construct(protected VendasService $vendasService) {}

    public function index()
    {
        $vendas = $this->vendasService->buscaTodasVendas();
        return response()->json($vendas, Response::HTTP_OK);
    }

    public function store(StoreVendaRequest $request)
    {
        $validated = $request->validated();
        $venda = $this->vendasService->criarVenda($validated);
        return response()->json($venda, Response::HTTP_CREATED);
    }

    public function show(Venda $venda)
    {
        return response()->json($venda->load(['produto', 'usuario']), Response::HTTP_OK);
    }

    // Verrificar se é necessario implementar a atualização de vendas, pois isso pode complicar o controle de estoque.

    // public function update(UpdateVendaRequest $request, Venda $venda)
    // {
    //     $validated = $request->validated();
    //     $venda = $this->vendasService->atualizarVenda($venda, $validated);
    //     return response()->json($venda, Response::HTTP_OK);
    // }

    public function destroy(Venda $venda)
    {
        $this->vendasService->deletarVenda($venda);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

