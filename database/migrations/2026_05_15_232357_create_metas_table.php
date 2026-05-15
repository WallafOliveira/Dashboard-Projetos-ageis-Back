<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo'); // Ex: Atingir R$ 500k de Faturamento
            $table->string('categoria'); // Financeiro, Comercial, Estoque
            $table->string('valor_alvo'); // Ex: R$ 500.000 ou 100 Clientes
            $table->string('valor_atual')->default('0'); // Valor atual atingido
            $table->enum('status', ['on-track', 'at-risk', 'completed'])->default('on-track');
            $table->date('data_limite'); // Prazo final
            $table->integer('progresso')->default(0); // Porcentagem (0-100)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
