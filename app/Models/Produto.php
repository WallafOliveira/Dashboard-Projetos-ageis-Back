<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'sku',
        'categoria',
        'quantidade',
        'preco_unitario',
        'status',
        'categoria_abc',
        'custo_unitario',
        'estoque_minimo',
        'prazo_reposicao_dias'
    ];
}
