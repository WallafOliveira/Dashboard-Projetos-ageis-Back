<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\Usuario;
use App\Models\Venda;

class DashboardService
{
    public function getDashboardData(int $dias = 7): array
    {
        return [
            'total_usuarios' => Usuario::count(),
            'total_produtos' => Produto::count(),
            'total_vendas' => Venda::count(),
            'faturamento' => round((float) Venda::sum('preco_total'), 2),
            'vendas_por_dia' => $this->getVendasPorDia($dias),
            'novos_produtos_por_dia' => $this->getNovosProdutosPorDia($dias),
            'ultimas_vendas' => $this->getUltimasVendas(),
        ];
    }

    private function getVendasPorDia(int $dias): array
    {
        return Venda::selectRaw('DATE(created_at) as dia, COUNT(*) as quantidade, SUM(preco_total) as valor')
            ->where('created_at', '>=', now()->subDays($dias))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->map(function ($item) {
                return [
                    'dia' => $item->dia,
                    'quantidade' => (int) $item->quantidade,
                    'valor' => round((float) $item->valor, 2),
                ];
            })
            ->toArray();
    }

    private function getNovosProdutosPorDia(int $dias): array
    {
        return Produto::selectRaw('DATE(created_at) as dia, COUNT(*) as quantidade')
            ->where('created_at', '>=', now()->subDays($dias))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->map(function ($item) {
                return [
                    'dia' => $item->dia,
                    'quantidade' => (int) $item->quantidade,
                ];
            })
            ->toArray();
    }

    private function getUltimasVendas(): array
    {
        return Venda::latest()
            ->take(5)
            ->get()
            ->map(function ($venda) {
                return [
                    'id' => $venda->id,
                    'total' => (float) $venda->preco_total,
                    'created_at' => $venda->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }
}
