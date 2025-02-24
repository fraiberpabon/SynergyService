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
        Schema::create('Sy_distribuciones_parte_diario', function (Blueprint $table) {
            $table->bigIncrements('id_distribuciones')->comment('clave primaria que identifica al registro');
            $table->string('fk_id_parte_diario')->comment('llave foranea de sy_parte_diario');
            $table->string('fk_id_centro_costo')->comment('llave foranea de cnf_cost_center');
            $table->dateTime('fecha_creacion_registro')->format('d/m/Y H:i:s')->comment('campo que identifica la fecha y hora del registro');
            $table->string('descripcion_trabajo', 255)->comment('campo que identifica la descripcion de la distribucion');
            $table->integer('hr_trabajo')->comment('horometro o kilometraje trabajado');
            $table->string('cant_viajes')->nullable()->comment('campo que identifica la cantidad de viajes');
            $table->string('fk_id_interrupcion')->comment('llave foranea de sy_interrupciones');
            $table->integer('estado')->comment('indicador de estado 0 representa no sincronizado 1 sincronizado 2 modificado 3 anulado');
            $table->string('hash')->comment('hash unico de registro');
            $table->string('fk_id_user_created')->comment('id del usuario que crea el registro');
            $table->string('fk_id_user_updated')->comment('id del usuario que actualiza el registro');
            $table->integer('fk_id_project_Company')->comment('id del proyecto al cual pertenece el registro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Sy_distribuciones_parte_diario');
    }
};

