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
            $table->integer('enviar_mensajes')->default(0)->nullable()->comment('permite enviar mensajes de texto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumn('enviar_mensajes');
    }
};
