<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\FinanceiroTransacao;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\PropostaComercial;
use App\Models\Vendedor;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // 1. Produtos
        $produtos = [];
        for ($i = 0; $i < 20; $i++) {
            $produtos[] = Produto::create([
                'nome' => $faker->words(3, true),
                'categoria_abc' => $faker->randomElement(['A', 'B', 'B', 'C', 'C', 'C']),
                'custo_unitario' => $faker->randomFloat(2, 10, 500),
                'quantidade_atual' => $faker->numberBetween(0, 1000),
                'estoque_minimo' => $faker->numberBetween(10, 100),
                'prazo_reposicao_dias' => $faker->numberBetween(3, 30),
            ]);
        }

        // 2. Vendedores
        $vendedores = [];
        $regioes = ['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul'];
        for ($i = 0; $i < 5; $i++) {
            $vendedores[] = Vendedor::create([
                'nome' => $faker->name(),
                'regiao' => $faker->randomElement($regioes),
                'meta_mensal' => $faker->randomFloat(2, 50000, 150000),
            ]);
        }

        // 3. Clientes
        $clientes = [];
        $statusCliente = ['Ativo', 'Ativo', 'Ativo', 'Inativo', 'Churn'];
        for ($i = 0; $i < 50; $i++) {
            $clientes[] = Cliente::create([
                'nome_razao_social' => $faker->company(),
                'data_cadastro' => $faker->dateTimeBetween('-2 years', 'now'),
                'status' => $faker->randomElement($statusCliente),
                'custo_aquisicao' => $faker->randomFloat(2, 100, 2000),
                'ltv_estimado' => $faker->randomFloat(2, 5000, 50000),
                'regiao' => $faker->randomElement($regioes),
            ]);
        }

        // 4. Pedidos & Financeiro Transacoes
        $statusPedido = ['Em Andamento', 'Concluído', 'Concluído', 'Pendente', 'Cancelado'];
        for ($i = 0; $i < 200; $i++) {
            $dataPedido = $faker->dateTimeBetween('-1 year', 'now');
            $status = $faker->randomElement($statusPedido);
            $dataConclusao = $status === 'Concluído' ? (clone $dataPedido)->modify('+' . rand(1, 15) . ' days') : null;

            $valorTotal = $faker->randomFloat(2, 500, 15000);
            $custoTotal = $valorTotal * $faker->randomFloat(2, 0.4, 0.8); // Margem de lucro de 20 a 60%

            $pedido = Pedido::create([
                'cliente_id' => $faker->randomElement($clientes)->id,
                'vendedor_id' => $faker->randomElement($vendedores)->id,
                'data_pedido' => $dataPedido,
                'data_conclusao' => $dataConclusao,
                'valor_total' => $valorTotal,
                'custo_total' => $custoTotal,
                'status' => $status,
            ]);

            // Transacao de Receita vinculada ao pedido
            if ($status === 'Concluído' || $status === 'Em Andamento' || $status === 'Pendente') {
                $statusTransacao = $status === 'Concluído' ? 'Pago' : $faker->randomElement(['Pendente', 'Atrasado/Inadimplente']);
                $dataVencimento = (clone $dataPedido)->modify('+30 days');
                $dataPagamento = $statusTransacao === 'Pago' ? (clone $dataVencimento)->modify('-' . rand(0, 5) . ' days') : null;

                FinanceiroTransacao::create([
                    'pedido_id' => $pedido->id,
                    'tipo' => 'Receita',
                    'categoria' => 'Vendas',
                    'valor' => $valorTotal,
                    'data_vencimento' => $dataVencimento,
                    'data_pagamento' => $dataPagamento,
                    'status' => $statusTransacao,
                ]);
            }
        }

        // 5. Propostas Comerciais
        $fases = ['Prospecção', 'Apresentação', 'Negociação', 'Fechamento'];
        for ($i = 0; $i < 100; $i++) {
            $dataCriacao = $faker->dateTimeBetween('-6 months', 'now');
            $statusProposta = $faker->randomElement(['Ganha', 'Perdida', 'Aberta', 'Aberta']);
            $fase = $statusProposta === 'Aberta' ? $faker->randomElement($fases) : 'Fechamento';
            // clone dataCriacao to avoid modifying it
            $dataFechamento = null;
            if ($statusProposta !== 'Aberta') {
                $cloneDataCriacao = clone $dataCriacao;
                $dataFechamento = $cloneDataCriacao->modify('+' . rand(5, 60) . ' days');
            }

            PropostaComercial::create([
                'cliente_id' => $faker->randomElement($clientes)->id,
                'vendedor_id' => $faker->randomElement($vendedores)->id,
                'valor_estimado' => $faker->randomFloat(2, 1000, 50000),
                'fase_funil' => $fase,
                'status' => $statusProposta,
                'data_criacao' => $dataCriacao,
                'data_fechamento' => $dataFechamento,
            ]);
        }

        // 6. Transacoes de Despesas Independentes
        for ($i = 0; $i < 50; $i++) {
            $dataVencimento = $faker->dateTimeBetween('-6 months', '+1 month');
            $statusTransacao = $dataVencimento < new \DateTime() ? $faker->randomElement(['Pago', 'Atrasado/Inadimplente']) : 'Pendente';
            
            $dataPagamento = null;
            if ($statusTransacao === 'Pago') {
                $cloneDataVencimento = clone $dataVencimento;
                $dataPagamento = $cloneDataVencimento->modify('-' . rand(0, 3) . ' days');
            }

            FinanceiroTransacao::create([
                'pedido_id' => null,
                'tipo' => 'Despesa',
                'categoria' => $faker->randomElement(['Fornecedores', 'Impostos', 'Salários', 'Marketing', 'Aluguel']),
                'valor' => $faker->randomFloat(2, 500, 10000),
                'data_vencimento' => $dataVencimento,
                'data_pagamento' => $dataPagamento,
                'status' => $statusTransacao,
            ]);
        }
    }
}
