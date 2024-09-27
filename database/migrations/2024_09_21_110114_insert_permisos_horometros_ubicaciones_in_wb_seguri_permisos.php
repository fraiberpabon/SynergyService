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
    protected $connection = 'sqlsrv2'; //conexion de la base de datos dbsolicitudes

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('Wb_Seguri_Permisos')->insert([
            'nombrePermiso' => 'HOROMETRO_UBICACIONES_VER',
            'date_create' => DB::raw('SYSDATETIME()'),
            'descripcion' => 'Permite ver la lista de horometros y ubicaciones en el modulo administrativo de horometros y ubicaciones',
        ]);

        DB::table('Wb_Seguri_Permisos')->insert([
            'nombrePermiso' => 'HOROMETRO_UBICACIONES_CREAR',
            'date_create' => DB::raw('SYSDATETIME()'),
            'descripcion' => 'Permite en el dispositivo movil crear registros de horometros y ubicaciones',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('Wb_Seguri_Permisos')->where('nombrePermiso', 'HOROMETRO_UBICACIONES_VER')->delete();
        DB::table('Wb_Seguri_Permisos')->where('nombrePermiso', 'HOROMETRO_UBICACIONES_CREAR')->delete();
    }
};
