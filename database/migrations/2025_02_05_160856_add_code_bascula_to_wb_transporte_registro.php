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
            $table->string('code_bascula')->nullable()->comment('codigo o boucher del registro de bascula movil');
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
            Schema::dropIfExists('code_bascula');
        });
    }
};
