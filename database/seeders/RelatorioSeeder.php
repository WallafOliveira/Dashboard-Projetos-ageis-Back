<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelatorioSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('pt_BR');
        $temas = [
            'Desempenho de Vendas',
            'Analise de Estoque',
            'Satisfacao de Clientes',
            'Indicadores Financeiros',
            'Produtividade Operacional',
            'Resumo Logistico',
        ];
        $conteudos = [
            'Este relatorio apresenta os principais indicadores do periodo, destacando evolucao de desempenho e pontos de atencao para o proximo ciclo.',
            'Foi realizada avaliacao consolidada das operacoes, com recomendacoes para melhoria de eficiencia, reducao de custos e controle de qualidade.',
            'A analise evidencia resultados relevantes para a tomada de decisao, incluindo variacoes de volume, receita e comportamento de demanda.',
            'Os dados confirmam estabilidade operacional e indicam oportunidades de ajuste em planejamento, abastecimento e acompanhamento de metas.',
            'O documento resume achados do periodo e sugere acoes praticas para aumento de produtividade e melhor aproveitamento de recursos.',
        ];

        $idsUsuarios = DB::table('usuarios')->pluck('id')->toArray();

        $lote = [];
        $total = 2000;

        for ($i = 1; $i <= $total; $i++) {
            $data = $faker->dateTimeBetween('-18 months', 'now');
            $tema = $faker->randomElement($temas);
            $paragrafo1 = $faker->randomElement($conteudos);
            $paragrafo2 = $faker->randomElement($conteudos);
            $paragrafo3 = $faker->randomElement($conteudos);
            $paragrafo4 = $faker->randomElement($conteudos);

            $lote[] = [
                'usuario_id' => (int) $idsUsuarios[array_rand($idsUsuarios)],
                'titulo' => 'Relatorio '.$i.' - '.$tema,
                'conteudo' => $paragrafo1.' '.$paragrafo2.' '.$paragrafo3.' '.$paragrafo4,
                'created_at' => $data,
                'updated_at' => $data,
            ];

            if (count($lote) >= 1000) {
                DB::table('relatorios')->insert($lote);
                $lote = [];
            }
        }

        if (! empty($lote)) {
            DB::table('relatorios')->insert($lote);
        }
    }
}
