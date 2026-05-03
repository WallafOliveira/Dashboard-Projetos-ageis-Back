<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', 'vendedor_id', 'data_pedido', 'data_conclusao', 'valor_total', 'custo_total', 'status'
    ];

    protected $casts = [
        'data_pedido' => 'datetime',
        'data_conclusao' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function transacoes()
    {
        return $this->hasMany(FinanceiroTransacao::class);
    }
}
