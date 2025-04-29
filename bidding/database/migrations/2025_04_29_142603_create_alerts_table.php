<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->morphs('alertable'); // Polimórfico para licitações ou propostas
            $table->string('type'); // deadline, status_change, etc.
            $table->string('title');
            $table->text('message');
            $table->boolean('read')->default(false);
            $table->dateTime('trigger_date');
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alerts');
    }
};
