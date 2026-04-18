<?php

namespace App\Services;

use App\Models\PerfilAcesso;

class PerfilsAcessoService
{
    public function buscaTodosPerfisAcesso()
    {
        return PerfilAcesso::all();
    }

    public function criarPerfilAcesso(array $dados)
    {
        return PerfilAcesso::create([
            'nome' => $dados['nome'],
        ]);
    }

    public function atualizarPerfilAcesso(PerfilAcesso $perfilAcesso, array $dados)
    {
        $perfilAcesso->update([
            'nome' => $dados['nome'],
        ]);

        return $perfilAcesso;
    }

    public function deletarPerfilAcesso(PerfilAcesso $perfilAcesso)
    {
        $perfilAcesso->delete();
    }
}