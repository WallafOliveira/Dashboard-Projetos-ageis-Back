<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardSeeder extends Seeder
{
    public function run(): void
    {
        $agora = now();

        $idsUsuarios = DB::table('usuarios')->pluck('id')->toArray();

        $lote = [];

        foreach ($idsUsuarios as $idUsuario) {
            $lote[] = [
                'usuario_id' => (int) $idUsuario,
                'modo_escuro' => random_int(0, 1),
                'created_at' => $agora,
                'updated_at' => $agora,
            ];

            if (count($lote) >= 1000) {
                DB::table('dashboard')->insert($lote);
                $lote = [];
            }
        }

        if (! empty($lote)) {
            DB::table('dashboard')->insert($lote);
        }
    }
}
