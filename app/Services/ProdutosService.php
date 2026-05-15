<?php

namespace App\Services;

use App\Models\Produto;
use Illuminate\Pagination\Paginator;

class ProdutosService
{
    /**
     * Busca produtos com filtros e paginação
     * 
     * @param int $page - Número da página (padrão: 1)
     * @param int $perPage - Itens por página (padrão: 50)
     * @param string|null $buscar - Termo de busca (nome, SKU ou categoria)
     * @param string|null $status - Filtro de status (OK, Baixo, Crítico)
     * @return array
     */
    public function buscaTodosProdutos($page = 1, $perPage = 50, $buscar = null, $status = null)
    {
        $query = Produto::query();

        // Filtro de busca - procura em nome, sku e categoria
        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nome', 'like', "%{$buscar}%")
                    ->orWhere('sku', 'like', "%{$buscar}%")
                    ->orWhere('categoria', 'like', "%{$buscar}%");
            });
        }

        // Filtro de status
        if ($status) {
            $query->where('status', $status);
        }

        // Paginar resultados
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginated->items(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'last_page' => $paginated->lastPage(),
        ];
    }

    public function criarProduto(array $dados)
    {
        return Produto::create([
            'nome' => $dados['nome'],
            'sku' => $dados['sku'],
            'categoria' => $dados['categoria'],
            'quantidade' => $dados['quantidade'],
            'preco_unitario' => $dados['preco_unitario'],
            'status' => $dados['status'] ?? 'OK',
        ]);
    }

    public function atualizarProduto(Produto $produto, array $dados)
    {
        $produto->update([
            'nome' => $dados['nome'] ?? $produto->nome,
            'sku' => $dados['sku'] ?? $produto->sku,
            'categoria' => $dados['categoria'] ?? $produto->categoria,
            'quantidade' => $dados['quantidade'] ?? $produto->quantidade,
            'preco_unitario' => $dados['preco_unitario'] ?? $produto->preco_unitario,
            'status' => $dados['status'] ?? $produto->status,
        ]);

        return $produto;
    }

    public function deletarProduto(Produto $produto)
    {
        $produto->delete();
    }
}