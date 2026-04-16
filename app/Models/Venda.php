<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    protected $table = 'vendas';

    protected $fillable = [
        'produto_id',
        'usuario_id',
        'quantidade',
        'preco_total',
    ];

    protected $casts = [
        'preco_total' => 'float',
    ];

    public function getTotalAttribute(): float
    {
        return (float) $this->preco_total;
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
