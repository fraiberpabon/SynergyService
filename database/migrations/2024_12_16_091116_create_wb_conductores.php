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
    protected $connection = 'sqlsrv3'; //conexion de la base de datos nueva.

    //tabla de permisos
    private $tb_employee = 'Employees';

    //conexion con base de datos db_solicitudes
    private $con_db_ts = 'sqlsrv';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Wb_conductores', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('identificador unico del registro');
            $table->string('dni')->comment('identificador unico del conductor');
            $table->string('nombreCompleto')->comment('nombre completo del conductor');
            $table->integer('estado')->default(0)->comment('1 activo, 0 inactivo');
            $table->integer('fk_user_creador')->nullable()->comment('id de usuario creador del registro');
            $table->integer('fk_user_update')->nullable()->comment('id de usuario que modifica el registro');
            $table->integer('fk_id_project_Company')->comment('proyecto al cual pertenece el conductor');
            $table->integer('fk_compania')->default(1)->comment('compañia al cual pertenece el conductor');
            $table->timestamps();
        });

        //establecemos el tamaño de registros por paquete de datos
        $tamanio_paquete_registro = 1000;

        //obtenemos el numero total de registros de la tabla
        $total_registros = DB::connection($this->con_db_ts)->table($this->tb_employee)->count();

        //obtenemos el numero total de paquetes de datos
        $total_paquetes = ceil($total_registros / $tamanio_paquete_registro);

        //recorremos la lista para ir copiando los datos
        for ($i = 0; $i < $total_paquetes; $i++) {

            //consultamos la informacion de la base de datos, lo dividiremos en paquetes de 1000
            $datos = DB::connection($this->con_db_ts)
                ->table($this->tb_employee)
                ->where('EmployeeType', 'o')
                ->offset($i * $tamanio_paquete_registro)
                ->limit($tamanio_paquete_registro)
                ->get();

            //tomamos los registros y los mapeamos la informacion situando cada dato en la columna correspondiente a la nueva tabla
            $datos->map(function ($dato) {
                return [
                    'dni' => $dato->EmployeeID,
                    'nombreCompleto' => $dato->FirstName ? $dato->FirstName . ' ' . $dato->LastName : $dato->LastName,
                    'estado' => $dato->Status == 'A' ? 1 : 0,
                    //'fk_user_creador' => $dato->tamaño,
                    'fk_id_project_Company' => 1,
                    'fk_compania' => 1,
                    // Formateamos las fechas en el formato requerido
                    'created_at' => now()->format('d-m-Y H:i:s.v'),
                    'updated_at' => now()->format('d-m-Y H:i:s.v'),
                ];
            })
                //insertamos uno a uno la informacion dentro de la nueva tabla.
                ->each(function ($dato) {
                    DB::table('Wb_conductores')
                        ->insert($dato);
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
        Schema::dropIfExists('Wb_conductores');
    }
};
