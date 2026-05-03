<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropostaComercial extends Model
{
    use HasFactory;

    protected $table = 'propostas_comerciais';

    protected $fillable = [
        'cliente_id', 'vendedor_id', 'valor_estimado', 'fase_funil', 'status', 'data_criacao', 'data_fechamento'
    ];

    protected $casts = [
        'data_criacao' => 'date',
        'data_fechamento' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }
}
