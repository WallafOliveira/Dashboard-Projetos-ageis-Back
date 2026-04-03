<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('perfil_acesso')->insert([
            ['nome' => 'Administrador', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Usuário', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('perfil_acesso')->whereIn('nome', ['Administrador', 'Usuário'])->delete();
    }
};
