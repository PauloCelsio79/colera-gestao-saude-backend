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
        Schema::create('hospitais', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('tipo', ['geral', 'municipal', 'centro_saude']);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('leitos_totais');
            $table->integer('leitos_disponiveis');
            $table->string('telefone');
            $table->text('endereco');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitais');
    }
};
