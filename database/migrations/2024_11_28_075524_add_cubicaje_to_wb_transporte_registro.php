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
    protected $connection = 'sqlsrv3'; //conexion de la base de datos bdsolicitudes.

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        //conexion con base de datos db_solicitudes
        $con_db_cadu = 'sqlsrv2';

        Schema::table('Wb_transporte_registro', function (Blueprint $table) {
            $table->decimal('cubicaje', 5, 2)
                ->nullable()
                ->comment('peso maximo que puede tener un equipo en un registro de transporte de bascula movil');
        });

        //establecemos el tamaño de registros por paquete de datos
        $tamanio_paquete_registro = 1000;

        //obtenemos el numero total de registros de la tabla
        $total_registros = DB::table('Wb_transporte_registro')->count();

        //obtenemos el numero total de paquetes de datos
        $total_paquetes = ceil($total_registros / $tamanio_paquete_registro);

        //recorremos la lista para ir copiando los datos
        for ($i = 0; $i < $total_paquetes; $i++) {
            $info = DB::table('Wb_transporte_registro')
                ->offset($i * $tamanio_paquete_registro)
                ->limit($tamanio_paquete_registro)
                ->get();


            // Extraer los ids de equipos para la búsqueda masiva
            $ids_equipos = $info->pluck('fk_id_equipo')->unique();

            // Obtener todos los equipos relacionados de una vez
            $equipos = DB::connection($con_db_cadu)->table('Wb_equipos')
                ->whereIn('id', $ids_equipos)
                ->get()
                ->keyBy('id');

            // Actualizar los registros con los datos correspondientes
            $actualizaciones = $info->filter(function ($key) use ($equipos) {
                return isset($equipos[$key->fk_id_equipo]);
            })->map(function ($key) use ($equipos) {
                return [
                    'id' => $key->id,
                    'cubicaje' => $equipos[$key->fk_id_equipo]->cubicaje,
                ];
            });

            // Aplicar las actualizaciones
            $actualizaciones->each(function ($item) {
                DB::table('Wb_transporte_registro')
                    ->where('id', $item['id'])
                    ->update(['cubicaje' => $item['cubicaje']]);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_transporte_registro', function (Blueprint $table) {
            $table->dropColumn('cubicaje');
        });
    }
};
