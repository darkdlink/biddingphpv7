<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('biddings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('entity_id')->constrained('entities');
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->string('notice_link')->nullable();
            $table->string('status');
            $table->dateTime('publication_date')->nullable();
            $table->dateTime('opening_date')->nullable();
            $table->dateTime('closing_date')->nullable();
            $table->json('requirements')->nullable();
            $table->json('metadata')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('biddings');
    }
};
