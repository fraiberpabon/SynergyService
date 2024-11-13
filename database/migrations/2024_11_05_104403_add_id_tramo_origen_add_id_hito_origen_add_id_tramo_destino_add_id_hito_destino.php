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

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Wb_transporte_registro', function (Blueprint $table) {
            $table->integer('id_tramo_origen')->nullable()->comment('id del tramo de origen con el cual se registro el transporte');
            $table->integer('id_hito_origen')->nullable()->comment('id del hito de origen con el cual se registro el transporte');
            $table->integer('id_tramo_destino')->nullable()->comment('id del tramo de destino con el cual se registro el transporte');
            $table->integer('id_hito_destino')->nullable()->comment('id del hito de destino con el cual se registro el transporte');
        });

        $datos = DB::table('Wb_transporte_registro')
            ->whereNotNull('fk_id_tramo_origen')
            ->orWhereNotNull('fk_id_tramo_destino')
            ->get();

        $datos->each(function ($dato) {
            // Actualizar tramo y hito de origen si están definidos
            if (!is_null($dato->fk_id_tramo_origen)) {
                $tramoOrigen = DB::connection('sqlsrv2')->table('Wb_Tramos')
                    ->where('Id_Tramo', $dato->fk_id_tramo_origen)
                    ->where('fk_id_project_Company', $dato->fk_id_project_Company)
                    ->first();

                $hitoOrigen = DB::connection('sqlsrv2')->table('Wb_Hitos')
                    ->where('Id_Hitos', $dato->fk_id_hito_origen)
                    ->where('fk_id_project_Company', $dato->fk_id_project_Company)
                    ->first();

                $dato->id_tramo_origen = $tramoOrigen->id;
                $dato->id_hito_origen = $hitoOrigen->Id;
            }

            // Actualizar tramo y hito de destino si están definidos
            if (!is_null($dato->fk_id_tramo_destino)) {
                $tramoDestino = DB::connection('sqlsrv2')->table('Wb_Tramos')
                    ->where('Id_Tramo', $dato->fk_id_tramo_destino)
                    ->where('fk_id_project_Company', $dato->fk_id_project_Company)
                    ->first();

                $hitoDestino = DB::connection('sqlsrv2')->table('Wb_Hitos')
                    ->where('Id_Hitos', $dato->fk_id_hito_destino)
                    ->where('fk_id_project_Company', $dato->fk_id_project_Company)
                    ->first();

                $dato->id_tramo_destino = $tramoDestino->id;
                $dato->id_hito_destino = $hitoDestino->Id;
            }

            // Guardar los cambios en el registro
            DB::table('Wb_transporte_registro')
                ->where('id', $dato->id)
                ->update([
                    'id_tramo_origen' => $dato->id_tramo_origen,
                    'id_hito_origen' => $dato->id_hito_origen,
                    'id_tramo_destino' => $dato->id_tramo_destino,
                    'id_hito_destino' => $dato->id_hito_destino,
                ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Wb_transporte_registro', function (Blueprint $table) {
            $table->dropColumn('id_tramo_origen');
            $table->dropColumn('id_hito_origen');
            $table->dropColumn('id_tramo_destino');
            $table->dropColumn('id_hito_destino');
        });
    }
};
