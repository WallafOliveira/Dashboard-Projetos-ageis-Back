<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'senha' => 'required|string|min:8',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'email',
            'senha' => 'senha',
        ];
    }
}
