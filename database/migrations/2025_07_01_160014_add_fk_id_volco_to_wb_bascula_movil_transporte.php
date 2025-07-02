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
    protected $connection = 'sqlsrv2'; //conexion de la base de datos nueva.

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Wb_bascula_movil_transporte', function (Blueprint $table) {
            $table->integer('fk_id_volco')->nullable()->comment('Identificador del volco asociado al equipo desde la bascula movil');
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
            $table->dropColumn('fk_id_volco');
        });
    }
};
