<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Validation\ValidationException;

class VendasService
{
    public function buscaTodasVendas()
    {
        return Venda::with(['produto', 'usuario'])->get();
    }

    public function criarVenda(array $dados)
    {
        $produto = Produto::findOrFail($dados['produto_id']);

        if ($produto->quantidade < $dados['quantidade']) {
            throw ValidationException::withMessages([
                'quantidade' => "Estoque insuficiente. Disponível: {$produto->quantidade}.",
            ]);
        }

        $produto->decrement('quantidade', $dados['quantidade']);

        return Venda::create([
            'produto_id'  => $dados['produto_id'],
            'usuario_id'  => $dados['usuario_id'],
            'quantidade'  => $dados['quantidade'],
            'preco_total' => $produto->preco * $dados['quantidade'],
        ]);
    }

    // Verrificar se é necessario implementar a atualização de vendas, pois isso pode complicar o controle de estoque.

    // public function atualizarVenda(Venda $venda, array $dados)
    // {
    //     $produto = Produto::findOrFail($dados['produto_id']);

    //     $estoqueDisponivel = $produto->quantidade + $venda->quantidade;

    //     if ($estoqueDisponivel < $dados['quantidade']) {
    //         throw ValidationException::withMessages([
    //             'quantidade' => "Estoque insuficiente. Disponível: {$estoqueDisponivel}.",
    //         ]);
    //     }

    //     $produto->quantidade = $estoqueDisponivel - $dados['quantidade'];
    //     $produto->save();

    //     $venda->update([
    //         'produto_id'  => $dados['produto_id'],
    //         'usuario_id'  => $dados['usuario_id'],
    //         'quantidade'  => $dados['quantidade'],
    //         'preco_total' => $produto->preco * $dados['quantidade'],
    //     ]);

    //     return $venda;
    // }

    public function deletarVenda(Venda $venda)
    {
        $venda->produto->increment('quantidade', $venda->quantidade);
        $venda->delete();
    }
}

