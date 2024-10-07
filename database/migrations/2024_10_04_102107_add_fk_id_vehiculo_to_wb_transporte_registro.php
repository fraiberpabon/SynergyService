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
        Schema::table('Wb_transporte_registro', function (Blueprint $table) {
            $table->integer('fk_id_equipo')->nullable()->comment('id del equipo con el cual se registro el transporte');
            $table->string('chofer')->nullable()->comment('cedula de conductor del equipo para el transporte');
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
            $table->dropColumn('fk_id_equipo');
            $table->dropColumn('chofer');
        });
    }
};
