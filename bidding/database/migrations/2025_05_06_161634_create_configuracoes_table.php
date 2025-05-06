<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreateConfiguracoesTable extends Migration
{
    public function up()
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        // Inserir configurações padrão
        $defaults = [
            'api_url' => 'https://pncp.gov.br/api/consulta/v1',
            'api_timeout' => 60,
            'itens_por_pagina' => 20,
            'email_driver' => 'smtp',
            'email_host' => '',
            'email_port' => 587,
            'email_encryption' => 'tls',
            'email_username' => '',
            'email_password' => '',
            'email_from_address' => 'noreply@bidding.com',
            'email_from_name' => 'Sistema Bidding',
            'notificar_novas_licitacoes' => 0,
            'notificar_encerramento' => 0,
            'dias_antecedencia' => 3,
            'emails_notificacao' => '',
            'nome_empresa' => 'Minha Empresa',
            'cnpj_empresa' => '',
            'intervalo_sincronizacao' => 24,
            'tema' => 'light'
        ];

        foreach ($defaults as $chave => $valor) {
            DB::table('configuracoes')->insert([
                'chave' => $chave,
                'valor' => $valor,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('configuracoes');
    }
}
