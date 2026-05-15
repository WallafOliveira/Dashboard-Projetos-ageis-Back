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
        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->string('usuario_nome'); // Ou chave estrangeira para usuario_id
            $table->string('acao'); // Ex: "fechou uma venda", "solicitou"
            $table->string('alvo'); // Ex: "R$ 5.400,00", "Aprovação de Desconto"
            $table->string('avatar_url')->nullable(); // Opcional, para carregar a foto do perfil
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividades');
    }
};
