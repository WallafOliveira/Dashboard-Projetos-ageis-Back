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
        DB::table('usuarios')->insert([
            [
                'nome' => 'Admin',
                'email' => 'admin@admin.com',
                'senha' => '12345678',
                'perfil_acesso_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('usuarios')->where('email', 'admin@admin.com')->delete();
    }
};
