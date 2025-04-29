<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidding_id')->constrained('biddings');
            $table->string('status'); // Em elaboração, Enviada, Vencedora, Perdedora
            $table->decimal('proposed_value', 15, 2);
            $table->decimal('cost_estimate', 15, 2)->nullable();
            $table->decimal('profit_margin', 8, 4)->nullable(); // Percentual
            $table->json('items')->nullable(); // Itens detalhados da proposta
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable(); // Caminho para o arquivo da proposta
            $table->dateTime('submission_date')->nullable();
            $table->string('submission_protocol')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('proposals');
    }
};
