<?php

namespace App\Http\Requests\Produto;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|max:100|unique:produtos,sku,' . $this->produto->id,
            'categoria' => 'sometimes|string|max:100',
            'quantidade' => 'sometimes|integer|min:0',
            'preco_unitario' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:OK,Baixo,Crítico',
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
