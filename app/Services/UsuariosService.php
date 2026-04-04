<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

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
        $dados['senha'] = Hash::make($dados['senha']);

        return Usuario::create([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'senha' => $dados['senha'],
            'perfil_acesso_id' => $dados['perfil_acesso_id'],
        ]);
    }

    public function atualizarUsuario(Usuario $usuario, array $dados)
    {
        if (! empty($dados['senha'])) {
            $dados['senha'] = Hash::make($dados['senha']);
        } else {
            unset($dados['senha']);
        }

        $usuario->update(
            array_filter([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => $dados['senha'] ?? null,
                'perfil_acesso_id' => $dados['perfil_acesso_id'],
            ], fn ($value) => $value !== null)
        );

        return $usuario;
    }

    public function deletarUsuario(Usuario $usuario)
    {
        $usuario->delete();
    }
}