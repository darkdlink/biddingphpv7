<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixLicitacoesTable extends Migration
{
    public function up()
    {
        // Verificar se a tabela existe e tem todas as colunas necessárias
        if (Schema::hasTable('licitacoes')) {
            Schema::table('licitacoes', function (Blueprint $table) {
                // Adicionar colunas que podem estar faltando
                if (!Schema::hasColumn('licitacoes', 'interesse')) {
                    $table->boolean('interesse')->default(false);
                }

                if (!Schema::hasColumn('licitacoes', 'analisada')) {
                    $table->boolean('analisada')->default(false);
                }

                // Ajustar tipos de dados, se necessário
                $table->string('numero_controle_pncp')->change();
                $table->text('objeto_compra')->change();
                $table->decimal('valor_total_estimado', 15, 2)->nullable()->change();
            });
        }
    }

    public function down()
    {
        // Não fazer nada no método down
    }
}
