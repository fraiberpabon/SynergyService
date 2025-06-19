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
    protected $connection = 'sqlsrv2';
    public function up()
    {
        Schema::table('Wb_configuraciones', function (Blueprint $table) {
            $table->integer('max_km')->default('600')->after('enviar_mensajes')->comment('Configuracion para el maximo kilometraje permitido parte diario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_configuraciones', function (Blueprint $table) {
            $table->dropColumn('max_km');
        });
    }
};
