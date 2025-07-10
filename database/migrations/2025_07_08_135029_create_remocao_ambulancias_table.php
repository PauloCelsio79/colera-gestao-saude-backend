<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('remocao_ambulancias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ambulancia_id');
            $table->unsignedBigInteger('solicitante_id');
            $table->enum('status', ['pendente', 'aprovada', 'recusada'])->default('pendente');
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->foreign('ambulancia_id')->references('id')->on('ambulancias')->onDelete('cascade');
            $table->foreign('solicitante_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remocao_ambulancias');
    }
};
