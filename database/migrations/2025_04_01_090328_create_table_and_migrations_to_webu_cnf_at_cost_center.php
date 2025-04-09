<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    protected $connection = 'sqlsrv3';
    public function up()
    {
        Schema::create('wb_costos', function (Blueprint $table) {
            $table->id();
            $table->string('Codigo', 20)->nullable();
            $table->string('Descripcion', 100)->nullable();
            $table->string('Observacion', 200)->nullable();
            $table->string('UM', 20)->nullable();
            $table->string('Grupo', 50)->nullable();
            $table->boolean('Distribuible')->default(0);
            $table->boolean('Estado')->default(1);
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('fk_user_creador')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('fk_user_update')->nullable();
            $table->integer('fk_id_project_Company')->nullable();
            $table->integer('fk_compania')->nullable();
        });
        DB::unprepared('SET IDENTITY_INSERT wb_costos ON');
        // // Add unique index
        // Schema::table('wb_costos', function (Blueprint $table) {
        //     $table->unique(['Codigo', 'fk_id_project_Company'], 'IX_Wb_Costos');
        // });

        // Data migration from SmartAccess.dbo.CNFCOSTCENTER
        DB::statement("
            INSERT INTO wb_costos (id,Codigo, Descripcion, Observacion, UM, Grupo, Distribuible, Estado, fk_user_creador, fk_id_project_Company, fk_compania)
            SELECT
                COCEIDENTIFICATION,
                COSYNCCODE,
                COCENAME,
                COCEOBSERVATION,
                NULL,
                NULL,
                DISTRIBUTABLE,
                COCEENABLED,
                60088,
                1,
                1
            FROM [10.57.20.9].bdsolicitudes.dbo.CNFCOSTCENTER
            WHERE COSYNCCODE IS NOT NULL
        ");

        // Remove duplicates
        DB::statement("
            DELETE FROM wb_costos
            WHERE id NOT IN (
                SELECT MIN(id)
                FROM wb_costos
                GROUP BY Codigo
            )
        ");
        DB::unprepared('SET IDENTITY_INSERT wb_costos OFF');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wb_costos');
    }
};
