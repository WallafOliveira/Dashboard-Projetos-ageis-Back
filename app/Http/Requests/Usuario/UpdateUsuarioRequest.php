<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'nome' => 'required|string|max:255',
            'email' => 'required|email',
            'senha' => 'nullable|string|min:8',
            'perfil_acesso_id' => 'required|integer|exists:perfil_acesso,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'email' => 'email',
            'senha' => 'senha',
            'perfil_acesso_id' => 'perfil de acesso',
        ];
    }

}
