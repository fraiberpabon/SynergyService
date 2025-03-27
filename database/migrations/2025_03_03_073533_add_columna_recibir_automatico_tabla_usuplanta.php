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
      Schema::table('usuplanta', function (Blueprint $table) {
          $table->integer('recibirAutomatico')->default(0)->comment('indica si se debe recibir automaticamente un viaje, 1 para generar la recepcion, 0 para no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('usuplanta', function (Blueprint $table) {
          Schema::dropIfExists('recibirAutomatico');
      });
    }
};
