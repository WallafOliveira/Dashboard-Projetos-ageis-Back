<?php

namespace App\Services;

use App\Models\Produto;

class ProdutosService
{
    public function buscaTodosProdutos()
    {
        return Produto::all();
    }

    public function criarProduto(array $dados)
    {
        return Produto::create(
            [
                'nome' => $dados['nome'],
                'descricao' => $dados['descricao'],
                'preco' => $dados['preco'],
                'quantidade' => $dados['quantidade'],
            ]
        );
    }

    public function atualizarProduto(Produto $produto, array $dados)
    {
        $produto->update(
            [
                'nome' => $dados['nome'],
                'descricao' => $dados['descricao'],
                'preco' => $dados['preco'],
                'quantidade' => $dados['quantidade'],
            ]
        );

        return $produto;
    }

    public function deletarProduto(Produto $produto)
    {
        $produto->delete();
    }
    
}