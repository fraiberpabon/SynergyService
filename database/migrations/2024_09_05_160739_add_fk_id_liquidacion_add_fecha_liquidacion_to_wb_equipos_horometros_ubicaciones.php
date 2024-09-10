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
        Schema::table('Wb_equipos_horometros_ubicaciones', function (Blueprint $table) {
            $table->integer('fk_id_liquidacion')->nullable()->comment('contiene el id que lo relaciona con su liquidacion');
            $table->dateTime('fecha_liquidacion')->nullable()->comment('fecha en la cual el registro fue liquidado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_equipos_horometros_ubicaciones', function (Blueprint $table) {
            $table->dropColumn('fk_id_liquidacion');
            $table->dropColumn('fecha_liquidacion');
        });
    }
};
