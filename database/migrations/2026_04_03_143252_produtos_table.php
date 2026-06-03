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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('sku')->unique();
            $table->string('categoria')->nullable();
            $table->integer('quantidade')->default(0);
            $table->integer('estoque_minimo')->default(10);
            $table->decimal('preco', 10, 2)->default(0);
            $table->decimal('preco_unitario', 10, 2)->default(0);
            $table->text('descricao')->nullable();
            $table->string('peso')->nullable();
            $table->json('especificacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
