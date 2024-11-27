<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'sqlsrv2'; //conexion de la base de datos bdsolicitudes.

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Wb_configuraciones', function (Blueprint $table) {
            $table->integer('transporte_max_peso')
                ->default(300)
                ->nullable()
                ->comment('peso maximo que puede tener un equipo en un registro de transporte de bascula movil');

            $table->integer('transporte_min_peso')
                ->default(100)
                ->nullable()
                ->comment('peso minimo que puede tener un equipo en un registro de transporte de bascula movil');

            $table->integer('transporte_usar_equipo_peso')
                ->default(0)
                ->nullable()
                ->comment('1 tomará el peso del equipo como peso1 en un registro de transporte de bascula movil, 0 no tendrá en cuenta el peso del equipo como peso1');
        });

        // actualizas los registros existentes
        DB::table('Wb_configuraciones')->update([
            'transporte_max_peso' => 0,
            'transporte_min_peso' => 0,
            'transporte_usar_equipo_peso' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_configuraciones', function (Blueprint $table) {
            $table->dropColumn('transporte_max_peso');
            $table->dropColumn('transporte_min_peso');
            $table->dropColumn('transporte_usar_equipo_peso');
        });
    }
};
