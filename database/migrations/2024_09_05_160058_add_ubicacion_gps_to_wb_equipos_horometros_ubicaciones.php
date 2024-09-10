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
            $table->string('ubicacion_gps', 50)->nullable()->comment('ubicacion donde se ha realizado el registro desde el dispositivo movil');
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
            $table->dropColumn('ubicacion_gps');
        });
    }
};
