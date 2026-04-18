<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PopularBancoSeeder extends Seeder
{
    public function run(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        DB::table('fedback')->delete();
        DB::table('vendas')->delete();
        DB::table('relatorios')->delete();
        DB::table('dashboard')->delete();
        DB::table('usuarios')->delete();
        DB::table('produtos')->delete();
        DB::table('perfil_acesso')->delete();

        if ($driver === 'sqlite') {
            DB::statement('DELETE FROM sqlite_sequence WHERE name IN ("fedback", "vendas", "relatorios", "dashboard", "usuarios", "produtos", "perfil_acesso")');
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->call([
            PerfilAcessoSeeder::class,
            UsuarioSeeder::class,
            ProdutoSeeder::class,
            VendaSeeder::class,
            FeedbackSeeder::class,
            RelatorioSeeder::class,
            DashboardSeeder::class,
        ]);
    }
}
