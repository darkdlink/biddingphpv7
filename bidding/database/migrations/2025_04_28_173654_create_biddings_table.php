<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('biddings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('notice_number');
            $table->foreignId('status_id')->constrained('bidding_statuses');
            $table->string('entity');
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->dateTime('publication_date');
            $table->dateTime('opening_date');
            $table->dateTime('closing_date');
            $table->string('source_url')->nullable();
            $table->json('additional_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biddings');
    }
};
