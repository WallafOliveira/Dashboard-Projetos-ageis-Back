<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\ConsultaGraficoRequest;
use App\Services\PainelAnaliticoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GraficoAnaliticoController extends Controller
{
    public function __construct(protected PainelAnaliticoService $painelAnaliticoService) {}

    public function exibir(Request $request, string $chaveGrafico): JsonResponse
    {
        if (! $this->painelAnaliticoService->graficoSuportado($chaveGrafico)) {
            return response()->json([
                'message' => 'Grafico informado e invalido.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(
            $this->painelAnaliticoService->obterPayloadGrafico($chaveGrafico, $request->all()),
            Response::HTTP_OK
        );
    }

    public function consultar(ConsultaGraficoRequest $request, string $chaveGrafico): JsonResponse
    {
        if (! $this->painelAnaliticoService->graficoSuportado($chaveGrafico)) {
            return response()->json([
                'message' => 'Grafico informado e invalido.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(
            $this->painelAnaliticoService->obterPayloadGrafico($chaveGrafico, $request->validated()),
            Response::HTTP_OK
        );
    }
}
