<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdutoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('pt_BR');
        $agora = now();
        $categorias = ['Alimento', 'Bebida', 'Eletronico', 'Higiene', 'Limpeza', 'Escritorio', 'Informatica', 'Acessorio'];
        $adjetivos = ['Premium', 'Economico', 'Profissional', 'Compacto', 'Resistente', 'Eficiente', 'Moderno', 'Essencial'];
        $descricoes = [
            'Produto indicado para uso diario com excelente custo-beneficio.',
            'Item desenvolvido para aumentar produtividade e qualidade no processo.',
            'Solucao versatil com boa durabilidade e desempenho consistente.',
            'Material de apoio operacional com facil aplicacao e manutencao simples.',
            'Produto com foco em eficiencia, seguranca e padrao de qualidade.',
        ];

        $lote = [];
        $total = 1200;

        for ($i = 1; $i <= $total; $i++) {
            $categoria = $faker->randomElement($categorias);
            $adjetivo = $faker->randomElement($adjetivos);

            $lote[] = [
                'nome' => $categoria.' '.$adjetivo.' '.$i,
                'quantidade' => $faker->numberBetween(0, 5000),
                'descricao' => $faker->randomElement($descricoes),
                'preco' => $faker->randomFloat(2, 5, 2500),
                'created_at' => $agora,
                'updated_at' => $agora,
            ];

            if (count($lote) >= 1000) {
                DB::table('produtos')->insert($lote);
                $lote = [];
            }
        }

        if (! empty($lote)) {
            DB::table('produtos')->insert($lote);
        }
    }
}
