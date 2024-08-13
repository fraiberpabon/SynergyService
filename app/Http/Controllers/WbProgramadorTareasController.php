<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbProgramadorTarea;
use DB;
use Illuminate\Http\Request;
use Validator;

class WbProgramadorTareasController extends BaseController implements Vervos
{
    /* aplicacion movil */
    /* public function getMovil(Request $request)
    {
        $proyecto = $this->traitGetProyectoCabecera($request);
        $turnos = WbProgramadorTarea::where('fk_id_project_Company', $proyecto)->where('estado', 1)->get();

        if (count($turnos) == 0) {
            return $this->handleAlert(__('messages.no_tiene_turnos_registrados'), false);
        }
        return $this->handleResponse($request, $this->wbSeguridadSitioTurnoToArray($turnos), __('messages.consultado'));
    } */
    /* fin aplicacion movil */



    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method
        $validator = Validator::make($req->all(), [
            'descripcion' => 'required',
            'intervalo' => 'nullable|numeric',
            'diaSemana' => 'nullable',
            'diaMes' => 'nullable',
            'semanaMes' => 'nullable|numeric',
            'mes' => 'nullable',
            'tipo' => 'required|numeric',
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        // Consultamos los permisos
        $usuarioRol = $this->traitGetMiUsuarioProyectoPorId($req);

        $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('PROGRAMADOR_TAREAS_CREAR', $usuarioRol->fk_rol);
        if (count($permiso) == 0) {
            return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
        }

        //creamos nueva instacia del modelo
        $modelo = new WbProgramadorTarea;

        //estabelcemos el proyecto y compañia
        $modelo = $this->traitSetProyecto($req, $modelo);



        $modelo->descripcion = $req->descripcion;
        $modelo->intervalo_semana = $req->intervalo ? trim($req->intervalo) : null;
        $modelo->dia_semana = $req->diaSemana ? trim($req->diaSemana) : null;
        $modelo->dia_mes = $req->diaMes ? trim($req->diaMes) : null;
        $modelo->semana_mes = $req->semanaMes ? trim($req->semanaMes) : null;
        $modelo->mes = $req->mes ? trim($req->mes) : null;
        $modelo->fk_id_usuarios_creacion = $usuarioRol->fk_usuario;
        $modelo->fecha_creacion = DB::raw('SYSDATETIME()');
        $modelo->estado = 1;
        $modelo->tipo = $req->tipo;

        if (!$modelo->save()) {
            return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
        }
        return $this->handleAlert(__('messages.registro_exitoso'), true);
    }

    /**
     * @param Request $req
     * @param $id
     * @return void
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $id
     * @return void
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function cambiarEstado(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'identificador' => 'required|numeric'
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            // Consultamos los permisos
            $usuarioRol = $this->traitGetMiUsuarioProyectoPorId($req);

            $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('PROGRAMADOR_TAREAS_CAMBIAR_ESTADOS', $usuarioRol->fk_rol);
            if (count($permiso) == 0) {
                return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
            }

            //Buscamos el registro por medio de su ID
            $modelo = WbProgramadorTarea::find($req->identificador);

            //En caso de no encontrarlo se devuelve el error
            if ($modelo == null) {
                return $this->handleAlert(__('messages.programacion_no_encontrada'), false);
            }

            //En caso de encontrarlo se cambia su estado a inactivo
            if ($modelo->estado == 0) {
                $modelo->estado = 1;
            } else {
                $modelo->estado = 0;
            }
            $modelo->fk_id_usuario_edicion = $usuarioRol->fk_usuario;
            $modelo->fecha_edicion = DB::raw('SYSDATETIME()');

            //Guardamos los cambios, en caso de error devolvemos el error
            if (!$modelo->save()) {
                return $this->handleAlert(__('messages.error_al_intentar_hacer_cambios'), false);
            }
            return $this->handleAlert(__('messages.registro_cambio_de_estado'), true);
        } catch (\Throwable $e) {
            //return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $req)
    {
        // TODO: Implement get() method.
        try {
            $query = WbProgramadorTarea::select(
                'id_programador_tareas',
                'descripcion',
                'intervalo_semana',
                'dia_semana',
                'dia_mes',
                'semana_mes',
                'mes',
                'tipo',
                'estado',
            );

            $query = $this->filtrarPorProyecto($req, $query)->orderBy('id_programador_tareas', 'DESC')->get();
            if (sizeof($query) == 0) {
                return $this->handleAlert(__('messages.sin_registros_por_mostrar'), false);
            }

            $query = $this->columnasExtras($query);

            return $this->handleResponse($req, $this->wbProgramadorTareaToArray($query), __('messages.consultado'));
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    private function columnasExtras($array)
    {
        if (sizeof($array) > 0) {
            foreach ($array as $key) {
                switch ($key['tipo']) {
                    case 1:
                        $key['tipoText'] = __('messages.programador_tarea_diario');
                        $key['programacion'] = $this->getDetalleProgramacionDiaria($key);
                        break;
                    case 2:
                        $key['tipoText'] = __('messages.programador_tarea_semanal');
                        $key['programacion'] = $this->getDetalleProgramacionSemana($key);
                        break;
                    case 3:
                        $key['tipoText'] = __('messages.programador_tarea_mensual');
                        $key['programacion'] = $this->getDetalleProgramacionMes($key);
                        break;
                }
            }
        }
        return $array;
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        return WbProgramadorTarea::where('estado', 1)->where('fk_id_project_Company', $proyecto)->get();
    }

    public function find($identificador)
    {
        return WbProgramadorTarea::find($identificador);
    }

    private function getDetalleProgramacionDiaria($object)
    {
        $cadenaDias = '';
        /* Verificamos si la cadena de dias contenga una ',' como separador */
        if (strpos($object->dia_semana, ',') !== false) {
            /* En dado caso de que tenga una ',' como delimitador entonces optenemos un array */
            $array = explode(',', $object->dia_semana);

            /* tomamos el array y ahora lo pasamos a la cadena de texto utilizando sus nombres correspondientes */
            $auxArray = array();
            foreach ($array as $item) {
                array_push($auxArray, $this->getDiaMesEnTexto($item));
            }
            $cadenaDias = trim(implode(',', $auxArray));
        } else {
            $cadenaDias = trim($this->getDiaMesEnTexto($object->dia_semana));
        }
        return 'Dia: ' . trim($cadenaDias);
    }

    private function getDetalleProgramacionSemana($object)
    {
        $cadenaDias = '';
        /* Verificamos si la cadena de dias contenga una ',' como separador */
        if (strpos($object->dia_semana, ',') !== false) {
            /* En dado caso de que tenga una ',' como delimitador entonces optenemos un array */
            $array = explode(',', $object->dia_semana);

            /* tomamos el array y ahora lo pasamos a la cadena de texto utilizando sus nombres correspondientes */
            $auxArray = array();
            foreach ($array as $item) {
                array_push($auxArray, $this->getDiaMesEnTexto($item));
            }
            $cadenaDias = trim(implode(',', $auxArray));
        } else {
            $cadenaDias = trim($this->getDiaMesEnTexto($object->dia_semana));
        }
        return 'Dia: ' . trim($cadenaDias) . ' ;Intervalo de semanas: ' . trim($object->intervalo_semana);
    }

    private function getDetalleProgramacionMes($object)
    {
        $cadenaDias = '';
        $cadenaMes = '';
        /* Verificamos si la cadena de dias del mes contenga una ',' como separador */
        if (strpos($object->dia_mes, ',') !== false) {
            /* En dado caso de que tenga una ',' como delimitador entonces optenemos un array */
            $array = explode(',', $object->dia_mes);

            /* tomamos el array y ahora lo pasamos a la cadena de texto utilizando sus nombres correspondientes */
            $auxArray = array();
            foreach ($array as $item) {
                array_push($auxArray, $this->getDiaMesEnTexto($item));
            }
            $cadenaDias = trim(implode(',', $auxArray));
        } else {
            if (empty($object->semana_mes)) {
                $cadenaDias = trim($object->dia_mes);
            } else {
                $cadenaDias = trim($this->getDiaMesEnTexto($object->dia_mes));
            }

        }

        if (strpos($object->mes, ',') !== false) {
            $array = explode(',', $object->mes);

            $auxArray = array();
            foreach ($array as $item) {
                array_push($auxArray, $this->getMesEnTexto($item));
            }
            $cadenaMes = trim(implode(',', $auxArray));
        } else {
            $cadenaMes = trim($this->getMesEnTexto($object->mes));
        }

        if (empty($object->semana_mes)) {
            return 'Dia: ' . trim($cadenaDias) . ' ;Mes: ' . trim($cadenaMes);
        }

        return 'Dia: ' . trim($cadenaDias) . ' ;Semana: ' . trim($object->semana_mes) . ' ;Mes: ' . trim($cadenaMes);
    }

    private function getDiaMesEnTexto($dia_mes)
    {
        $dia = '';
        switch ($dia_mes) {
            case 1:
                $dia = __('messages.lunes');
                break;
            case 2:
                $dia = __('messages.martes');
                break;
            case 3:
                $dia = __('messages.miercoles');
                break;
            case 4:
                $dia = __('messages.jueves');
                break;
            case 5:
                $dia = __('messages.viernes');
                break;
            case 6:
                $dia = __('messages.sabado');
                break;
            case 7:
                $dia = __('messages.domingo');
                break;
        }
        return $dia;
    }

    private function getMesEnTexto($mes)
    {
        switch ($mes) {
            case 1:
                $mes = __('messages.enero');
                break;
            case 2:
                $mes = __('messages.febrero');
                break;
            case 3:
                $mes = __('messages.marzo');
                break;
            case 4:
                $mes = __('messages.abril');
                break;
            case 5:
                $mes = __('messages.mayo');
                break;
            case 6:
                $mes = __('messages.junio');
                break;
            case 7:
                $mes = __('messages.julio');
                break;
            case 8:
                $mes = __('messages.agosto');
                break;
            case 9:
                $mes = __('messages.septiembre');
                break;
            case 10:
                $mes = __('messages.octubre');
                break;
            case 11:
                $mes = __('messages.noviembre');
                break;
            case 12:
                $mes = __('messages.diciembre');
                break;
        }
        return $mes;
    }
}