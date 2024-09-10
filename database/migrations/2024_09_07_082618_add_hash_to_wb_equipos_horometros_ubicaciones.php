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
            $table->integer('user_created')->nullable()->comment('id del usuario que crea el registro');
            $table->integer('user_updated')->nullable()->comment('id del usuario que actualiza el registro');
            $table->string('hash', 250)->nullable()->comment('hash unico del registro para identificar duplicidad con otros registros');
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
            $table->dropColumn('user_created');
            $table->dropColumn('user_updated');
            $table->dropColumn('hash');
        });
    }
};
