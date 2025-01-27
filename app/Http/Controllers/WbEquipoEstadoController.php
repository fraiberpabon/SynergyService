<?php

/**
 * Aqui se realizan todas las importaciones para usar el controlador
 */

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Compania;
use App\Models\Equipos\WbEquipoEstado;
use App\Models\SyncRelacionVehiculoPesos;
use App\Models\Equipos\WbEquipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Aqui se encuentra la clase WbEquipoControlles que contiene toda la
 * logica del controlador de equipos
 */
class WbEquipoEstadoController extends BaseController implements Vervos
{

    /*
     * Función que crea un equipo tanto en TimeScan como en Webu
     * Esta función contiene la validación del formulario
     * Ademas de la funciones se valida si esta en proyecto 1 para actualizar la
     * tabla de TimeScan.
     */
    public function post(Request $req)
    {
    }

    /**
     * Funcion de update no tocar por la interface de vervos
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * Funcion de delete no tocar por la interface de vervos
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * Funcion de get no tocar por la interface de vervos
     */
    public function get(Request $request)
    {
    }

    public function getActivosForProject(Request $req) {
        try {
            $query = WbEquipoEstado::where('estado', 1)
                ->select(
                    'id',
                    'nombre',
                    'descripcion',
                    'fk_id_project_Company'
                );
            $query = $this->filtrar($req, $query)->orderBy('id', 'ASC')->get();

            return $this->handleResponse($req, $this->WbEquipoEstadotoArray($query), __('messages.consultado'));
        } catch (\Exception $e) {
            return $this->handleAlert($e->getMessage(), false);
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
