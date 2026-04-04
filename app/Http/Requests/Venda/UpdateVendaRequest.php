<?php

namespace App\Http\Requests\Venda;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'produto_id'  => 'required|integer|exists:produtos,id',
            'usuario_id'  => 'required|integer|exists:usuarios,id',
            'quantidade'  => 'required|integer|min:1',
        ];
    }

    public function attributes(): array
    {
        return [
            'produto_id' => 'produto',
            'usuario_id' => 'usuário',
            'quantidade' => 'quantidade',
        ];
    }
}
