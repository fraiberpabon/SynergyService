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
        Schema::table('Wb_Liberaciones_Formatos', function (Blueprint $table) {
            $table->string('html_report','MAX')->nullable()->comment('contendra texto html o ruta de blade a compilar para enviar al dispositivo');
        });

        DB::table('Wb_tipo_formato')->insert([
            'nombre' => 'Transporte solicitud material',
        ]);

        DB::table('Wb_tipo_formato')->insert([
            'nombre' => 'Transporte solicitud asfalto',
        ]);

        DB::table('Wb_tipo_formato')->insert([
            'nombre' => 'Transporte solicitud concreto',
        ]);

        DB::table('Wb_tipo_formato')->insert([
            'nombre' => 'Transporte bascula',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_Liberaciones_Formatos', function (Blueprint $table) {
            Schema::dropIfExists('html_report');
        });

        DB::table('Wb_tipo_formato')->where('nombre', 'Transporte solicitud material')->delete();
        DB::table('Wb_tipo_formato')->where('nombre', 'Transporte solicitud asfalto')->delete();
        DB::table('Wb_tipo_formato')->where('nombre', 'Transporte solicitud concreto')->delete();
        DB::table('Wb_tipo_formato')->where('nombre', 'Transporte bascula')->delete();
    }
};
