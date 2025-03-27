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
    protected $connection = 'sqlsrv3'; //conexion de la base de datos nueva.

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Wb_bascula_movil_transporte', function (Blueprint $table) {
            $table->string('equipo_externo')->nullable()->comment('Placa del equipo ingresado a mano por el usuario');
            $table->string('material_externo')->nullable()->comment('Material escrito a mano por el usuario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_bascula_movil_transporte', function (Blueprint $table) {
            Schema::dropIfExists('equipo_externo');
            Schema::dropIfExists('material_externo');
        });
    }
};
