<?php

use App\Models\Usuario;

class UsuariosService
{
    public function buscaTodosTopicos()
    {
        return Usuario::all();
    }
}