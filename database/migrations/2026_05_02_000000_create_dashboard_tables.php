<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Clientes
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome_razao_social');
            $table->date('data_cadastro');
            $table->enum('status', ['Ativo', 'Inativo', 'Churn']);
            $table->decimal('custo_aquisicao', 10, 2)->default(0);
            $table->decimal('ltv_estimado', 10, 2)->default(0);
            $table->string('regiao');
            $table->timestamps();
        });

        // 2. Vendedores
        Schema::create('vendedores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('regiao');
            $table->decimal('meta_mensal', 10, 2);
            $table->timestamps();
        });

        // 3. Produtos (Estoque)
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('categoria_abc', ['A', 'B', 'C']);
            $table->decimal('custo_unitario', 10, 2);
            $table->integer('quantidade_atual');
            $table->integer('estoque_minimo');
            $table->integer('prazo_reposicao_dias');
            $table->timestamps();
        });

        // 4. Pedidos (Vendas)
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
            $table->timestamp('data_pedido');
            $table->timestamp('data_conclusao')->nullable();
            $table->decimal('valor_total', 10, 2);
            $table->decimal('custo_total', 10, 2);
            $table->enum('status', ['Em Andamento', 'Concluído', 'Pendente', 'Cancelado']);
            $table->timestamps();
        });

        // 5. Propostas Comerciais
        Schema::create('propostas_comerciais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
            $table->decimal('valor_estimado', 10, 2);
            $table->enum('fase_funil', ['Prospecção', 'Apresentação', 'Negociação', 'Fechamento']);
            $table->enum('status', ['Ganha', 'Perdida', 'Aberta']);
            $table->date('data_criacao');
            $table->date('data_fechamento')->nullable();
            $table->timestamps();
        });

        // 6. Financeiro Transações
        Schema::create('financeiro_transacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->onDelete('cascade');
            $table->enum('tipo', ['Receita', 'Despesa']);
            $table->string('categoria');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['Pago', 'Pendente', 'Atrasado/Inadimplente']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financeiro_transacoes');
        Schema::dropIfExists('propostas_comerciais');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('produtos');
        Schema::dropIfExists('vendedores');
        Schema::dropIfExists('clientes');
    }
};
