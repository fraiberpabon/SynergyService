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
        Schema::table('PlanillaControlConcreto', function (Blueprint $table) {
            $table->string('codeqr')->nullable()->comment('codigo qr del transporte');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('PlanillaControlConcreto', function (Blueprint $table) {
            Schema::dropIfExists('codeqr');
        });
    }
};
