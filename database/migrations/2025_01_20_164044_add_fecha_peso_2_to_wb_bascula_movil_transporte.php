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
        Schema::table('Wb_bascula_movil_transporte', function (Blueprint $table) {
            $table->string('fecha_registro_peso2')->nullable()->comment('fecha en la cual se hizo el registro del segundo peso y calculo del peso neto');
            $table->string('codigo_transporte')->nullable()->comment('codigo del registro de transporte asociado al registro de bascula movil');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_bascula_movil_transporte', function (Blueprint $table) {
            $table->dropColumn('fecha_registro_peso2');
            $table->dropColumn('codigo_transporte');
        });
    }
};
