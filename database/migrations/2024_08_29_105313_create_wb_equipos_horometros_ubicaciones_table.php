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
    public function up()
    {
        Schema::create('Wb_equipos_horometros_ubicaciones', function (Blueprint $table) {
            $table->bigIncrements('id_equipos_horometros_ubicaciones')->comment('clave primaria que identifica al registro');
            $table->integer('fk_id_equipo')->comment('id que sirve como referecia al equipo seleccionado por el usuario');
            $table->string('fk_id_tramo', 10)->comment('id que sirve como referecia al tramo seleccionado por el usuario');
            $table->string('fk_id_hito', 20)->comment('id que sirve como referecia al hito seleccionado por el usuario');
            $table->string('horometro', 50)->nullable()->comment('contiene el valor del horometro ingresado por el usuario');
            $table->string('horometro_foto', 'MAX')->nullable()->comment('imagen jpeg en base64 del horometro');
            $table->string('observaciones', 300)->nullable()->comment('notas u observaciones ingresadas por el usuario');
            $table->string('fecha_registro')->comment('fecha en el cual se realizo el registro en el dispositivo movil');
            $table->integer('estado')->comment('indicador de activo como 1 e inactivo como 0');
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
        Schema::dropIfExists('Wb_equipos_horometros_ubicaciones');
    }
};
