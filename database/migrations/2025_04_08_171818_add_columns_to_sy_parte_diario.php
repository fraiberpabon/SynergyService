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
    protected $connection = 'sqlsrv3';
    public function up()
    {
        Schema::table('Sy_Parte_diario', function (Blueprint $table) {
            $table->string('kilometraje_inicial')->nullable()->after('fk_id_seguridad_sitio_turno')->comment('Indica el kilometraje inicial');
            $table->string('kilometraje_final')->nullable()->after('kilometraje_inicial')->comment('Indica el kilometraje final');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Sy_Parte_diario', function (Blueprint $table) {
            $table->dropColumn('kilometraje_inicial');
            $table->dropColumn('kilometraje_final');
        });
    }
};
