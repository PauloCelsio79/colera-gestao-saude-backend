<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcao_municipais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gabinete_provincial_id')->constrained('gabinete_provinciais');
            $table->string('nome');
            $table->string('municipio')->unique();
            $table->string('endereco');
            $table->string('telefone');
            $table->string('email')->unique();
            $table->string('diretor');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Adiciona a coluna direcao_municipal_id na tabela hospitais
        Schema::table('hospitais', function (Blueprint $table) {
            $table->foreignId('direcao_municipal_id')->nullable()->constrained('direcao_municipais');
        });
    }

    public function down(): void
    {
        Schema::table('hospitais', function (Blueprint $table) {
            $table->dropForeignId('direcao_municipal_id');
        });
        Schema::dropIfExists('direcao_municipais');
    }
}; 