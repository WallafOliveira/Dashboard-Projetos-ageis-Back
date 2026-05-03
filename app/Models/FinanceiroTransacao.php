<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceiroTransacao extends Model
{
    use HasFactory;

    protected $table = 'financeiro_transacoes';

    protected $fillable = [
        'pedido_id', 'tipo', 'categoria', 'valor', 'data_vencimento', 'data_pagamento', 'status'
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
