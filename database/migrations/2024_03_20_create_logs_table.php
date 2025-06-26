<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // triagem, encaminhamento, erro, sistema
            $table->string('mensagem');
            $table->json('detalhes')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nivel')->default('info'); // info, warning, error, critical
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('rota')->nullable();
            $table->string('metodo')->nullable();
            $table->timestamps();

            // Índices para melhor performance
            $table->index('tipo');
            $table->index('nivel');
            $table->index('created_at');
            $table->index(['tipo', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('logs');
    }
}; 