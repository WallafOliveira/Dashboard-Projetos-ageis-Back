<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

=======
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';
>>>>>>> a850cd7c88af1deddd85d5e4c3e34cc6a5112ba3
    protected $fillable = [
        'nome',
        'email',
        'senha',
        'perfil_acesso_id',
    ];

<<<<<<< HEAD
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

=======
>>>>>>> a850cd7c88af1deddd85d5e4c3e34cc6a5112ba3
    public function perfilAcesso()
    {
        return $this->belongsTo(PerfilAcesso::class, 'perfil_acesso_id');
    }
}
