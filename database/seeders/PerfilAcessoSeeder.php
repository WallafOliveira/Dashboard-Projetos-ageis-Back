<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerfilAcessoSeeder extends Seeder
{
    public function run(): void
    {
        $agora = now();

        DB::table('perfil_acesso')->insert([
            ['nome' => 'Administrador', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Gerente', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Analista', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Operador', 'created_at' => $agora, 'updated_at' => $agora],
        ]);
    }
}
