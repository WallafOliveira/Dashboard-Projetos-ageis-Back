<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $request->input('message');
        
        // Obtém o contexto dos dados do dashboard
        $dashboardData = $this->dashboardService->getDashboardData(30); // últimos 30 dias

        // Prepara o prompt do sistema com o contexto
        $systemPrompt = "Você é um assistente virtual especializado em vendas e dados deste sistema (Panorama Corporativo / Projetos Ágeis).
Responda às perguntas do usuário com base EXCLUSIVAMENTE nos dados fornecidos abaixo. Seja conciso, profissional e use formatação Markdown se ajudar a legibilidade.

DADOS ATUAIS DO SISTEMA:
- Total de Usuários: {$dashboardData['total_usuarios']}
- Total de Produtos: {$dashboardData['total_produtos']}
- Total de Vendas Realizadas: {$dashboardData['total_vendas']}
- Faturamento Total: R$ " . number_format($dashboardData['faturamento'], 2, ',', '.') . "

As vendas por dia nos últimos dias foram:
" . json_encode($dashboardData['vendas_por_dia']) . "

Últimas vendas registradas:
" . json_encode($dashboardData['ultimas_vendas']) . "

Responda à seguinte pergunta do usuário:";

        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'error' => 'A chave de API do Gemini não está configurada no backend (.env).'
            ], 500);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nPergunta: " . $userMessage]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Extrai o texto da resposta do Gemini
                $aiText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Desculpe, não consegui processar sua resposta.';
                
                return response()->json([
                    'reply' => $aiText
                ]);
            } else {
                Log::error('Erro na API do Gemini: ' . $response->body());
                return response()->json([
                    'error' => 'Falha ao se comunicar com a inteligência artificial. Tente novamente mais tarde.',
                    'details' => $response->json()
                ], 502);
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao chamar o Gemini: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro interno ao processar sua requisição.'
            ], 500);
        }
    }
}
