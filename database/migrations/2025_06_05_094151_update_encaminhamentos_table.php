<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Primeiro remove a coluna status existente
        Schema::table('encaminhamentos', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Depois adiciona as novas colunas
        Schema::table('encaminhamentos', function (Blueprint $table) {
            // Adiciona a referência para ambulância
            $table->foreignId('ambulancia_id')->nullable()->after('hospital_id')->constrained('ambulancias')->onDelete('set null');
            
            // Adiciona o novo enum de status
            $table->enum('status', ['pendente', 'em_deslocamento', 'concluido', 'cancelado'])->default('pendente')->after('ambulancia_id');
            
            // Adiciona campo para motivo de cancelamento
            $table->string('motivo_cancelamento')->nullable()->after('status');
        });
    }

    public function down()
    {
        // Primeiro remove as novas colunas
        Schema::table('encaminhamentos', function (Blueprint $table) {
            $table->dropForeign(['ambulancia_id']);
            $table->dropColumn(['ambulancia_id', 'motivo_cancelamento']);
            $table->dropColumn('status');
        });

        // Depois restaura o status original
        Schema::table('encaminhamentos', function (Blueprint $table) {
            $table->enum('status', ['pendente', 'em_andamento', 'concluido', 'cancelado'])->default('pendente');
        });
    }
}; 