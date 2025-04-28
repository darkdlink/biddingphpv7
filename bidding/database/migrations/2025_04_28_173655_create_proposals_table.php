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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidding_id')->constrained();
            $table->foreignId('company_id')->constrained();
            $table->decimal('value', 15, 2);
            $table->text('description')->nullable();
            $table->dateTime('submission_date');
            $table->enum('status', ['draft', 'submitted', 'won', 'lost', 'cancelled']);
            $table->decimal('profit_margin', 8, 2)->nullable();
            $table->json('cost_breakdown')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
