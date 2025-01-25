<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'sqlsrv2'; //conexion de la base de datos bdsolicitudes.

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Wb_equipos_horometros_ubicaciones', function (Blueprint $table) {
            $table->integer('fk_id_equipo_estado')->nullable()->comment('identificador relacional con la tabla de equipos estados');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_equipos_horometros_ubicaciones', function (Blueprint $table) {
            Schema::dropIfExists('fk_id_equipo_estado');
        });
    }
};
