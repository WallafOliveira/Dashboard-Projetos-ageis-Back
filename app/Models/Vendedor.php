<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    use HasFactory;

    protected $table = 'vendedores';

    protected $fillable = [
        'nome', 'regiao', 'meta_mensal'
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
