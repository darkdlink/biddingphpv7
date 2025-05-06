<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cnpj', 14)->unique();
            $table->string('email');
            $table->string('telefone', 20);
            $table->string('endereco');
            $table->string('cidade', 100);
            $table->string('uf', 2);
            $table->string('cep', 8);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes');
    }
}
