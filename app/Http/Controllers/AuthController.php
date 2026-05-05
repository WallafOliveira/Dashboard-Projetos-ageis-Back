<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = Usuario::where('email', $credentials['email'])->first();

        if (! $user || $user->senha !== $credentials['senha']) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'user' => $user,
            'token' => $token,
        ], Response::HTTP_OK);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ], Response::HTTP_OK);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user(), Response::HTTP_OK);
    }
}
