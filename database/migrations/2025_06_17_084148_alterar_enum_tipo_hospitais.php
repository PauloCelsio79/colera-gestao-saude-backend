<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hospitais', function (Blueprint $table) {
            $table->enum('tipo', [
                'geral', 'municipal', 'provincial', 'centro_medico', 'clinica', 'outro'
            ])->change();
        });
    }


    public function down()
    {
        Schema::table('hospitais', function (Blueprint $table) {
            $table->enum('tipo', [
                'geral', 'municipal', 'centro_saude'
            ])->change();
        });
    }
};

