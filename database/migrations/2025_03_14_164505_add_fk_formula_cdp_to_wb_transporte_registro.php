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
        Schema::table('Wb_transporte_registro', function (Blueprint $table) {
            $table->string('fk_formula_cdp')->nullable()->comment('Idenficador unico de la formula centro de produccion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_transporte_registro', function (Blueprint $table) {
            Schema::dropIfExists('fk_formula_cdp');
        });
    }
};
