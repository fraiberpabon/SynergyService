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
        //
        Schema::table('PlanillaControlConcreto', function (Blueprint $table) {
            $table->string('placaVehiculo', 50)->nullable()->change();
        });

        Schema::table('PlanillaControlAsfalto', function (Blueprint $table) {
            $table->string('placaVehiculo', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
