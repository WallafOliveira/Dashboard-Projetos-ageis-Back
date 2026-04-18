<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PainelAnaliticoService
{
    private const GRAFICO_POR_DOMINIO = [
        'financeiro' => 'financeRevenueChart',
        'comercial' => 'commercialPipelineChart',
        'operacoes' => 'operationsEfficiencyChart',
        'logistica' => 'logisticsDeliveryChart',
    ];

    public function dominioSuportado(string $dominio): bool
    {
        return array_key_exists($dominio, self::GRAFICO_POR_DOMINIO);
    }

    public function graficoSuportado(string $chaveGrafico): bool
    {
        return in_array($chaveGrafico, array_values(self::GRAFICO_POR_DOMINIO), true);
    }

    public function obterPanorama(array $filtros = []): array
    {
        $payload = $this->normalizarPayload($filtros);
        $periodo = $this->obterPeriodo($payload);
        $intervalo = [$periodo['de'], $periodo['ate']];

        $receita = (float) DB::table('vendas')->whereBetween('created_at', $intervalo)->sum('preco_total');
        $custo = (float) DB::table('vendas')
            ->join('produtos', 'produtos.id', '=', 'vendas.produto_id')
            ->whereBetween('vendas.created_at', $intervalo)
            ->sum(DB::raw('vendas.quantidade * produtos.preco'));

        $ticketMedio = (float) DB::table('vendas')->whereBetween('created_at', $intervalo)->avg('preco_total');
        $totalVendas = (int) DB::table('vendas')->whereBetween('created_at', $intervalo)->count();

        $totalFeedbacks = (int) DB::table('fedback')->whereBetween('created_at', $intervalo)->count();
        $feedbacksBons = (int) DB::table('fedback')->whereBetween('created_at', $intervalo)->where('avaliacao', '>=', 4)->count();
        $slaEntrega = $totalFeedbacks > 0 ? round(($feedbacksBons / $totalFeedbacks) * 100, 2) : 0;

        $totalProdutos = (int) DB::table('produtos')->count();
        $produtosRuptura = (int) DB::table('produtos')->where('quantidade', '<=', 0)->count();
        $rupturaPercentual = $totalProdutos > 0 ? round(($produtosRuptura / $totalProdutos) * 100, 2) : 0;

        $margemPercentual = $receita > 0 ? round((($receita - $custo) / $receita) * 100, 2) : 0;
        $oeeProxy = $this->calcularProdutividadeMedia($intervalo);

        return [
            'meta' => [
                'painel' => 'panorama-corporativo',
                'timestamp' => now()->toIso8601String(),
                'last_updated_at' => now()->toIso8601String(),
            ],
            'applied_filters' => $this->resolverFiltrosAplicados($payload),
            'cards' => [
                'financeiro' => [
                    'receita_acumulada' => $receita,
                    'margem_ebitda' => $margemPercentual,
                    'caixa_disponivel' => round($receita - $custo, 2),
                    'inadimplencia' => round(100 - $slaEntrega, 2),
                ],
                'comercial' => [
                    'receita_fechada' => $receita,
                    'pipeline_qualificado' => round($receita * 1.2, 2),
                    'taxa_conversao' => $totalVendas > 0 ? round(($totalVendas / ($totalVendas + 120)) * 100, 2) : 0,
                    'ticket_medio' => $ticketMedio,
                ],
                'operacoes' => [
                    'oee_consolidado' => $oeeProxy,
                    'ordens_concluidas' => $totalVendas,
                    'retrabalho' => round(max(0, 100 - $oeeProxy) / 3, 2),
                    'capacidade_usada' => round(min(100, $oeeProxy + 8), 2),
                ],
                'logistica' => [
                    'pedidos_expedidos' => $totalVendas,
                    'sla_entrega' => $slaEntrega,
                    'cobertura_estoque' => $this->calcularCoberturaDias($intervalo),
                    'ruptura' => $rupturaPercentual,
                ],
            ],
            'fontes_dados' => [
                'financeiro' => ['vendas', 'produtos'],
                'comercial' => ['vendas', 'usuarios'],
                'operacoes' => ['vendas', 'produtos'],
                'logistica' => ['vendas', 'fedback', 'produtos'],
            ],
        ];
    }

    public function obterVisaoTatica(string $dominio, array $filtros = []): array
    {
        $payload = $this->normalizarPayload($filtros);
        $periodo = $this->obterPeriodo($payload);

        $dados = match ($dominio) {
            'financeiro' => $this->obterTaticaFinanceiro($periodo),
            'comercial' => $this->obterTaticaComercial($periodo),
            'operacoes' => $this->obterTaticaOperacoes($periodo),
            'logistica' => $this->obterTaticaLogistica($periodo),
            default => ['indicadores' => [], 'tabelas' => []],
        };

        return [
            'meta' => [
                'painel' => $dominio.'-tatico',
                'dominio' => $dominio,
                'timestamp' => now()->toIso8601String(),
                'last_updated_at' => now()->toIso8601String(),
            ],
            'applied_filters' => $this->resolverFiltrosAplicados($payload),
            'indicadores' => $dados['indicadores'],
            'tabelas' => $dados['tabelas'],
        ];
    }

    public function obterVisaoGeral(string $dominio, array $filtros = []): array
    {
        $payload = $this->normalizarPayload($filtros);
        $chaveGrafico = self::GRAFICO_POR_DOMINIO[$dominio];

        return [
            'meta' => [
                'dominio' => $dominio,
                'timestamp' => now()->toIso8601String(),
                'granularidade' => $payload['periodo']['granularidade'] ?? 'mes',
                'last_updated_at' => now()->toIso8601String(),
            ],
            'applied_filters' => $this->resolverFiltrosAplicados($payload),
            'kpis' => $this->obterKpisPorDominio($dominio, $payload),
            'charts' => [$this->obterPayloadGrafico($chaveGrafico, $payload)],
            'tables' => [$this->obterTabelaPorDominio($dominio, $payload)],
        ];
    }

    public function obterOpcoesFiltros(string $dominio): array
    {
        $filtrosGlobais = [
            'periodo_de' => ['type' => 'date'],
            'periodo_ate' => ['type' => 'date'],
            'granularidade' => ['options' => ['dia', 'semana', 'mes']],
            'empresa_id' => ['type' => 'string'],
            'unidade_id' => ['type' => 'string'],
            'timezone' => ['options' => ['America/Sao_Paulo']],
            'comparacao' => ['options' => ['vs_periodo_anterior', 'vs_meta', 'vs_ano_anterior']],
        ];

        $filtrosDominio = [
            'financeiro' => [
                'centro_custo' => ['CC-100', 'CC-200'],
                'conta_contabil' => ['3.1.01', '3.1.02'],
                'tipo_movimento' => ['entrada', 'saida'],
                'cenario' => ['realizado', 'orcado', 'projetado'],
            ],
            'comercial' => [
                'canal' => ['inside_sales', 'field_sales', 'parcerias'],
                'vendedor_id' => ['V-10', 'V-11'],
                'status_oportunidade' => ['aberta', 'ganha', 'perdida'],
            ],
            'operacoes' => [
                'linha_id' => ['LIN-01', 'LIN-03'],
                'turno' => ['A', 'B', 'C'],
                'causa_perda' => ['setup', 'parada_nao_planejada'],
            ],
            'logistica' => [
                'rota' => ['SUL', 'SUDESTE', 'NORTE'],
                'transportadora_id' => ['T-01', 'T-04'],
                'status_entrega' => ['entregue', 'entregue_no_prazo', 'atrasada'],
            ],
        ];

        return [
            'globais' => $filtrosGlobais,
            'dominio' => $filtrosDominio[$dominio],
        ];
    }

    public function obterPayloadGrafico(string $chaveGrafico, array $payload = []): array
    {
        $payloadNormalizado = $this->normalizarPayload($payload);

        return match ($chaveGrafico) {
            'financeRevenueChart' => $this->graficoFinanceiroReceitaMargem($payloadNormalizado),
            'commercialPipelineChart' => $this->graficoComercialPipeline($payloadNormalizado),
            'operationsEfficiencyChart' => $this->graficoOperacoesEficiencia($payloadNormalizado),
            'logisticsDeliveryChart' => $this->graficoLogisticaEntregas($payloadNormalizado),
            default => $this->graficoVazio('line', $chaveGrafico, 'Grafico indisponivel', ['Serie 1', 'Serie 2'], 'numero', '', '', $payloadNormalizado),
        };
    }

    private function graficoFinanceiroReceitaMargem(array $payload): array
    {
        $periodo = $this->obterPeriodo($payload);
        $expressaoAgrupamento = $this->expressaoAgrupamento($periodo['granularidade'], 'vendas.created_at');

        $linhas = DB::table('vendas')
            ->join('produtos', 'produtos.id', '=', 'vendas.produto_id')
            ->whereBetween('vendas.created_at', [$periodo['de'], $periodo['ate']])
            ->selectRaw($expressaoAgrupamento.' as categoria')
            ->selectRaw('SUM(vendas.preco_total) as receita')
            ->selectRaw('SUM(vendas.preco_total - (vendas.quantidade * produtos.preco)) as margem')
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->get();

        if ($linhas->isEmpty()) {
            return $this->graficoVazio('area', 'financeRevenueChart', 'Receita e margem por periodo', ['Receita', 'Margem'], 'moeda_brl', '', 'R$ ', $payload);
        }

        return [
            'chartKey' => 'financeRevenueChart',
            'chartType' => 'area',
            'titulo' => 'Receita e margem por periodo',
            'categories' => $linhas->pluck('categoria')->values()->toArray(),
            'series' => [
                ['name' => 'Receita', 'data' => $linhas->pluck('receita')->map(fn ($valor) => (float) $valor)->values()->toArray()],
                ['name' => 'Margem', 'data' => $linhas->pluck('margem')->map(fn ($valor) => (float) $valor)->values()->toArray()],
            ],
            'formatacao' => [
                'yAxisTipo' => 'moeda_brl',
                'sufixo' => '',
                'prefixo' => 'R$ ',
            ],
            'appliedFilters' => $this->resolverFiltrosAplicados($payload),
            'updatedAt' => now()->toIso8601String(),
        ];
    }

    private function graficoComercialPipeline(array $payload): array
    {
        $periodo = $this->obterPeriodo($payload);

        $linhas = DB::table('vendas')
            ->join('usuarios', 'usuarios.id', '=', 'vendas.usuario_id')
            ->whereBetween('vendas.created_at', [$periodo['de'], $periodo['ate']])
            ->selectRaw('usuarios.nome as categoria')
            ->selectRaw('SUM(vendas.preco_total) as pipeline_total')
            ->selectRaw('COUNT(vendas.id) as vendas_fechadas')
            ->groupBy('usuarios.id', 'usuarios.nome')
            ->orderByDesc('pipeline_total')
            ->limit(8)
            ->get();

        if ($linhas->isEmpty()) {
            return $this->graficoVazio('bar', 'commercialPipelineChart', 'Pipeline e fechamentos por vendedor', ['Pipeline', 'Fechadas'], 'quantidade', '', '', $payload);
        }

        return [
            'chartKey' => 'commercialPipelineChart',
            'chartType' => 'bar',
            'titulo' => 'Pipeline e fechamentos por vendedor',
            'categories' => $linhas->pluck('categoria')->values()->toArray(),
            'series' => [
                ['name' => 'Pipeline', 'data' => $linhas->pluck('pipeline_total')->map(fn ($valor) => (float) $valor)->values()->toArray()],
                ['name' => 'Fechadas', 'data' => $linhas->pluck('vendas_fechadas')->map(fn ($valor) => (int) $valor)->values()->toArray()],
            ],
            'formatacao' => [
                'yAxisTipo' => 'quantidade',
                'sufixo' => '',
                'prefixo' => '',
            ],
            'appliedFilters' => $this->resolverFiltrosAplicados($payload),
            'updatedAt' => now()->toIso8601String(),
        ];
    }

    private function graficoOperacoesEficiencia(array $payload): array
    {
        $periodo = $this->obterPeriodo($payload);
        $expressaoAgrupamento = $this->expressaoAgrupamento($periodo['granularidade']);

        $linhas = DB::table('vendas')
            ->whereBetween('created_at', [$periodo['de'], $periodo['ate']])
            ->selectRaw($expressaoAgrupamento.' as categoria')
            ->selectRaw('COUNT(id) as volume')
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->get();

        if ($linhas->isEmpty()) {
            return $this->graficoVazio('line', 'operationsEfficiencyChart', 'Indice de produtividade operacional', ['Produtividade', 'Meta'], 'percentual', '%', '', $payload, ['min' => 0, 'max' => 100]);
        }

        $maiorVolume = $linhas->max('volume') ?: 1;
        $produtividade = $linhas
            ->map(function ($linha) use ($maiorVolume) {
                return round((((int) $linha->volume) / $maiorVolume) * 100, 2);
            })
            ->values()
            ->toArray();

        return [
            'chartKey' => 'operationsEfficiencyChart',
            'chartType' => 'line',
            'titulo' => 'Indice de produtividade operacional',
            'categories' => $linhas->pluck('categoria')->values()->toArray(),
            'series' => [
                ['name' => 'Produtividade', 'data' => $produtividade],
                ['name' => 'Meta', 'data' => array_fill(0, count($produtividade), 80)],
            ],
            'eixoY' => [
                'min' => 0,
                'max' => 100,
            ],
            'formatacao' => [
                'yAxisTipo' => 'percentual',
                'sufixo' => '%',
                'prefixo' => '',
            ],
            'appliedFilters' => $this->resolverFiltrosAplicados($payload),
            'updatedAt' => now()->toIso8601String(),
        ];
    }

    private function graficoLogisticaEntregas(array $payload): array
    {
        $periodo = $this->obterPeriodo($payload);
        $expressaoAgrupamento = $this->expressaoAgrupamento($periodo['granularidade']);

        $pedidosExpedidos = DB::table('vendas')
            ->whereBetween('created_at', [$periodo['de'], $periodo['ate']])
            ->selectRaw($expressaoAgrupamento.' as categoria')
            ->selectRaw('COUNT(id) as total')
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->get()
            ->keyBy('categoria');

        $entregasNoPrazo = DB::table('fedback')
            ->whereBetween('created_at', [$periodo['de'], $periodo['ate']])
            ->where('avaliacao', '>=', 4)
            ->selectRaw($expressaoAgrupamento.' as categoria')
            ->selectRaw('COUNT(id) as total')
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->get()
            ->keyBy('categoria');

        if ($pedidosExpedidos->isEmpty() && $entregasNoPrazo->isEmpty()) {
            return $this->graficoVazio('area', 'logisticsDeliveryChart', 'Pedidos expedidos e entregas no prazo', ['Pedidos expedidos', 'No prazo'], 'quantidade', '', '', $payload);
        }

        $categorias = $pedidosExpedidos->keys()->merge($entregasNoPrazo->keys())->unique()->sort()->values();

        return [
            'chartKey' => 'logisticsDeliveryChart',
            'chartType' => 'area',
            'titulo' => 'Pedidos expedidos e entregas no prazo',
            'categories' => $categorias->toArray(),
            'series' => [
                [
                    'name' => 'Pedidos expedidos',
                    'data' => $categorias->map(function ($categoria) use ($pedidosExpedidos) {
                        return (int) ($pedidosExpedidos[$categoria]->total ?? 0);
                    })->toArray(),
                ],
                [
                    'name' => 'No prazo',
                    'data' => $categorias->map(function ($categoria) use ($entregasNoPrazo) {
                        return (int) ($entregasNoPrazo[$categoria]->total ?? 0);
                    })->toArray(),
                ],
            ],
            'formatacao' => [
                'yAxisTipo' => 'quantidade',
                'sufixo' => '',
                'prefixo' => '',
            ],
            'appliedFilters' => $this->resolverFiltrosAplicados($payload),
            'updatedAt' => now()->toIso8601String(),
        ];
    }

    private function obterKpisPorDominio(string $dominio, array $payload): array
    {
        $periodo = $this->obterPeriodo($payload);
        $intervaloAtual = [$periodo['de'], $periodo['ate']];
        $intervaloAnterior = $this->obterIntervaloAnterior($periodo['de'], $periodo['ate']);

        $receitaAtual = (float) DB::table('vendas')->whereBetween('created_at', $intervaloAtual)->sum('preco_total');
        $receitaAnterior = (float) DB::table('vendas')->whereBetween('created_at', $intervaloAnterior)->sum('preco_total');
        $vendasAtual = (int) DB::table('vendas')->whereBetween('created_at', $intervaloAtual)->count();
        $vendasAnterior = (int) DB::table('vendas')->whereBetween('created_at', $intervaloAnterior)->count();
        $ticketAtual = (float) DB::table('vendas')->whereBetween('created_at', $intervaloAtual)->avg('preco_total');
        $ticketAnterior = (float) DB::table('vendas')->whereBetween('created_at', $intervaloAnterior)->avg('preco_total');

        return match ($dominio) {
            'financeiro' => [
                $this->montarKpi('receita_liquida', $receitaAtual, $receitaAnterior),
                $this->montarKpi('total_vendas', $vendasAtual, $vendasAnterior),
                $this->montarKpi('ticket_medio', $ticketAtual, $ticketAnterior),
            ],
            'comercial' => [
                $this->montarKpi('pipeline_total', $receitaAtual, $receitaAnterior),
                $this->montarKpi('vendas_fechadas', $vendasAtual, $vendasAnterior),
                $this->montarKpi('ticket_medio', $ticketAtual, $ticketAnterior),
            ],
            'operacoes' => [
                $this->montarKpi(
                    'produtos_cadastrados_periodo',
                    (int) DB::table('produtos')->whereBetween('created_at', $intervaloAtual)->count(),
                    (int) DB::table('produtos')->whereBetween('created_at', $intervaloAnterior)->count()
                ),
                $this->montarKpi('movimentacoes_venda', $vendasAtual, $vendasAnterior),
                $this->montarKpi(
                    'estoque_total_atual',
                    (int) DB::table('produtos')->sum('quantidade'),
                    (int) DB::table('produtos')->sum('quantidade')
                ),
            ],
            'logistica' => [
                $this->montarKpi('pedidos_expedidos', $vendasAtual, $vendasAnterior),
                $this->montarKpi(
                    'entregas_bem_avaliadas',
                    (int) DB::table('fedback')->whereBetween('created_at', $intervaloAtual)->where('avaliacao', '>=', 4)->count(),
                    (int) DB::table('fedback')->whereBetween('created_at', $intervaloAnterior)->where('avaliacao', '>=', 4)->count()
                ),
                $this->montarKpi('receita_movimentada', $receitaAtual, $receitaAnterior),
            ],
            default => [],
        };
    }

    private function obterTabelaPorDominio(string $dominio, array $payload): array
    {
        $periodo = $this->obterPeriodo($payload);

        return match ($dominio) {
            'financeiro' => $this->tabelaFinanceiro($periodo),
            'comercial' => $this->tabelaComercial($periodo),
            'operacoes' => $this->tabelaOperacoes($periodo),
            'logistica' => $this->tabelaLogistica($periodo),
            default => $this->tabelaVazia('resumo_'.$dominio),
        };
    }

    private function tabelaFinanceiro(array $periodo): array
    {
        $linhas = DB::table('vendas')
            ->join('produtos', 'produtos.id', '=', 'vendas.produto_id')
            ->whereBetween('vendas.created_at', [$periodo['de'], $periodo['ate']])
            ->selectRaw('produtos.nome as produto')
            ->selectRaw('COUNT(vendas.id) as vendas')
            ->selectRaw('SUM(vendas.preco_total) as receita')
            ->groupBy('produtos.id', 'produtos.nome')
            ->orderByDesc('receita')
            ->limit(10)
            ->get()
            ->map(function ($linha) {
                return [
                    'produto' => $linha->produto,
                    'vendas' => (int) $linha->vendas,
                    'receita' => (float) $linha->receita,
                ];
            })
            ->toArray();

        return $this->montarTabela('resumo_financeiro', $linhas, 'receita');
    }

    private function tabelaComercial(array $periodo): array
    {
        $linhas = DB::table('vendas')
            ->join('usuarios', 'usuarios.id', '=', 'vendas.usuario_id')
            ->whereBetween('vendas.created_at', [$periodo['de'], $periodo['ate']])
            ->selectRaw('usuarios.nome as vendedor')
            ->selectRaw('COUNT(vendas.id) as vendas_fechadas')
            ->selectRaw('SUM(vendas.preco_total) as pipeline_total')
            ->groupBy('usuarios.id', 'usuarios.nome')
            ->orderByDesc('pipeline_total')
            ->limit(10)
            ->get()
            ->map(function ($linha) {
                return [
                    'vendedor' => $linha->vendedor,
                    'vendas_fechadas' => (int) $linha->vendas_fechadas,
                    'pipeline_total' => (float) $linha->pipeline_total,
                ];
            })
            ->toArray();

        return $this->montarTabela('resumo_comercial', $linhas, 'pipeline_total');
    }

    private function tabelaOperacoes(array $periodo): array
    {
        $linhas = DB::table('vendas')
            ->join('produtos', 'produtos.id', '=', 'vendas.produto_id')
            ->whereBetween('vendas.created_at', [$periodo['de'], $periodo['ate']])
            ->selectRaw('produtos.nome as produto')
            ->selectRaw('SUM(vendas.quantidade) as quantidade_movimentada')
            ->selectRaw('COUNT(vendas.id) as ordens')
            ->groupBy('produtos.id', 'produtos.nome')
            ->orderByDesc('quantidade_movimentada')
            ->limit(10)
            ->get()
            ->map(function ($linha) {
                return [
                    'produto' => $linha->produto,
                    'quantidade_movimentada' => (int) $linha->quantidade_movimentada,
                    'ordens' => (int) $linha->ordens,
                ];
            })
            ->toArray();

        return $this->montarTabela('resumo_operacoes', $linhas, 'quantidade_movimentada');
    }

    private function tabelaLogistica(array $periodo): array
    {
        $linhas = DB::table('fedback')
            ->join('produtos', 'produtos.id', '=', 'fedback.produto_id')
            ->whereBetween('fedback.created_at', [$periodo['de'], $periodo['ate']])
            ->selectRaw('produtos.nome as produto')
            ->selectRaw('COUNT(fedback.id) as total_feedbacks')
            ->selectRaw('AVG(fedback.avaliacao) as avaliacao_media')
            ->groupBy('produtos.id', 'produtos.nome')
            ->orderByDesc('total_feedbacks')
            ->limit(10)
            ->get()
            ->map(function ($linha) {
                return [
                    'produto' => $linha->produto,
                    'total_feedbacks' => (int) $linha->total_feedbacks,
                    'avaliacao_media' => round((float) ($linha->avaliacao_media ?? 0), 2),
                ];
            })
            ->toArray();

        return $this->montarTabela('resumo_logistica', $linhas, 'total_feedbacks');
    }

    private function tabelaVazia(string $chaveTabela): array
    {
        return [
            'tableKey' => $chaveTabela,
            'sorting' => [
                'field' => 'valor',
                'direction' => 'desc',
            ],
            'pagination' => [
                'page' => 1,
                'per_page' => 10,
                'total' => 0,
            ],
            'rows' => [],
        ];
    }

    private function montarTabela(string $chaveTabela, array $linhas, string $campoOrdenacao): array
    {
        return [
            'tableKey' => $chaveTabela,
            'sorting' => [
                'field' => $campoOrdenacao,
                'direction' => 'desc',
            ],
            'pagination' => [
                'page' => 1,
                'per_page' => 10,
                'total' => count($linhas),
            ],
            'rows' => $linhas,
        ];
    }

    private function resolverFiltrosAplicados(array $payload): array
    {
        $periodo = $this->obterPeriodo($payload);
        $filtrosGlobais = (array) ($payload['filtrosGlobais'] ?? []);
        $filtrosDominio = (array) ($payload['filtrosDominio'] ?? []);

        return array_merge([
            'periodoDe' => $periodo['de'],
            'periodoAte' => $periodo['ate'],
            'granularidade' => $periodo['granularidade'],
            'empresaId' => $filtrosGlobais['empresaId'] ?? '',
            'unidadeIds' => $filtrosGlobais['unidadeIds'] ?? [],
            'timezone' => $filtrosGlobais['timezone'] ?? 'America/Sao_Paulo',
            'comparacao' => $filtrosGlobais['comparacao'] ?? 'vs_periodo_anterior',
        ], $filtrosDominio);
    }

    private function normalizarPayload(array $payload): array
    {
        if (isset($payload['periodo']) || isset($payload['filtrosGlobais']) || isset($payload['filtrosDominio'])) {
            return $payload;
        }

        return [
            'periodo' => [
                'de' => $payload['periodo_de'] ?? '',
                'ate' => $payload['periodo_ate'] ?? '',
                'granularidade' => $payload['granularidade'] ?? '',
            ],
            'filtrosGlobais' => [
                'empresaId' => $payload['empresa_id'] ?? '',
                'unidadeIds' => isset($payload['unidade_id']) ? [$payload['unidade_id']] : [],
                'timezone' => $payload['timezone'] ?? '',
                'comparacao' => $payload['comparacao'] ?? '',
            ],
            'filtrosDominio' => [],
        ];
    }

    private function obterPeriodo(array $payload): array
    {
        $periodo = (array) ($payload['periodo'] ?? []);
        $dataDe = $periodo['de'] ?? now()->startOfYear()->format('Y-m-d');
        $dataAte = $periodo['ate'] ?? now()->endOfYear()->format('Y-m-d');
        $granularidade = $periodo['granularidade'] ?? 'mes';

        return [
            'de' => Carbon::parse($dataDe)->startOfDay()->toDateTimeString(),
            'ate' => Carbon::parse($dataAte)->endOfDay()->toDateTimeString(),
            'granularidade' => in_array($granularidade, ['dia', 'semana', 'mes']) ? $granularidade : 'mes',
        ];
    }

    private function obterIntervaloAnterior(string $de, string $ate): array
    {
        $inicio = Carbon::parse($de);
        $fim = Carbon::parse($ate);
        $quantidadeDias = $inicio->diffInDays($fim) + 1;

        return [
            $inicio->copy()->subDays($quantidadeDias)->startOfDay()->toDateTimeString(),
            $inicio->copy()->subDay()->endOfDay()->toDateTimeString(),
        ];
    }

    private function expressaoAgrupamento(string $granularidade, string $coluna = 'created_at'): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return match ($granularidade) {
                'dia' => "strftime('%Y-%m-%d', $coluna)",
                'semana' => "strftime('%Y-W%W', $coluna)",
                default => "strftime('%Y-%m', $coluna)",
            };
        }

        return match ($granularidade) {
            'dia' => "DATE_FORMAT($coluna, '%Y-%m-%d')",
            'semana' => "DATE_FORMAT($coluna, '%x-W%v')",
            default => "DATE_FORMAT($coluna, '%Y-%m')",
        };
    }

    private function montarKpi(string $chave, float|int $valorAtual, float|int $valorAnterior): array
    {
        $variacao = 0;

        if ($valorAnterior > 0) {
            $variacao = round((($valorAtual - $valorAnterior) / $valorAnterior) * 100, 2);
        }

        return [
            'key' => $chave,
            'valor_atual' => $valorAtual,
            'valor_anterior' => $valorAnterior,
            'variacao' => $variacao,
        ];
    }

    private function graficoVazio(
        string $tipo,
        string $chaveGrafico,
        string $titulo,
        array $nomesSeries,
        string $tipoEixoY,
        string $sufixo,
        string $prefixo,
        array $payload,
        array $eixoY = []
    ): array {
        $grafico = [
            'chartKey' => $chaveGrafico,
            'chartType' => $tipo,
            'titulo' => $titulo,
            'categories' => [],
            'series' => [
                ['name' => $nomesSeries[0] ?? 'Serie 1', 'data' => []],
                ['name' => $nomesSeries[1] ?? 'Serie 2', 'data' => []],
            ],
            'formatacao' => [
                'yAxisTipo' => $tipoEixoY,
                'sufixo' => $sufixo,
                'prefixo' => $prefixo,
            ],
            'appliedFilters' => $this->resolverFiltrosAplicados($payload),
            'updatedAt' => now()->toIso8601String(),
        ];

        if (! empty($eixoY)) {
            $grafico['eixoY'] = $eixoY;
        }

        return $grafico;
    }

    private function obterTaticaFinanceiro(array $periodo): array
    {
        $intervalo = [$periodo['de'], $periodo['ate']];

        $entradas = (float) DB::table('vendas')->whereBetween('created_at', $intervalo)->sum('preco_total');
        $saidas = (float) DB::table('vendas')
            ->join('produtos', 'produtos.id', '=', 'vendas.produto_id')
            ->whereBetween('vendas.created_at', $intervalo)
            ->sum(DB::raw('vendas.quantidade * produtos.preco'));

        $mediaEntradaDia = $this->mediaDiariaVendas($intervalo, 'entrada');
        $mediaSaidaDia = $this->mediaDiariaVendas($intervalo, 'saida');

        $linhas = [];
        for ($dia = 1; $dia <= 14; $dia++) {
            $linhas[] = [
                'dia' => 'D+'.$dia,
                'entradas_previstas' => round($mediaEntradaDia, 2),
                'saidas_planejadas' => round($mediaSaidaDia, 2),
                'saldo_projetado' => round(($mediaEntradaDia - $mediaSaidaDia) * $dia, 2),
            ];
        }

        return [
            'indicadores' => [
                'entradas_previstas' => round($entradas, 2),
                'saidas_planejadas' => round($saidas, 2),
                'saldo_projetado_d14' => round(($mediaEntradaDia - $mediaSaidaDia) * 14, 2),
            ],
            'tabelas' => [
                $this->montarTabela('fluxo_caixa_d14', $linhas, 'saldo_projetado'),
            ],
        ];
    }

    private function obterTaticaComercial(array $periodo): array
    {
        $intervalo = [$periodo['de'], $periodo['ate']];
        $totalOportunidades = (int) DB::table('vendas')->whereBetween('created_at', $intervalo)->count();

        $etapas = [
            'Prospeccao' => 0.38,
            'Qualificacao' => 0.27,
            'Proposta' => 0.19,
            'Negociacao' => 0.11,
            'Fechamento' => 0.05,
        ];

        $linhas = [];
        foreach ($etapas as $etapa => $peso) {
            $qtd = (int) round($totalOportunidades * $peso);
            $linhas[] = [
                'etapa' => $etapa,
                'oportunidades' => $qtd,
                'prioridade' => $qtd > 100 ? 'alta' : ($qtd > 40 ? 'media' : 'baixa'),
            ];
        }

        return [
            'indicadores' => [
                'prospeccao' => $linhas[0]['oportunidades'] ?? 0,
                'qualificacao' => $linhas[1]['oportunidades'] ?? 0,
                'proposta' => $linhas[2]['oportunidades'] ?? 0,
                'negociacao' => $linhas[3]['oportunidades'] ?? 0,
            ],
            'tabelas' => [
                $this->montarTabela('pipeline_comercial', $linhas, 'oportunidades'),
            ],
        ];
    }

    private function obterTaticaOperacoes(array $periodo): array
    {
        $intervalo = [$periodo['de'], $periodo['ate']];

        $linhas = DB::table('vendas')
            ->join('produtos', 'produtos.id', '=', 'vendas.produto_id')
            ->whereBetween('vendas.created_at', $intervalo)
            ->selectRaw('((produtos.id % 4) + 1) as linha_numero')
            ->selectRaw('COUNT(vendas.id) as ordens')
            ->selectRaw('SUM(vendas.quantidade) as quantidade')
            ->groupBy('linha_numero')
            ->orderBy('linha_numero')
            ->get()
            ->map(function ($item) {
                return [
                    'linha' => 'Linha '.str_pad((string) $item->linha_numero, 2, '0', STR_PAD_LEFT),
                    'ordens' => (int) $item->ordens,
                    'quantidade' => (int) $item->quantidade,
                    'perda_estimada_percentual' => round(max(1, 12 - ($item->ordens / 40)), 2),
                ];
            })
            ->toArray();

        return [
            'indicadores' => [
                'linha_01' => $linhas[0]['quantidade'] ?? 0,
                'linha_02' => $linhas[1]['quantidade'] ?? 0,
                'linha_03' => $linhas[2]['quantidade'] ?? 0,
                'linha_04' => $linhas[3]['quantidade'] ?? 0,
            ],
            'tabelas' => [
                $this->montarTabela('produtividade_capacidade', $linhas, 'quantidade'),
            ],
        ];
    }

    private function obterTaticaLogistica(array $periodo): array
    {
        $intervalo = [$periodo['de'], $periodo['ate']];

        $totalProdutos = (int) DB::table('produtos')->count();
        $itensClasseA = (int) max(1, round($totalProdutos * 0.2));
        $cobertura = $this->calcularCoberturaDias($intervalo);

        $rupturaQtd = (int) DB::table('produtos')->where('quantidade', '<=', 0)->count();
        $giroAnual = $this->calcularGiroAnual($intervalo);

        $linhas = DB::table('produtos')
            ->select('nome', 'quantidade')
            ->orderByDesc('preco')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'produto' => $item->nome,
                    'estoque_atual' => (int) $item->quantidade,
                ];
            })
            ->toArray();

        return [
            'indicadores' => [
                'itens_classe_a' => $itensClasseA,
                'cobertura_media' => $cobertura,
                'ruptura_prevista' => $rupturaQtd,
                'giro_anual' => $giroAnual,
            ],
            'tabelas' => [
                $this->montarTabela('estoque_cobertura', $linhas, 'estoque_atual'),
            ],
        ];
    }

    private function mediaDiariaVendas(array $intervalo, string $tipo): float
    {
        $de = Carbon::parse($intervalo[0]);
        $ate = Carbon::parse($intervalo[1]);
        $dias = max(1, $de->diffInDays($ate) + 1);

        if ($tipo === 'saida') {
            $total = (float) DB::table('vendas')
                ->join('produtos', 'produtos.id', '=', 'vendas.produto_id')
                ->whereBetween('vendas.created_at', $intervalo)
                ->sum(DB::raw('vendas.quantidade * produtos.preco'));

            return $total / $dias;
        }

        $total = (float) DB::table('vendas')->whereBetween('created_at', $intervalo)->sum('preco_total');

        return $total / $dias;
    }

    private function calcularProdutividadeMedia(array $intervalo): float
    {
        $expressaoDia = $this->expressaoAgrupamento('dia');

        $porDia = DB::table('vendas')
            ->whereBetween('created_at', $intervalo)
            ->selectRaw($expressaoDia.' as dia')
            ->selectRaw('COUNT(id) as total')
            ->groupBy('dia')
            ->get();

        if ($porDia->isEmpty()) {
            return 0;
        }

        $maximo = (int) $porDia->max('total');
        if ($maximo === 0) {
            return 0;
        }

        $mediaNormalizada = $porDia->map(function ($item) use ($maximo) {
            return ((int) $item->total / $maximo) * 100;
        })->avg();

        return round((float) $mediaNormalizada, 2);
    }

    private function calcularCoberturaDias(array $intervalo): float
    {
        $estoqueTotal = (int) DB::table('produtos')->sum('quantidade');

        $de = Carbon::parse($intervalo[0]);
        $ate = Carbon::parse($intervalo[1]);
        $dias = max(1, $de->diffInDays($ate) + 1);

        $consumo = (int) DB::table('vendas')->whereBetween('created_at', $intervalo)->sum('quantidade');
        $consumoMedioDia = $consumo > 0 ? ($consumo / $dias) : 0;

        if ($consumoMedioDia <= 0) {
            return 0;
        }

        return round($estoqueTotal / $consumoMedioDia, 2);
    }

    private function calcularGiroAnual(array $intervalo): float
    {
        $quantidadeVendida = (int) DB::table('vendas')->whereBetween('created_at', $intervalo)->sum('quantidade');
        $estoqueTotal = (int) DB::table('produtos')->sum('quantidade');

        if ($estoqueTotal <= 0) {
            return 0;
        }

        return round($quantidadeVendida / $estoqueTotal, 2);
    }
}
