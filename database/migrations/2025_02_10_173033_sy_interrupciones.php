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
        Schema::create('Sy_interrupciones', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('clave primaria que identifica al registro');
            $table->string('nombre_interrupcion')->comment('nombre de la interrupcion');
            $table->string('descripcion_interrupcion',255)->nullable()->comment('descripcion de la interrupcion');
            $table->integer('estado')->comment('estado de la interrupcion');
            $table->string('fk_id_centro_de_costos')->comment('llave foranea de CNFCOSCENTER');
            $table->integer('es_obligatorio')->comment('identificador que indica si el campo descripcion es obligatorio o no');
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
        Schema::dropIfExists('Sy_interrupciones');
    }
};

