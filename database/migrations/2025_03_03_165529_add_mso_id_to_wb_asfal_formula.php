<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'sqlsrv2'; //conexion de la base de datos}

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Wb_Asfal_Formula', function (Blueprint $table) {
            $table->string(column: 'mso_id')->nullable()->comment('Idenficador unico de la formula de asfalto');
        });

        DB::table('Wb_Asfal_Formula')->where('id_asfal_formula', 1)->update([
            'mso_id' => '011', //MDC19
        ]);

        DB::table('Wb_Asfal_Formula')->where('id_asfal_formula', 2)->update([
            'mso_id' => '012', //MDC25
        ]);

        DB::table('Wb_Asfal_Formula')->where('id_asfal_formula', 3)->update([
            'mso_id' => '064', //MSC25
        ]);

        DB::table('Wb_Asfal_Formula')->where('id_asfal_formula', 4)->update([
            'mso_id' => '026', //MDC10
        ]);

        DB::table('Wb_Asfal_Formula')->where('id_asfal_formula', 5)->update([
            'mso_id' => '063', //MSC19
        ]);

        DB::table('Wb_Asfal_Formula')->where('id_asfal_formula', 7)->update([
            'mso_id' => '026', //MDC10
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_Asfal_Formula', function (Blueprint $table) {
            Schema::dropIfExists('mso_id');
        });
    }
};
