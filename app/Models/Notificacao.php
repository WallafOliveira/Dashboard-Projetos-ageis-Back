<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'titulo',
        'mensagem',
        'lida',
        'tipo',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
