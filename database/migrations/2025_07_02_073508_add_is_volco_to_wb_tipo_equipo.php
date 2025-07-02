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
    protected $connection = 'sqlsrv2'; //conexion de la base de datos nueva.

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Wb_tipo_equipo', function (Blueprint $table) {
            $table->boolean('is_volco')->default(false)->comment('Indica si el equipo es un volco');
        });

        DB::table('Wb_tipo_equipo')->insert([
            'nombre' => 'VOLCO',
            'estado' => 1,
            'fk_id_project_Company' => 1,
            'horometro' => 0,
            'kilometraje' => 0,
            'created_at' => DB::raw('SYSDATETIME()'),
            'updated_at' => DB::raw('SYSDATETIME()'),
            'is_volco' => true, // Establece el campo is_volco como verdadero
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_tipo_equipo', function (Blueprint $table) {
            $table->dropColumn('is_volco');
        });
    }
};
