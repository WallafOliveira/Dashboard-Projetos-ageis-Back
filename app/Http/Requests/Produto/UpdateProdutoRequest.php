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
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
            'quantidade' => 'required|integer|min:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'descricao' => 'descrição',
            'preco' => 'preço',
            'quantidade' => 'quantidade em estoque',
        ];
    }
}
