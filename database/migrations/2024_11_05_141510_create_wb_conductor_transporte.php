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
        Schema::create('Wb_conductor_transporte', function (Blueprint $table) {
            $table->string('cedula')->primary();
            $table->string('nombre');
            $table->integer('estado')->default(1);
            $table->integer('fk_id_project_Company')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Wb_conductor_transporte');
    }
};
