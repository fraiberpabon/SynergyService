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
        DB::table('Wb_Seguri_Permisos')->insert([
            'nombrePermiso' => 'SYNC_VEH_EDI_PESO',
            'date_create' => DB::raw('SYSDATETIME()'),
            'descripcion' => 'Permite sincronizar los datos de peso de los vehiculos desde la aplicacion movil a la base de datos',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('Wb_Seguri_Permisos')->where('nombrePermiso', 'SYNC_VEH_EDI_PESO')->delete();
    }
};
