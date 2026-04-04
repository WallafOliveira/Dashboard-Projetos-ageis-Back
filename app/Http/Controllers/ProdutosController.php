<?php

namespace App\Http\Controllers;

use App\Http\Requests\Produto\StoreProdutoRequest;
use App\Http\Requests\Produto\UpdateProdutoRequest;
use App\Models\Produto;
use App\Services\ProdutosService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProdutosController extends Controller
{
    public function __construct(protected ProdutosService $produtosService) {}

    public function index()
    {
        $produtos = $this->produtosService->buscaTodosProdutos();
        return response()->json($produtos, Response::HTTP_OK);
    }

    public function store(StoreProdutoRequest $request){
        $validated = $request->validated();
        $produto = $this->produtosService->criarProduto($validated);
        return response()->json($produto, Response::HTTP_CREATED);
    }

    public function show(Produto $produto)
    {
        return $produto;
    }

    public function update(UpdateProdutoRequest $request, Produto $produto)
    {
        $validated = $request->validated();
        $produto = $this->produtosService->atualizarProduto($produto, $validated);
        return response()->json($produto, Response::HTTP_OK);
    }

    public function destroy(Produto $produto)
    {
        $this->produtosService->deletarProduto($produto);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

}
