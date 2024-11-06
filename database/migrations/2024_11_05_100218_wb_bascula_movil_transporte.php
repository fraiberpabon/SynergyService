<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
        Schema::create('Wb_bascula_movil_transporte', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('identificador unico del registro');
            $table->string('boucher')->comment('numero de vale del transporte');
            $table->integer('es_externo')->comment('1 si es externo, 0 sino es externo');
            $table->integer('tipo')->comment('1 para entradas, 2 para salidas');
            $table->string('fk_id_material')->nullable()->comment('identificador unico del material del transporte');
            $table->string('fk_id_formula')->nullable()->comment('identificador unico de la formula de material del transporte');
            $table->integer('fk_id_planta_origen')->nullable()->comment('planta de origen del transporte');
            $table->integer('fk_id_tramo_origen')->nullable()->comment('tramo de origen del transporte');
            $table->integer('fk_id_hito_origen')->nullable()->comment('hito de origen del transporte');
            $table->string('otro_origen')->nullable()->comment('ubicacion escrita por el usuario del origen del transporte');
            $table->string('fk_id_cost_center_origen')->nullable()->comment('centro de costo del transporte');
            $table->integer('fk_id_planta_destino')->nullable()->comment('planta de destino del transporte');
            $table->integer('fk_id_tramo_destino')->nullable()->comment('tramo de destino del transporte');
            $table->integer('fk_id_hito_destino')->nullable()->comment('hito de destino del transporte');
            $table->string('otro_destino')->nullable()->comment('ubicacion escrita por el usuario del destino del transporte');
            $table->string('fk_id_cost_center_destino')->nullable()->comment('centro de costo del transporte');
            $table->integer('fk_id_equipo')->nullable()->comment('identificador del equipo que realiza el transporte');
            $table->string('conductor')->nullable()->comment('cedula de conductor del equipo para el transporte');
            $table->string('peso1')->nullable()->comment('peso 1 tomado por el usuario en bascula');
            $table->string('peso2')->nullable()->comment('peso 2 tomado por el usuario en bascula');
            $table->string('peso_neto')->nullable()->comment('peso resultante de la resta de peso1 y peso2');
            $table->string('observacion')->nullable()->comment('observaciones opcionales del transporte');
            $table->string('user_created')->nullable()->comment('usuario que realiza la creacion del transporte');
            $table->string('ubicacion_gps')->nullable()->comment('coordenadas de ubicacion gps del dispositivo de registro del transporte');
            $table->string('fecha_registro')->nullable()->comment('fecha en la cual se realizo el registro en el dispotivo movil');
            $table->string('user_updated')->nullable()->comment('usuario que realiza alteracion al registro del transporte');
            $table->integer('fk_id_project_Company')->comment('proyecto al cual pertenece el transporte');
            $table->string('hash')->nullable()->comment('hash unico del registro, esto para evitar enviar dos veces el mismo registro');
            $table->integer('estado')->nullable()->comment('1 activo, 0 inactivo');
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
        Schema::dropIfExists('Wb_bascula_movil_transporte');
    }
};
