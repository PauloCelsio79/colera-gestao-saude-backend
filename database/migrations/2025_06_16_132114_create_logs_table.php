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
    Schema::create('logs', function (Blueprint $table) {
        $table->id();
        $table->string('usuario')->nullable(); // Nome do usuário que fez a ação
        $table->string('acao');                // Ex: 'Bloqueou usuário'
        $table->text('detalhes')->nullable();  // Detalhes adicionais
        $table->timestamp('data')->useCurrent(); // Data/hora do log
    });
}

public function down()
{
    Schema::dropIfExists('logs');
}
};
