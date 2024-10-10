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
    protected $connection = 'sqlsrv2'; //conexion de la base de datos dbsolicitudes

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('Wb_Seguri_Permisos')->insert([
            'nombrePermiso' => 'TRANSPORTES_VER',
            'date_create' => DB::raw('SYSDATETIME()'),
            'descripcion' => 'Permite ver la lista de transportes en el modulo administrativo de transportes',
        ]);

        DB::table('Wb_Seguri_Permisos')->insert([
            'nombrePermiso' => 'TRANSPORTES_CREAR',
            'date_create' => DB::raw('SYSDATETIME()'),
            'descripcion' => 'Permite en el dispositivo movil crear registros de transportes',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('Wb_Seguri_Permisos')->where('nombrePermiso', 'TRANSPORTES_VER')->delete();
        DB::table('Wb_Seguri_Permisos')->where('nombrePermiso', 'TRANSPORTES_CREAR')->delete();
    }
};
