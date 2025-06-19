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
        Schema::table('Wb_configuraciones', function (Blueprint $table) {
            $table->boolean(column: 'permitir_peso_manual')->default(0)->comment('Condicional para permitir el registro de peso manual en las basculas moviles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_configuraciones', function (Blueprint $table) {
           $table->dropColumn('permitir_peso_manual');
        });
    }
};
