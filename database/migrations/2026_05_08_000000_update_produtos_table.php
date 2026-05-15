<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Adicionar novos campos
            if (!Schema::hasColumn('produtos', 'sku')) {
                $table->string('sku')->unique()->after('id');
            }
            
            if (!Schema::hasColumn('produtos', 'categoria')) {
                $table->string('categoria')->nullable()->after('nome');
            }
            
            if (!Schema::hasColumn('produtos', 'preco_unitario')) {
                $table->decimal('preco_unitario', 10, 2)->nullable()->after('quantidade_atual');
            }
            
            if (!Schema::hasColumn('produtos', 'status')) {
                $table->enum('status', ['OK', 'Baixo', 'Crítico'])->default('OK')->after('preco_unitario');
            }
        });
        
        // Renomear quantidade_atual para quantidade em transação separada
        if (Schema::hasColumn('produtos', 'quantidade_atual') && !Schema::hasColumn('produtos', 'quantidade')) {
            Schema::table('produtos', function (Blueprint $table) {
                $table->renameColumn('quantidade_atual', 'quantidade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            if (Schema::hasColumn('produtos', 'sku')) {
                $table->dropUnique(['sku']);
                $table->dropColumn('sku');
            }
            
            if (Schema::hasColumn('produtos', 'categoria')) {
                $table->dropColumn('categoria');
            }
            
            if (Schema::hasColumn('produtos', 'preco_unitario')) {
                $table->dropColumn('preco_unitario');
            }
            
            if (Schema::hasColumn('produtos', 'status')) {
                $table->dropColumn('status');
            }
        });
        
        if (Schema::hasColumn('produtos', 'quantidade') && !Schema::hasColumn('produtos', 'quantidade_atual')) {
            Schema::table('produtos', function (Blueprint $table) {
                $table->renameColumn('quantidade', 'quantidade_atual');
            });
        }
    }
};
