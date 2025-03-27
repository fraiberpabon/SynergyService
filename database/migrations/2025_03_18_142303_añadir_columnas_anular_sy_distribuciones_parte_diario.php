<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('Sy_distribuciones_parte_diario', function (Blueprint $table) {
            $table->string('motivo_anulacion',100)->default("NULL");
            $table->dateTime('fecha_anulacion')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('fk_usuario_anulacion',10)->default("NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Sy_distribuciones_parte_diario', function (Blueprint $table) {
            $table->dropColumn('motivo_anulacion');
            $table->dropColumn('fecha_anulacion');
            $table->dropColumn('fk_usuario_anulacion');
        });
    }
};
