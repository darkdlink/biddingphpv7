<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicitacoesTable extends Migration
{
    public function up()
    {
        Schema::create('licitacoes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_controle_pncp')->unique();
            $table->string('orgao_entidade');
            $table->string('unidade_orgao')->nullable();
            $table->integer('ano_compra');
            $table->integer('sequencial_compra');
            $table->string('numero_compra');
            $table->text('objeto_compra');
            $table->string('modalidade_nome');
            $table->string('modo_disputa_nome')->nullable();
            $table->decimal('valor_total_estimado', 15, 2)->nullable();
            $table->string('situacao_compra_nome');
            $table->dateTime('data_inclusao')->nullable();
            $table->dateTime('data_publicacao_pncp')->nullable();
            $table->dateTime('data_abertura_proposta')->nullable();
            $table->dateTime('data_encerramento_proposta')->nullable();
            $table->string('link_sistema_origem')->nullable();
            $table->boolean('is_srp')->default(false);
            $table->string('uf');
            $table->string('municipio')->nullable();
            $table->string('cnpj');
            $table->boolean('analisada')->default(false);
            $table->boolean('interesse')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('licitacoes');
    }
}
