<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_razao_social', 'data_cadastro', 'status', 'custo_aquisicao', 'ltv_estimado', 'regiao'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function propostas()
    {
        return $this->hasMany(PropostaComercial::class);
    }
}
