<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'perfil_acesso_id',
    ];

    protected $hidden = [
        'senha',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    public function perfilAcesso()
    {
        return $this->belongsTo(PerfilAcesso::class, 'perfil_acesso_id');
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class, 'usuario_id');
    }
}
