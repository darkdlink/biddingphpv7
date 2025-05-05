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
            $table->unsignedBigInteger('licitacao_id');
            $table->unsignedBigInteger('cliente_id');
            $table->decimal('valor_proposta', 15, 2);
            $table->text('descricao');
            $table->enum('status', ['elaboracao', 'enviada', 'aceita', 'rejeitada', 'vencedora']);
            $table->dateTime('data_envio')->nullable();
            $table->timestamps();

            $table->foreign('licitacao_id')->references('id')->on('licitacoes');
            $table->foreign('cliente_id')->references('id')->on('clientes');
        });
    }

    public function down()
    {
        Schema::dropIfExists('propostas');
    }
}
