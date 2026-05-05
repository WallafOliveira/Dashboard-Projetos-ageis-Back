<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('pt_BR');
        $agora = now();

        $perfis = DB::table('perfil_acesso')->pluck('id')->toArray();

        DB::table('usuarios')->insert([
            [
                'nome' => 'Admin',
                'email' => 'admin@admin.com',
                'senha' => '12345678',
                'perfil_acesso_id' => $perfis[0] ?? 1,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'nome' => 'Bruno Albertoni',
                'email' => 'brunoalbertoni06@gmail.com',
                'senha' => '12345678',
                'perfil_acesso_id' => $perfis[0] ?? 1,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]
        ]);

        $lote = [];
        $total = 300;

        for ($i = 1; $i <= $total; $i++) {
            $lote[] = [
                'nome' => $faker->name(),
                'email' => 'usuario'.$i.'@fpa.local',
                'senha' => '12345678',
                'perfil_acesso_id' => $faker->randomElement($perfis),
                'created_at' => $agora,
                'updated_at' => $agora,
            ];

            if (count($lote) >= 1000) {
                DB::table('usuarios')->insert($lote);
                $lote = [];
            }
        }

        if (! empty($lote)) {
            DB::table('usuarios')->insert($lote);
        }
    }
}
