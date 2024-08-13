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
        //esquema de la tabla Sy_usuarios
        Schema::create('Sy_usuarios', function (Blueprint $table) {
            $table->bigIncrements('id_sy_usuarios');
            $table->integer('fk_wb_id_usuarios');
            $table->string('imei');
            $table->string('version');
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
        //rollback de la tabla Sy_usuarios
        Schema::dropIfExists('Sy_usuarios');
    }
};
