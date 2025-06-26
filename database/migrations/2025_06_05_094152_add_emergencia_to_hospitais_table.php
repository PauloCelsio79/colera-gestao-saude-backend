<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hospitais', function (Blueprint $table) {
            $table->boolean('ponto_emergencia')->default(false)->after('leitos_disponiveis');
            $table->text('servicos_emergencia')->nullable()->after('ponto_emergencia');
            $table->integer('capacidade_emergencia')->default(0)->after('servicos_emergencia');
        });
    }

    public function down()
    {
        Schema::table('hospitais', function (Blueprint $table) {
            $table->dropColumn([
                'ponto_emergencia',
                'servicos_emergencia',
                'capacidade_emergencia'
            ]);
        });
    }
}; 