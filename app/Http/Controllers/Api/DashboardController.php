<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use App\Models\FinanceiroTransacao;
use App\Models\Cliente;
use App\Models\Vendedor;
use App\Models\Produto;
use App\Models\PropostaComercial;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Helpers
    private function getDateRange(Request $request)
    {
        $start = $request->input('data_inicio') ? Carbon::parse($request->input('data_inicio')) : Carbon::now()->subMonths(6)->startOfMonth();
        $end = $request->input('data_fim') ? Carbon::parse($request->input('data_fim')) : Carbon::now()->endOfMonth();
        return [$start, $end];
    }

    public function getVisaoGeral(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);

        $receitaTotal = FinanceiroTransacao::where('tipo', 'Receita')
            ->where('status', 'Pago')
            ->whereBetween('data_pagamento', [$start, $end])
            ->sum('valor');

        $quantidadeVendas = Pedido::whereBetween('data_pedido', [$start, $end])
            ->whereIn('status', ['Em Andamento', 'Concluído'])
            ->count();

        $novosClientes = Cliente::whereBetween('data_cadastro', [$start, $end])->count();

        $titulosPendentes = FinanceiroTransacao::where('status', 'Pendente')
            ->whereBetween('data_vencimento', [$start, $end])
            ->count();

        // Chart Data (Daily Revenue)
        $chartData = FinanceiroTransacao::select(DB::raw('DATE(data_pagamento) as date'), DB::raw('SUM(valor) as total'))
            ->where('tipo', 'Receita')
            ->where('status', 'Pago')
            ->whereBetween('data_pagamento', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'kpis' => [
                'receita_total' => $receitaTotal,
                'quantidade_vendas' => $quantidadeVendas,
                'novos_clientes' => $novosClientes,
                'titulos_pendentes' => $titulosPendentes,
            ],
            'charts' => [
                'evolucao_receita' => [
                    'categories' => $chartData->pluck('date'),
                    'series' => $chartData->pluck('total'),
                ]
            ]
        ]);
    }

    public function getEstrategico(Request $request)
    {
        $startYear = Carbon::now()->startOfYear();
        $endNow = Carbon::now();

        $receitaYTD = FinanceiroTransacao::where('tipo', 'Receita')
            ->where('status', 'Pago')
            ->whereBetween('data_pagamento', [$startYear, $endNow])
            ->sum('valor');

        $pedidos = Pedido::whereIn('status', ['Em Andamento', 'Concluído'])->get();
        $ticketMedio = $pedidos->avg('valor_total') ?? 0;

        $lucroYTD = $receitaYTD - FinanceiroTransacao::where('tipo', 'Despesa')
            ->where('status', 'Pago')
            ->whereBetween('data_pagamento', [$startYear, $endNow])
            ->sum('valor');
            
        $investimentoMarketing = FinanceiroTransacao::where('tipo', 'Despesa')
            ->where('categoria', 'Marketing')
            ->where('status', 'Pago')
            ->whereBetween('data_pagamento', [$startYear, $endNow])
            ->sum('valor');
            
        $roi = $investimentoMarketing > 0 ? (($lucroYTD - $investimentoMarketing) / $investimentoMarketing) * 100 : 0;

        // Chart: Receita vs Lucro (Monthly)
        $receitaMeses = FinanceiroTransacao::select(DB::raw('MONTH(data_pagamento) as month'), DB::raw('SUM(valor) as total'))
            ->where('tipo', 'Receita')->where('status', 'Pago')
            ->whereBetween('data_pagamento', [$startYear, $endNow])
            ->groupBy('month')->orderBy('month')->pluck('total', 'month')->toArray();
            
        $despesaMeses = FinanceiroTransacao::select(DB::raw('MONTH(data_pagamento) as month'), DB::raw('SUM(valor) as total'))
            ->where('tipo', 'Despesa')->where('status', 'Pago')
            ->whereBetween('data_pagamento', [$startYear, $endNow])
            ->groupBy('month')->orderBy('month')->pluck('total', 'month')->toArray();

        $months = range(1, Carbon::now()->month);
        $chartReceita = [];
        $chartLucro = [];
        foreach ($months as $m) {
            $rec = $receitaMeses[$m] ?? 0;
            $desp = $despesaMeses[$m] ?? 0;
            $chartReceita[] = $rec;
            $chartLucro[] = $rec - $desp;
        }

        return response()->json([
            'kpis' => [
                'receita_ytd' => $receitaYTD,
                'ticket_medio_geral' => $ticketMedio,
                'roi' => $roi,
            ],
            'charts' => [
                'receita_vs_lucro' => [
                    'categories' => $months,
                    'series_receita' => $chartReceita,
                    'series_lucro' => $chartLucro,
                ]
            ]
        ]);
    }

    public function getOperacional(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);
        $hoje = Carbon::today();

        $emAndamento = Pedido::where('status', 'Em Andamento')->count();
        $concluidosHoje = Pedido::where('status', 'Concluído')->whereDate('data_conclusao', $hoje)->count();
        $pendentes = Pedido::where('status', 'Pendente')->count();

        $pedidosConcluidos = Pedido::where('status', 'Concluído')->whereNotNull('data_conclusao')->get();
        $tmaSum = 0;
        foreach ($pedidosConcluidos as $p) {
            $tmaSum += Carbon::parse($p->data_pedido)->diffInHours(Carbon::parse($p->data_conclusao));
        }
        $tma = $pedidosConcluidos->count() > 0 ? $tmaSum / $pedidosConcluidos->count() : 0;

        $fluxoDiario = Pedido::select(DB::raw('DATE(data_conclusao) as date'), DB::raw('COUNT(*) as total'))
            ->where('status', 'Concluído')
            ->whereBetween('data_conclusao', [$start, $end])
            ->groupBy('date')->orderBy('date')->get();

        return response()->json([
            'kpis' => [
                'em_andamento' => $emAndamento,
                'concluidos_hoje' => $concluidosHoje,
                'pendencias' => $pendentes,
                'tma_horas' => round($tma, 2),
            ],
            'charts' => [
                'fluxo_diario' => [
                    'categories' => $fluxoDiario->pluck('date'),
                    'series' => $fluxoDiario->pluck('total'),
                ]
            ]
        ]);
    }

    public function getFinanceiro(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);

        $totalReceitas = FinanceiroTransacao::where('tipo', 'Receita')->whereBetween('data_vencimento', [$start, $end])->sum('valor');
        $totalDespesas = FinanceiroTransacao::where('tipo', 'Despesa')->whereBetween('data_vencimento', [$start, $end])->sum('valor');
        $lucroLiquido = $totalReceitas - $totalDespesas;
        $inadimplencia = FinanceiroTransacao::where('status', 'Atrasado/Inadimplente')->sum('valor');

        return response()->json([
            'kpis' => [
                'total_receitas' => $totalReceitas,
                'total_despesas' => $totalDespesas,
                'lucro_liquido' => $lucroLiquido,
                'valor_inadimplencia' => $inadimplencia,
            ],
            'charts' => [
                'receitas_vs_despesas' => [
                    'receitas' => $totalReceitas,
                    'despesas' => $totalDespesas
                ]
            ]
        ]);
    }

    public function getComercial(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);

        $vendas = Pedido::where('status', 'Concluído')->whereBetween('data_pedido', [$start, $end])->get();
        $totalVendas = $vendas->sum('valor_total');
        $ticketMedio = $vendas->avg('valor_total') ?? 0;

        $topVendedor = Vendedor::withSum(['pedidos' => function ($query) use ($start, $end) {
            $query->where('status', 'Concluído')->whereBetween('data_pedido', [$start, $end]);
        }], 'valor_total')->orderByDesc('pedidos_sum_valor_total')->first();

        $propostas = PropostaComercial::whereBetween('data_criacao', [$start, $end])->get();
        $totalPropostas = $propostas->count();
        $ganhas = $propostas->where('status', 'Ganha')->count();
        $taxaConversao = $totalPropostas > 0 ? ($ganhas / $totalPropostas) * 100 : 0;

        $funil = PropostaComercial::select('fase_funil', DB::raw('COUNT(*) as total'))
            ->whereBetween('data_criacao', [$start, $end])
            ->groupBy('fase_funil')->pluck('total', 'fase_funil');

        return response()->json([
            'kpis' => [
                'total_vendas' => $totalVendas,
                'top_vendedor' => $topVendedor ? $topVendedor->nome : 'N/A',
                'ticket_medio' => round($ticketMedio, 2),
                'taxa_conversao' => round($taxaConversao, 2),
            ],
            'charts' => [
                'funil_vendas' => $funil
            ]
        ]);
    }

    public function getClientes(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);

        $clientesAtivos = Cliente::where('status', 'Ativo')->count();
        $novosClientes = Cliente::whereBetween('data_cadastro', [$start, $end])->count();
        
        $cacMedio = Cliente::avg('custo_aquisicao') ?? 0;
        
        $totalClientes = Cliente::count();
        $churn = Cliente::where('status', 'Churn')->count();
        $churnRate = $totalClientes > 0 ? ($churn / $totalClientes) * 100 : 0;

        $perfilRegional = Cliente::select('regiao', DB::raw('COUNT(*) as total'))
            ->groupBy('regiao')->pluck('total', 'regiao');

        return response()->json([
            'kpis' => [
                'clientes_ativos' => $clientesAtivos,
                'novos_clientes' => $novosClientes,
                'cac_medio' => round($cacMedio, 2),
                'churn_rate' => round($churnRate, 2),
            ],
            'charts' => [
                'perfil_regional' => $perfilRegional
            ]
        ]);
    }

    public function getEstoque(Request $request)
    {
        $valorEstoque = Produto::sum(DB::raw('custo_unitario * quantidade_atual'));
        $riscoRuptura = Produto::whereColumn('quantidade_atual', '<=', 'estoque_minimo')->count();
        $prazoMedio = Produto::avg('prazo_reposicao_dias') ?? 0;

        $curvaABC = Produto::select('categoria_abc', DB::raw('COUNT(*) as total'))
            ->groupBy('categoria_abc')->pluck('total', 'categoria_abc');

        return response()->json([
            'kpis' => [
                'valor_estoque' => $valorEstoque,
                'risco_ruptura' => $riscoRuptura,
                'prazo_medio_reposicao' => round($prazoMedio, 2),
            ],
            'charts' => [
                'curva_abc' => $curvaABC
            ]
        ]);
    }
}
