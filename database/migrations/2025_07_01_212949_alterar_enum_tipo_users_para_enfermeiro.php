<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN tipo ENUM('admin', 'enfermeiro', 'gestor', 'tecnico')");
    }

    public function down()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN tipo ENUM('admin', 'medico', 'gestor', 'tecnico')");
    }
};
