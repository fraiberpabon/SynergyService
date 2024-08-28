<?php

/**
 * Aqui se realizan todas las importaciones para usar el controlador
 */

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Http\Resources\EquipementsCollection;
use App\Models\Compania;
use App\Models\SyncRelacionVehiculoPesos;
use App\Models\ts_Equipement;
use App\Models\Equipos\WbEquipo;
use App\Models\Equipos\wbTipoEquipo;
use App\Models\WbCompanieProyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



/*
* Se debe tener en cuenta que la siguiente estructura se debe respetar
* de la interface Vervos se debe tener en cuenta que los metodos acá mostrados deben ser inmmutables
* recomendación crear nuevos metodos sin alterar los mencionados.
    public function post(Request $req);
    public function update(Request $req, $id);
    public function delete(Request $request, $id);
    public function get(Request $request);
    public function getPorProyecto(Request $request, $proyecto);
*/


/**
 * Aqui se encuentra la clase WbEquipoControlles que contiene toda la
 * logica del controlador de equipos
 */
class WbEquipoControlles extends BaseController implements Vervos
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

    //Aqui se busca la compañia por id en cual se tienen dos parametros modelo
    // y vamos asiganando compañias, se anexa a la
    //lista de equipos de respuesta el peso vacio que tiene registrado en
    //el sistema, modelo es listado de equipos y array lista de pesos
    private function setCompaniaById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($modelo->fk_compania == $array[$i]->id_compañia) {
                $reescribir = $this->companiaToModel($array[$i]);
                $modelo->objectCompania = $reescribir;
                break;
            }
        }
    }
    //Aqui se busca el peso  por id en cual se tienen dos parametros modelo
    // y array recorremos el array y vamos asiganando el peso
    private function setPesoById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($modelo->equiment_id == $array[$i]->vehiculo) {
                $reescribir = $this->companiaToModel($array[$i]);
                $modelo->objectPeso = $reescribir;
                break;
            }
        }
    }

    /**
     * Aqui procedemos a cambiar el estado del equipo de activo a inactivo
     * teniendo en cuenta sus estado inciaial primero verificamos a que proyecto pertenece
     * ese equipo
     * luego verificamos el estado inicial si es A lo cambiamos a I y si es I lo cambiamos a A
     * con sus respectivos mensajes y luego guardamos el usuario que lo edito y guardamos el estado
     * y en el caso de que sea 1 el proyecto guardamos en timescan
     */
    public function CambiarEstado(Request $request, $equipo)
    {
    }

    /**
     * Aqui se encuentra la funcion de equipo viajes para bascula la cual
     *se recibe un valor de filtro en la variable equipos,
     * en caso de verdadero se filtra la consulta por compañia, placa o id_equipo,luego se valida si se recibe
     * valores de filtro de id o estado para filtrar la consulta, luego se ordena de forma,
     * Finalmente se incrusta la informacion de la compañia y el peso de cada equipo en el resultado
     *
     */
    public function equiposParaViajeBascula(Request $request, $equipo = 0)
    {
        $baseDatos = Db::connection('sqlsrv2')->getDatabaseName() . '.dbo.';
        $consulta = WbEquipo::select('Wb_equipos.*')
            ->leftJoin('compañia as sc', 'sc.id_compañia', 'Wb_equipos.fk_compania')
            ->selectRaw("(SELECT top 1 PESO FROM {$baseDatos}sync_relacion_VehiculosPesos WHERE vehiculo = Wb_equipos.equiment_id collate SQL_Latin1_General_CP1_CI_AS) as peso");
        // ->selectRaw("(SELECT COUNT(equipo) FROM sync_registros WHERE equipo=Wb_equipos.equiment_id collate SQL_Latin1_General_CP1_CI_AS) as CANTIDAD");
        if ($equipo != 0 && strlen($equipo) > 0) {
            $equipo = strtolower($equipo);
            $consulta = $consulta->where(function ($query) use ($equipo) {
                $query->where(DB::raw('LOWER(sc.nombreCompañia)'), 'like', DB::raw("'%$equipo%'"))
                    ->orWhere(DB::raw('LOWER(Wb_equipos.placa)'), 'like', DB::raw("'%$equipo%'"))
                    ->orWhere(DB::raw('LOWER(Wb_equipos.equiment_id)'), 'like', DB::raw("'%$equipo%'"));
            });
        }
        if ($request->has('id') && strlen($request->id) > 0) {
            $consulta = $consulta->where('equiment_id', $request->id);
        }
        if ($request->has('estado') && strlen($request->estado) > 0) {
            $consulta = $consulta->where('estado', 'A');
        }
        $consulta = $consulta->orderBy('Wb_equipos.equiment_id', 'asc');
        $limitePaginas = 1;
        if ($request->query('page') && is_numeric($request->page) && $request->query('limit') && is_numeric($request->limit)) {
            $consulta = $this->filtrar3($request, $consulta, 'Wb_equipos');
            $contador = clone $consulta;
            $contador = $contador->select('Wb_equipos.equiment_id')->get();
            $consulta = $consulta->forPage($request->page, $request->limit)->get();
            $limitePaginas = ($contador->count() / $request->limit) + 1;
        } else {
            $consulta = $this->filtrar3($request, $consulta, 'Wb_equipos')->get();
        }
        $companias = Compania::select('id_compañia', 'nombreCompañia')->get();
        $pesos = SyncRelacionVehiculoPesos::select('vehiculo', 'peso')->get();
        foreach ($consulta as $item) {
            $this->setCompaniaById($item, $companias);
            $this->setPesoById($item, $pesos);
        }

        return $this->handleResponse($request, $this->equiposToArray($consulta), __('messages.consultado'), $limitePaginas);
    }


    /**
     * En esta funcion validamos si el equipo existe y si esta activo
     * en caso de que no lo sea mostramos que el equipo no fue encontrado
     */
    public function validarEquimentId(Request $request, $id)
    {
        $consulta = WbEquipo::where('equiment_id', $id)->where('estado', 'A')->first();
        if ($consulta != null) {
            return $this->handleResponse($request, ['placa' => $consulta->placa, 'descripcion' => $consulta->descripcion], __('messages.consultado'), $consulta != null);
        } else {
            return $this->handleAlert('Equipo no encontrado');
        }
    }


    /**
     * Aqui se encuentra la funcion de los equipos activos
     * se filtran los equipos diferentes de inactivos
     */
    public function equiposActivos(Request $request)
    {
        $consulta = WbEquipo::with([
            'tipo_equipo' => function ($query) {
                $query->select('id_tipo_equipo', 'nombre');
            },
            'vehiculos_pesos' => function ($query) {
                $query->select('vehiculo', 'peso');
            },
            'compania' => function ($query) {
                $query->select('id_compañia', 'nombreCompañia');
            }
            /* 'horometros' => function ($query) {
                $query->select('id_compañia', 'nombreCompañia');
            } */
           /* 'ubicacion' => function ($query) {
                $query->select('id_compañia', 'nombreCompañia');
            } */
        ])->where('estado', '!=', 'I')
            ->select(
                'id',
                'equiment_id',
                'descripcion',
                'cubicaje',
                'marca',
                'modelo',
                'placa',
                'observacion',
                'dueno',
                'estado',
                'tipocontrato',
                //'codigo_externo',
                //'horometro_inicial',
                'fk_compania',
                'fk_id_tipo_equipo',
                'fk_id_project_Company'
            );

        $consulta = $this->filtrar($request, $consulta)->orderBy('equiment_id', 'DESC')->get();
        //return $this->handleResponse($request, $consulta->get(), 'consultado');
        return $this->handleResponse($request, $this->equiposToArray($consulta), 'consultado');
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    /*
    *Función que me permite listar los equipos para descargar el excel en la cual pasamos las
    variables que necesistamos para consultar los equipos
    */

    public function verEquipos(Request $request)
    {

        //consultamos los equipos
        $baseDatos = Db::connection('sqlsrv2')->getDatabaseName() . '.dbo.';
        $consulta = WbEquipo::select(
            'Wb_equipos.equiment_id',
            'Wb_equipos.descripcion',
            'Wb_equipos.cubicaje',
            'Wb_equipos.marca',
            'Wb_equipos.modelo',
            'Wb_equipos.placa',
            'Wb_equipos.observacion',
            'Wb_equipos.dueno',
            'Wb_equipos.estado',
            'sc.nombreCompañia',
            'wb.nombre',
            'Wb_equipos.tipocontrato'
        )
            //agrupamos por compañia y tipo de equipo
            ->leftJoin('compañia as sc', 'sc.id_compañia', 'Wb_equipos.fk_compania')
            ->leftJoin('Wb_tipo_equipo as wb', 'wb.id_tipo_equipo', 'Wb_equipos.fk_id_tipo_equipo');

        $equipo = $request->busqueda;
        if ($equipo != 0 && strlen($equipo) > 0) {
            $equipo = strtolower($equipo);
            //se filtra por placa y nombre de la compañia
            $consulta = $consulta->where(function ($query) use ($equipo) {
                $query->where(DB::raw('LOWER(sc.nombreCompañia)'), 'like', DB::raw("'%$equipo%'"))
                    ->orWhere(DB::raw('LOWER(Wb_equipos.placa)'), 'like', DB::raw("'%$equipo%'"))
                    ->orWhere(DB::raw('LOWER(Wb_equipos.equiment_id)'), 'like', DB::raw("'%$equipo%'"))
                    ->orWhere(DB::raw('LOWER(wb.nombre)'), 'like', DB::raw("'%$equipo%'"));
            });
        }
        if ($request->has('id') && strlen($request->id) > 0) {
            $consulta = $consulta->where('equiment_id', $request->id);
        }
        //traemos los que esten activos
        if ($request->has('estado') && strlen($request->estado) > 0) {
            $consulta = $consulta->where('estado', 'A');
        }
        $consulta = $consulta->orderBy('Wb_equipos.equiment_id', 'asc');
        $consulta = $this->filtrar3($request, $consulta, 'Wb_equipos')->get();
        //retornamos que fue consultado correctamente
        return $this->handleResponse($request, $consulta, __('messages.consultado'));
    }
}
