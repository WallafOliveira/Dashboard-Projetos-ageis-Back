<?php

namespace App\Services;
<<<<<<< HEAD

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
=======
use App\Models\Usuario;
>>>>>>> a850cd7c88af1deddd85d5e4c3e34cc6a5112ba3

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
<<<<<<< HEAD
        $dados['senha'] = Hash::make($dados['senha']);

        return Usuario::create([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'senha' => $dados['senha'],
            'perfil_acesso_id' => $dados['perfil_acesso_id'],
        ]);
=======
        return Usuario::create(
            [
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => $dados['senha'],
                'perfil_acesso_id' => $dados['perfil_acesso_id'],
            ]
        );
>>>>>>> a850cd7c88af1deddd85d5e4c3e34cc6a5112ba3
    }

    public function atualizarUsuario(Usuario $usuario, array $dados)
    {
<<<<<<< HEAD
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
=======
        $usuario->update(
            [
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => $dados['senha'],
                'perfil_acesso_id' => $dados['perfil_acesso_id'],
            ]
>>>>>>> a850cd7c88af1deddd85d5e4c3e34cc6a5112ba3
        );

        return $usuario;
    }

    public function deletarUsuario(Usuario $usuario)
    {
        $usuario->delete();
    }
}