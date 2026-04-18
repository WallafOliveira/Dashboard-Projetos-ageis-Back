<?php

namespace App\Http\Controllers;

use App\Services\PainelAnaliticoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PainelAnaliticoController extends Controller
{
    public function __construct(protected PainelAnaliticoService $painelAnaliticoService) {}

    public function panorama(Request $request): JsonResponse
    {
        return response()->json(
            $this->painelAnaliticoService->obterPanorama($request->all()),
            Response::HTTP_OK
        );
    }

    public function visaoGeral(Request $request, string $dominio): JsonResponse
    {
        if (! $this->painelAnaliticoService->dominioSuportado($dominio)) {
            return response()->json([
                'message' => 'Dominio informado e invalido.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(
            $this->painelAnaliticoService->obterVisaoGeral($dominio, $request->all()),
            Response::HTTP_OK
        );
    }

    public function visaoTatica(Request $request, string $dominio): JsonResponse
    {
        if (! $this->painelAnaliticoService->dominioSuportado($dominio)) {
            return response()->json([
                'message' => 'Dominio informado e invalido.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(
            $this->painelAnaliticoService->obterVisaoTatica($dominio, $request->all()),
            Response::HTTP_OK
        );
    }

    public function filtros(string $dominio): JsonResponse
    {
        if (! $this->painelAnaliticoService->dominioSuportado($dominio)) {
            return response()->json([
                'message' => 'Dominio informado e invalido.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'dominio' => $dominio,
            'filtros' => $this->painelAnaliticoService->obterOpcoesFiltros($dominio),
        ], Response::HTTP_OK);
    }
}
