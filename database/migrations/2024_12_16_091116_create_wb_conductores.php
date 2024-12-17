<?php

use App\Models\Transporte\WbTransporteRegistro;
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
            $table->timestamps();
        });

        $transportes = WbTransporteRegistro::select('chofer')
            ->groupBy('chofer')
            ->get();

        if ($transportes->count() == 0) {
            return;
        }


        $datosGuardar = DB::connection($this->con_db_ts)
            ->table($this->tb_employee)
            ->whereIn('EmployeeID', $transportes)
            ->get();

        if ($datosGuardar->count() == 0) {
            return;
        }

        $datosGuardar->map(function ($dato) {
            return [
                'dni' => $dato->EmployeeID ? preg_replace('/[.,\s]/', '', $dato->EmployeeID) : null,
                'nombreCompleto' => $dato->FirstName ?
                    mb_strtoupper($dato->FirstName . ' ' . $dato->LastName, 'UTF-8') :
                    mb_strtoupper($dato->LastName, 'UTF-8'),
                'estado' => $dato->Status == 'A' ? 1 : 0,
                'fk_id_project_Company' => 1,
                'created_at' => now()->format('d-m-Y H:i:s.v'),
                'updated_at' => now()->format('d-m-Y H:i:s.v'),
            ];
        })->each(function ($dato) {
            if ((strlen($dato['dni']) > 6) && (strlen($dato['nombreCompleto']) > 10)) {
                DB::table('Wb_conductores')
                    ->insert($dato);
            }
        });
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
