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
        Schema::table('SolicitudConcreto', function (Blueprint $table) {
            $table->string('fecha_cierre')->nullable()->comment('fecha proveniente de synergy para cuando se complete el despacho de la solicitud');
            $table->integer('user_despacho')->nullable()->comment('id de usuario que completa el despacho de la solicitud');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('SolicitudConcreto', function (Blueprint $table) {
            Schema::dropIfExists('fecha_cierre');
            Schema::dropIfExists('user_despacho');
        });
    }
};
