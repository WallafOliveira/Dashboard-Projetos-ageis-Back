<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('pt_BR');
        $comentarios = [
            'Produto entregue no prazo e em perfeitas condicoes.',
            'Atendeu as expectativas e teve bom desempenho no uso diario.',
            'Qualidade satisfatoria, com pequeno ajuste necessario na embalagem.',
            'Experiencia positiva com atendimento rapido e eficiente.',
            'Material dentro do esperado, recomendo para reposicao de estoque.',
            'Produto funcional, com boa relacao entre preco e qualidade.',
            'Entrega ocorreu sem problemas e o item chegou conforme descrito.',
            'Uso simples e resultado consistente nas operacoes da equipe.',
        ];

        $idsUsuarios = DB::table('usuarios')->pluck('id')->toArray();
        $idsProdutos = DB::table('produtos')->pluck('id')->toArray();

        $lote = [];
        $total = 6000;

        for ($i = 1; $i <= $total; $i++) {
            $data = $faker->dateTimeBetween('-18 months', 'now');

            $lote[] = [
                'usuario_id' => (int) $idsUsuarios[array_rand($idsUsuarios)],
                'produto_id' => (int) $idsProdutos[array_rand($idsProdutos)],
                'comentario' => $faker->randomElement($comentarios),
                'avaliacao' => $faker->numberBetween(1, 5),
                'created_at' => $data,
                'updated_at' => $data,
            ];

            if (count($lote) >= 1000) {
                DB::table('fedback')->insert($lote);
                $lote = [];
            }
        }

        if (! empty($lote)) {
            DB::table('fedback')->insert($lote);
        }
    }
}
