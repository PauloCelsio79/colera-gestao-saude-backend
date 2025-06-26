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
        Schema::create('relatorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('tipo', ['casos_por_regiao', 'evolucao_temporal', 'demografico']);
            $table->json('filtros');
            $table->json('dados');
            $table->dateTime('periodo_inicio');
            $table->dateTime('periodo_fim');
            $table->string('formato')->default('pdf');
            $table->string('arquivo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relatorios');
    }
};
