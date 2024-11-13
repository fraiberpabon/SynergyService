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
        Schema::table('Wb_Solicitud_Materiales', function (Blueprint $table) {
            $table->integer('user_despacho')->nullable()->comment('identificador del usuario que realiza el ultimo despacho en el modulo de transporte');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_Solicitud_Materiales', function (Blueprint $table) {
            $table->integer('user_despacho');
        });
    }
};
