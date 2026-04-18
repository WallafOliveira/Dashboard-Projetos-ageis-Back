<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('pt_BR');

        $idsUsuarios = DB::table('usuarios')->pluck('id')->toArray();
        $produtos = DB::table('produtos')->select('id', 'preco')->get()->toArray();

        $lote = [];
        $total = 10000;

        for ($i = 1; $i <= $total; $i++) {
            $produto = $produtos[array_rand($produtos)];
            $quantidade = $faker->numberBetween(1, 20);
            $fatorVariacao = $faker->randomFloat(4, 0.85, 1.35);
            $precoTotal = round(((float) $produto->preco * $quantidade) * $fatorVariacao, 2);
            $data = $faker->dateTimeBetween('-18 months', 'now');

            $lote[] = [
                'produto_id' => (int) $produto->id,
                'usuario_id' => (int) $idsUsuarios[array_rand($idsUsuarios)],
                'quantidade' => $quantidade,
                'preco_total' => $precoTotal,
                'created_at' => $data,
                'updated_at' => $data,
            ];

            if (count($lote) >= 1000) {
                DB::table('vendas')->insert($lote);
                $lote = [];
            }
        }

        if (! empty($lote)) {
            DB::table('vendas')->insert($lote);
        }
    }
}
