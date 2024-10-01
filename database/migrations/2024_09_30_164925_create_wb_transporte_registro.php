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
        Schema::create('Wb_transporte_registro', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('identificador unico del registro');
            $table->integer('tipo')->comment('1 para entradas, 2 para salidas');
            $table->string('ticket')->comment('numero de vale del transporte');
            $table->string('hash')->nullable()->comment('hash unico del registro, esto para evitar enviar dos veces el mismo registro');
            $table->string('fk_id_solicitud')->nullable()->comment('identificador de la solicitud a la cual se le realizo el transporte');
            $table->integer('fk_id_planta_origen')->nullable()->comment('planta de origen del transporte');
            $table->string('fk_id_tramo_origen')->nullable()->comment('tramo de origen del transporte');
            $table->string('fk_id_hito_origen')->nullable()->comment('hito de origen del transporte');
            $table->string('abscisa_origen')->nullable()->comment('abscisa de origen del transporte');
            $table->integer('fk_id_planta_destino')->nullable()->comment('planta de destino del transporte');
            $table->string('fk_id_tramo_destino')->nullable()->comment('tramo de destino del transporte');
            $table->string('fk_id_hito_destino')->nullable()->comment('hito de destino del transporte');
            $table->string('abscisa_destino')->nullable()->comment('abscisa de origen del transporte');
            $table->string('fk_id_cost_center')->nullable()->comment('centro de costo del transporte');
            $table->string('fk_id_material')->nullable()->comment('identificador unico del material del transporte');
            $table->string('fk_id_formula')->nullable()->comment('identificador unico de la formula de material del transporte');
            $table->string('cantidad')->nullable()->comment('cantidad de material transportado');
            $table->string('observacion')->nullable()->comment('observaciones opcionales del transporte');
            $table->string('fecha_registro')->nullable()->comment('fecha en la cual se realizo el registro en el dispotivo movil');
            $table->integer('estado')->nullable()->comment('1 activo, 0 inactivo');
            $table->string('ubicacion_gps')->nullable()->comment('coordenadas de ubicacion gps del dispositivo de registro del transporte');
            $table->string('user_created')->nullable()->comment('usuario que realiza la creacion del transporte');
            $table->string('user_updated')->nullable()->comment('usuario que realiza alteracion al registro del transporte');
            $table->integer('fk_id_project_Company')->nullable()->comment('proyecto al cual pertenece el transporte');
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
        Schema::dropIfExists('Wb_transporte_registro');
    }
};
