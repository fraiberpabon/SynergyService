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
use App\Models\WbEquipo;
use App\Models\wbTipoEquipo;
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
        try {
            // se valida la informacion recibida
            $validator = Validator::make($req->all(), [
                'CODIGO' => 'required|min:3',
                'MARCA' => 'required|min:1',
                'MODELO' => 'present|max:50',
                'PLACA' => 'present|max:50',
                'CUBICAJE' => 'present|numeric',
                'TIPO' => 'required|size:1|regex:/[O,R]/',
                'CONTRATISTAID' => 'required|max:20',
                'TIPO_CONTRATO' => 'required',
                'DESCRIPCION' => 'required|max:50',
                'OBSERVACION' => 'present',
                'TIPO_CONTRATO' => 'present',
            ]);
            // si no cumple validaciones imprime error
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }
            /**
             * Copio todos los datos a la variable $datos.
             */
            $datos = $req->all();
            // inicia la instancia de contratista
            //validacion si la compañia existe
            $companiaProyecto = new WbCompanieProyecto();
            $companiaProyecto = WbCompanieProyecto::where('fk_compañia', $datos['CONTRATISTAID'])
                ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                ->first();
            //Aqui devuelve un handle alert de que no existen las compañias
            if (!$companiaProyecto) {
                return $this->handleAlert(__('No existen compañias para este proyecto.'));
            }
            //Aqui se compara si tipo de equipo es vacio o diferente de 0 el me trae por proyecto los tipos de equipo
            if (
                strcmp($req->tipoDeEquipo, '') != 0 && wbTipoEquipo::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                ->find($req->tipoDeEquipo) == null
            ) {
                //Aqui devuelve un handle alert de que no se encontro el tipo de equipo
                return $this->handleAlert(__('messages.tipo_de_equipo_no_encontrado'));
            }

            //Aqui se verifica si se encuentra un equipo con el mismo codigo
            if (
                WbEquipo::where('equiment_id', $req->CODIGO)
                ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                ->first() != null
            ) {
                //retorna el mensaje que el equipo ya esta registrado
                return $this->handleAlert(__('messages.ya_existe_un_equipo_con_el_mismo_codigo_registrado'));
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            /*
             * Si el codigo es un numero le concateno al inicio el caracter E y recorto el numero a los 3 primeros caracteres,
             * si no tomo los caracteres antes del punto
             */

            if (is_numeric(substr($datos['CODIGO'], 0, 1))) {
                $a = 'E' . substr($datos['CODIGO'], 0, 3);
            } else {
                $a = substr($datos['CODIGO'], 0, strpos($datos['CODIGO'], '.'));
            }
            // Inserto el equipo a la tabla WbEquipo
            $equipo = new WbEquipo();
            $equipo->equiment_id = $datos['CODIGO'];
            $equipo->marca = $datos['MARCA'];
            $equipo->modelo = $datos['MODELO'];
            $equipo->placa = $datos['PLACA'];
            $equipo->cubicaje = $datos['CUBICAJE'];
            $equipo->dueno = $datos['TIPO'];
            $equipo->descripcion = $datos['DESCRIPCION'];
            $equipo->observacion = $datos['OBSERVACION'];
            $equipo->estado = 'A';
            $equipo->fk_compania = $datos['CONTRATISTAID'];
            $equipo->fk_user_creador = $this->traitGetIdUsuarioToken($req);
            $equipo->fk_id_project_Company = $proyecto;
            $equipo->tipocontrato = $datos['TIPO_CONTRATO'];
            if (strcmp($req->tipoDeEquipo, -1) != 0) {
                $equipo->fk_id_tipo_equipo = $req->tipoDeEquipo;
            }
            /*
             * Solo se agregara el equipo a TimeScanSI si estamos en el proyecto 1
             */
            if ($proyecto == 1) {
                $equipoTimeScan = new ts_Equipement();
                /*
                 * Se corta los datos de entrada si superan el limite con el que deben ser guardado.
                 */
                if (strlen($datos['CODIGO']) > 10) {
                    $datos['CODIGO'] = substr($datos['CODIGO'], 0, 10);
                }
                if (strlen($datos['DESCRIPCION']) > 50) {
                    $datos['DESCRIPCION'] = substr($datos['DESCRIPCION'], 0, 50);
                }
                if (strlen($datos['MODELO']) > 50) {
                    $datos['MODELO'] = substr($datos['MODELO'], 0, 50);
                }
                if (strlen($datos['PLACA']) > 50) {
                    $datos['PLACA'] = substr($datos['PLACA'], 0, 50);
                }
                if (strlen($datos['MARCA']) > 50) {
                    $datos['MARCA'] = substr($datos['MARCA'], 0, 50);
                }
                if (strlen($datos['TIPO']) > 1) {
                    $datos['TIPO'] = substr($datos['TIPO'], 0, 1);
                }
                //se guarda en la tabla de timescan
                $equipoTimeScan->EquipmentID = $datos['CODIGO'];
                $equipoTimeScan->Make = $datos['MARCA'];
                $equipoTimeScan->Model = $datos['MODELO'];
                $equipoTimeScan->SerialNumber = $datos['PLACA'];
                $equipoTimeScan->Payload = $datos['CUBICAJE'];
                $equipoTimeScan->Owned = $datos['TIPO'];
                $equipoTimeScan->Description = $datos['DESCRIPCION'];
                $equipoTimeScan->Comments = $datos['OBSERVACION'];
                $equipoTimeScan->Status = 'A';
                $equipoTimeScan->ContractID = $datos['CONTRATISTAID'];
                $equipoTimeScan->ModelNumber = $a;
                if (!$equipoTimeScan->save()) {
                    return $this->handleAlert('Equiment no guadado.');
                }
            }
            //se guarda en la tabla de webu
            $equipo->save();
            //se guarda con exito
            return $this->handleResponse($req, [], 'El equipo ' . $datos['CODIGO'] . ' ha sido insertado con exito');
        } catch (\Exception $e) {
            var_dump($e);
            //retorna algun error al insertar dependiendo de las validaciones antes mencionadas
            return $this->handleError('Error al  insertar', $e->getMessage());
        }
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
        $WBequipos = WbEquipo::where('equiment_id', $equipo)
            ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($request))
            ->first();
        $usuario = $this->traitGetIdUsuarioToken($request);
        switch ($WBequipos->estado) {
            case 'A':
                $nuevoEstado = 'I';
                $mensaje = __('messages.inactivado');
                break;
            case 'I':
                $nuevoEstado = 'A';
                $mensaje = __('messages.activado');
                break;
        }
        $WBequipos->fk_user_update = $usuario;
        $WBequipos->estado = $nuevoEstado;
        $WBequipos->save();

        if ($this->traitGetProyectoCabecera($request) == 1) {
            $equipos = ts_Equipement::find($equipo);
            $equipos->Status = $nuevoEstado;
            $equipos->save();
        }
        $mensaje = __('messages.El equipo') . $equipo . $mensaje = __('messages.ha sido') . $mensaje;
        // $mensaje = 'El equipo ' . $equipo . ' ha sido ' . $mensaje;
        return $this->handleAlert($mensaje, true);
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
     * se filtran los equipos activos donde los equipos sean diferentes de que
     * de inactivos osea esten activos
     */
    public function equiposActivos(Request $request)
    {
        $consulta = WbEquipo::select(
            'id',
            'equiment_id',
            'descripcion',
            'marca',
            'modelo',
            'dueno',
            'Wb_equipos.estado',
            'placa',
            'placa',
            'Wb_tipo_equipo.id_tipo_equipo as idTipoEquipo',
            'Wb_tipo_equipo.nombre as nombreTipoEquipo'
        )->leftJoin('Wb_tipo_equipo', 'Wb_tipo_equipo.id_tipo_equipo', 'Wb_equipos.fk_id_tipo_equipo')
            ->where('Wb_equipos.estado', '!=', 'I');
        $consulta = $this->filtrarPorProyecto($request, $consulta, 'Wb_equipos')->get();

        $is_excel = true;
        return $this->handleResponse($request, $this->equiposToArray($consulta), 'consultado');
        return $this->handleResponse($request, $this->syncRegistroToArray($consulta, $is_excel), __("messages.consultado"));
    }

    /**
     * @return false|string
     * Aqui consultamos los equipos de time scan que se encuentren activos
     * @deprecated
     */
    public function equiposActivosDeprecated(Request $request)
    {
        $consulta = WbEquipo::select(
            'equiment_id as EquipmentID',
            'descripcion as Description',
            'marca as Make',
            'modelo as Model',
            'dueno as Owned',
            'estado as Status',
            'placa as SerialNumber'
        )
            ->where('estado', '!=', 'I');

        // $consulta = $this->filtrar($request, $consulta)->get();
        return json_encode(new EquipementsCollection($consulta->get()));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    /*
    * Función que actualiza un equipo tanto en TimeScan como en Webu
    * Esta función contiene la validación del formulario
    * Ademas de la funciones se valida si esta en proyecto 1 para actualizar la
    * tabla de TimeScan.
    */
    public function updateEquipo(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'CODIGO' => 'required',
                'MARCA' => 'required|min:1',
                'MODELO' => 'present|max:50',
                'PLACA' => 'present|max:50',
                'CUBICAJE' => 'present|numeric',
                'TIPO' => 'required|size:1|regex:/[O,R]/',
                'CONTRATISTAID' => 'required|max:20',
                'TIPO_CONTRATO' => 'required',
                'DESCRIPCION' => 'required|max:50',
                'OBSERVACION' => 'present',
                'TIPO_CONTRATO' => 'present',
            ]);
            //validamos que el formulario no contenga errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }
            //recogemos datos
            $datos = $req->all();

            //buscamos el codigo por proyectos y recogemos el primero
            $equipo = WbEquipo::where('equiment_id', $datos['CODIGO'])
                ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                ->first();
            //validacion si la compañia existe
            $companiaProyecto = new WbCompanieProyecto();
            //Buscamos comapñias por proyecto ye recogemos el primero
            $companiaProyecto = WbCompanieProyecto::where('fk_compañia', $datos['CONTRATISTAID'])
                ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                ->first();
            //mandamos la alerta si no existen compañias
            if (!$companiaProyecto) {
                return $this->handleAlert(__('No existen compañias para este proyecto.'));
            }
            //Aqui verificamos que el equipo no venga vacio o sea cero
            if (
                strcmp($req->tipoDeEquipo, '') != 0 && wbTipoEquipo::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                ->find($req->tipoDeEquipo) == null
            ) {
                //mandamos la alerta de que el equipo no fue encontrado
                return $this->handleAlert(__('messages.tipo_de_equipo_no_encontrado'));
            }


            $proyecto = $this->traitGetProyectoCabecera($req);
            // Verificar si se encontró un equipo para actualizar
            if ($equipo) {
                // Actualizar los campos del equipo con los datos recibidos
                $equipo->marca = $datos['MARCA'];
                $equipo->modelo = $datos['MODELO'];
                $equipo->placa = $datos['PLACA'];
                $equipo->cubicaje = $datos['CUBICAJE'];
                $equipo->dueno = $datos['TIPO'];
                $equipo->descripcion = $datos['DESCRIPCION'];
                $equipo->observacion = $datos['OBSERVACION'];
                $equipo->tipocontrato = $datos['TIPO_CONTRATO'];
                $equipo->fk_compania = $datos['CONTRATISTAID'];
                $equipo->fk_user_update = $this->traitGetIdUsuarioToken($req);
                if (strcmp($req->tipoDeEquipo, -1) != 0) {
                    $equipo->fk_id_tipo_equipo = $req->tipoDeEquipo;
                }
                // Guardar los cambios en la base de datos en webu
                $equipo->save();
                // si el proyecto es 1 recortamos las siguientes variables
                if ($proyecto == 1) {
                    $equipoTimeScan = ts_Equipement::where('EquipmentID', $datos['CODIGO'])->first();
                    if (strlen($datos['DESCRIPCION']) > 50) {
                        $datos['DESCRIPCION'] = substr($datos['DESCRIPCION'], 0, 50);
                    }
                    if (strlen($datos['MODELO']) > 50) {
                        $datos['MODELO'] = substr($datos['MODELO'], 0, 50);
                    }
                    if (strlen($datos['PLACA']) > 50) {
                        $datos['PLACA'] = substr($datos['PLACA'], 0, 50);
                    }
                    if (strlen($datos['MARCA']) > 50) {
                        $datos['MARCA'] = substr($datos['MARCA'], 0, 50);
                    }
                    if (strlen($datos['TIPO']) > 1) {
                        $datos['TIPO'] = substr($datos['TIPO'], 0, 1);
                    }

                    //guardamos el timescan
                    $equipoTimeScan->Make = $datos['MARCA'];
                    $equipoTimeScan->Model = $datos['MODELO'];
                    $equipoTimeScan->SerialNumber = $datos['PLACA'];
                    $equipoTimeScan->Payload = $datos['CUBICAJE'];
                    $equipoTimeScan->Owned = $datos['TIPO'];
                    $equipoTimeScan->Description = $datos['DESCRIPCION'];
                    $equipoTimeScan->Comments = $datos['OBSERVACION'];
                    $equipoTimeScan->Status = 'A';
                    $equipo->tipocontrato = $datos['TIPO_CONTRATO'];
                    $equipoTimeScan->save();
                }
                return $this->handleResponse($req, [], 'El equipo ' . $datos['CODIGO'] . ' ha sido actualizado exitosamente');
            } else {
                return $this->handleAlert('No se encontró un equipo con el código ' . $datos['CODIGO']);
            }
        } catch (\Exception $e) {
            var_dump($e);
            return $this->handleError('Error al actualizar', $e->getMessage());
        }
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
