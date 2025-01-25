<?php

use App\Models\Equipos\WbEquipoEstado;
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
    protected $connection = 'sqlsrv3'; //conexion de la base de datos nueva.


    private $table_name = 'Wb_equipo_estado';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table_name, function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->comment('Nombre que recibe el estado');
            $table->string('descripcion')->nullable()->comment('descripcion del estado en cuestion');
            $table->integer('estado')->default(1)->comment('estado de actividad del estado creado, 1 activo 0 inactivo');
            $table->integer('fk_id_project_Company')->default(1)->comment('proyecto al cual pertenece el estado');
            $table->timestamps();
        });

        WbEquipoEstado::create([
            'nombre' => 'Operativa',
            'descripcion' => '',
            'estado' => 1,
            'fk_id_project_Company' => 1
        ]);

        WbEquipoEstado::create([
            'nombre' => 'No Operativa',
            'descripcion' => '',
            'estado' => 1,
            'fk_id_project_Company' => 1
        ]);
        WbEquipoEstado::create([
            'nombre' => 'Reparación',
            'descripcion' => '',
            'estado' => 1,
            'fk_id_project_Company' => 1
        ]);
        WbEquipoEstado::create([
            'nombre' => 'En Espera',
            'descripcion' => '',
            'estado' => 1,
            'fk_id_project_Company' => 1
        ]);

        /* proyecto canada */
        WbEquipoEstado::create([
            'nombre' => 'Turn on',
            'descripcion' => '',
            'estado' => 1,
            'fk_id_project_Company' => 4
        ]);
        WbEquipoEstado::create([
            'nombre' => 'Turn on',
            'descripcion' => '',
            'estado' => 1,
            'fk_id_project_Company' => 4
        ]);
        WbEquipoEstado::create([
            'nombre' => 'Idle',
            'descripcion' => '',
            'estado' => 1,
            'fk_id_project_Company' => 4
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table_name);
    }
};
