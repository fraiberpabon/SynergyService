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
        Schema::create('Sy_Parte_diario', function (Blueprint $table) {
            $table->bigIncrements('id_parte_diario')->comment('clave primaria que identifica al registro');
            $table->string('fecha_registro')->comment('campo que identifica la fecha del registro');
            $table->string('fecha_creacion_registro')->comment('campo que identifica la fecha y hora del registro');
            $table->string('fk_equiment_id')->comment('llave foranea de wb_equipos');
            $table->string('observacion', 255)->comment('campo que indentifica la observacion del parte diario');
            $table->string('fk_id_seguridad_sitio_turno')->comment('llave forarena de wb_seguridad_sitio_turno');
            $table->string('horometro_inicial')->nullable()->comment('campo que identifica el horometro inicial');
            $table->string('horometro_final')->nullable()->comment('campo que identifica el horometro final');
            $table->string('fk_matricula_operador')->nullable()->comment('llave foranea de wb_conductores');
            $table->string('hash')->comment('hash unico de registro');
            $table->integer('estado')->comment('indicador de estado 0 representa no sincronizado 1 sincronizado 2 modificado 3 anulado');
            $table->string('fk_id_user_created')->comment('id del usuario que crea el registro');
            $table->string('fk_id_user_updated')->nullable()->comment('id del usuario que actualiza el registro');
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
        Schema::dropIfExists('Sy_Parte_diario');
    }
};
