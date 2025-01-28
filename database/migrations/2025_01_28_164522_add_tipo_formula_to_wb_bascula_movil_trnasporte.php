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
        Schema::table('Wb_bascula_movil_transporte', function (Blueprint $table) {
            $table->string('tipo_formula')->nullable()->default('M')->comment('tipo de formula registrada. asfalto = A, material = M, concreto = C');
        });

        // actualizas los registros existentes
        DB::table('Wb_bascula_movil_transporte')->update([
            'tipo_formula' => 'M',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_bascula_movil_transporte', function (Blueprint $table) {
            Schema::dropIfExists('tipo_formula');
        });
    }
};
