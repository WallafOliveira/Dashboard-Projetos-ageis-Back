<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Executar comandos SQL raw para tornar as colunas nullable
        DB::statement('ALTER TABLE produtos MODIFY COLUMN categoria_abc VARCHAR(1) NULL');
        DB::statement('ALTER TABLE produtos MODIFY COLUMN custo_unitario DECIMAL(10, 2) NULL');
        DB::statement('ALTER TABLE produtos MODIFY COLUMN estoque_minimo INT NULL');
        DB::statement('ALTER TABLE produtos MODIFY COLUMN prazo_reposicao_dias INT NULL');
    }

    public function down(): void
    {
        // Reverter para NOT NULL com valores padrão
        DB::statement('ALTER TABLE produtos MODIFY COLUMN categoria_abc VARCHAR(1) NOT NULL DEFAULT "A"');
        DB::statement('ALTER TABLE produtos MODIFY COLUMN custo_unitario DECIMAL(10, 2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE produtos MODIFY COLUMN estoque_minimo INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE produtos MODIFY COLUMN prazo_reposicao_dias INT NOT NULL DEFAULT 0');
    }
};
