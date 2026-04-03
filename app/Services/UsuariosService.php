<?php

namespace App\Services;
use App\Models\Usuario;

class UsuariosService
{
    public function buscaTodosUsuarios()
    {
        return Usuario::all();
    }

    // public function buscaUsuarioPorId(Usuario $usuario)
    // {
    //     return Usuario::findOrFail($usuario->id);
    // }

    public function criarUsuario(array $dados)
    {
        return Usuario::create(
            [
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => $dados['senha'],
                'perfil_acesso_id' => $dados['perfil_acesso_id'],
            ]
        );
    }

    public function atualizarUsuario(Usuario $usuario, array $dados)
    {
        $usuario->update(
            [
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => $dados['senha'],
                'perfil_acesso_id' => $dados['perfil_acesso_id'],
            ]
        );

        return $usuario;
    }

    public function deletarUsuario(Usuario $usuario)
    {
        $usuario->delete();
    }
}