<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'categoria_abc', 'custo_unitario', 'quantidade_atual', 'estoque_minimo', 'prazo_reposicao_dias'
    ];
}
