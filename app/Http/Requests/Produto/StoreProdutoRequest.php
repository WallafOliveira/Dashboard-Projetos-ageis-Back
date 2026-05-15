<?php

namespace App\Http\Requests\Produto;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:produtos,sku',
            'categoria' => 'required|string|max:100',
            'quantidade' => 'required|integer|min:0',
            'preco_unitario' => 'required|numeric|min:0',
            'status' => 'required|in:OK,Baixo,Crítico',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'sku' => 'SKU',
            'categoria' => 'categoria',
            'quantidade' => 'quantidade',
            'preco_unitario' => 'preço unitário',
            'status' => 'status',
        ];
    }
}
