<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    protected $connection = 'sqlsrv3';
    public function up()
    {
        Schema::create('Sy_turno_equipos', function (Blueprint $table) {
            $table->bigIncrements('id_turnos')->comment('clave primaria que identifica al registro');
            $table->string('nombre_turno',50)->nullable()->comment('Identificador del turno');
            $table->integer('horas_turno')->nullable()->comment('Horas de duracion del turno');
            $table->integer('fk_id_project_Company')->nullable()->comment('llave foranea de el id del proyecto');
            $table->integer('fk_compañia')->nullable()->comment('llave foranea de la compañia');
            $table->string('hora_inicio_turno',5)->nullable()->comment('Identificador del turno');
            $table->string('hora_final_turno',5)->nullable()->comment('Identificador del turno');
            $table->tinyInteger('estado')->comment('estado');
            $table->integer('usuario_creacion')->comment('usuario que crea el registro');
            $table->integer('usuario_actualizacion')->nullable()->comment('usuario que modifica el registro');
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
        Schema::dropIfExists('Sy_turno_equipos');
    }
};
