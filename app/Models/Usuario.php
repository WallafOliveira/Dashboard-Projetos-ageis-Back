<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $fillable = [
        'nome',
        'email',
        'senha',
        'perfil_acesso_id',
    ];

    public function perfilAcesso()
    {
        return $this->belongsTo(PerfilAcesso::class, 'perfil_acesso_id');
    }
}
