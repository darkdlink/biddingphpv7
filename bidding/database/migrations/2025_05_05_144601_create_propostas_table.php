<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropostasTable extends Migration
{
    public function up()
    {
        Schema::create('propostas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('licitacao_id'); // Deve corresponder ao tipo da coluna id na tabela licitacoes
            $table->unsignedBigInteger('cliente_id');
            $table->decimal('valor_proposta', 15, 2);
            $table->text('descricao');
            $table->enum('status', ['elaboracao', 'enviada', 'aceita', 'rejeitada', 'vencedora']);
            $table->dateTime('data_envio')->nullable();
            $table->timestamps();

            // Adicionar a chave estrangeira
            $table->foreign('licitacao_id')
                  ->references('id')
                  ->on('licitacoes')
                  ->onDelete('cascade'); // Opcionalmente, você pode usar 'restrict' em vez de 'cascade'

            $table->foreign('cliente_id')
                  ->references('id')
                  ->on('clientes')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('propostas');
    }
}
