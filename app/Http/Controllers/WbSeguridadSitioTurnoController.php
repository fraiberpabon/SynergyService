<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbSeguridadSitioTurno;
use DB;
use Illuminate\Http\Request;
use Validator;

class WbSeguridadSitioTurnoController extends BaseController implements Vervos
{
    /* aplicacion movil */

    /**
     * Obtiene la información de los turnos registrados en un proyecto de seguridad en el sitio.
     *
     * @param  Request  $request  La solicitud HTTP.
     * @return mixed  La respuesta con la información de los turnos o un mensaje de alerta en caso de error.
     */
    public function getMovil(Request $request)
    {
        $proyecto = $this->traitGetProyectoCabecera($request);
        $turnos = WbSeguridadSitioTurno::where('fk_id_project_Company', $proyecto)->where('estado', 1)->get();

        if (count($turnos) == 0) {
            return $this->handleAlert(__('messages.no_tiene_turnos_registrados'), false);
        }
        return $this->handleResponse($request, $this->wbSeguridadSitioTurnoToArray($turnos), __('messages.consultado'));
    }
    /* fin aplicacion movil */

    /**
     * Maneja la creación de un nuevo turno en el contexto de seguridad en el sitio.
     *
     * @param  Request  $req  La solicitud HTTP.
     * @return mixed  La respuesta con un mensaje de éxito o error.
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method
        $validator = Validator::make($req->all(), [
            'turno' => 'required|string',
            'horas' => 'required|numeric',
            'hora_inicio' => 'required|string',
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        // Consultamos los permisos
        $usuarioRol = $this->traitGetMiUsuarioProyectoPorId($req);

        $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('GESTION_TURNO_SEGURIDAD_SITIO', $usuarioRol->fk_rol);
        if (count($permiso) == 0) {
            return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
        }

        //convertimos el texto a tiempo
        $hora_inicio = strtotime($req->hora_inicio);

        $hora_final = strtotime('+' . $req->horas . " hours", $hora_inicio);

        //calculamos la hora de finalizacion


        //convertimos la hora en formato de 24 horas
        $hora_inicio = date("H:i", $hora_inicio);
        $hora_final = date("H:i", $hora_final);

        //creamos nueva instacia del modelo
        $turno = new WbSeguridadSitioTurno;

        //estabelcemos el proyecto y compañia
        $turno = $this->traitSetProyectoYCompania($req, $turno);

        $usuario = $this->traitGetIdUsuarioToken($req);

        $turno->nombre_turno = $req->turno;
        $turno->horas_turno = $req->horas;
        $turno->hora_inicio_turno = $hora_inicio;
        $turno->hora_final_turno = $hora_final;
        $turno->estado = 1;
        $turno->usuario_creacion = $usuario;
        $turno->usuario_modificacion = $usuario;
        $turno->fecha_creacion = DB::raw('SYSDATETIME()');
        $turno->fecha_modificacion = DB::raw('SYSDATETIME()');

        if (!$turno->save()) {
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

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $req)
    {
        // TODO: Implement get() method.
        try {
            if (!is_numeric($req->page) || !is_numeric($req->limit)) {
                return $this->handleAlert(__('messages.datos_invalidos'), false);
            }

            $pagina = $req->page;
            $limite = $req->limit;
            $buscar = $req->ask;

            $query = WbSeguridadSitioTurno::select(
                'id_seguridad_sitio_turno',
                'nombre_turno',
                'horas_turno',
                'hora_inicio_turno',
                'hora_final_turno',
                //DB::raw("CONVERT(VARCHAR, CAST(hora_inicio_turno AS TIME), 100) AS hora_inicio_turno"),
                //DB::raw("CONVERT(VARCHAR, CAST(hora_final_turno AS TIME), 100) AS hora_final_turno"),
                'estado',
            )->where('estado', 1);

            if (($buscar !== null && strlen($buscar) > 0)) {
                if (is_numeric($buscar)) {
                    $query = $query->where('horas_turno', $buscar);
                } else {
                    $query = $query->where('nombre_turno', 'like', '%' . $buscar . '%');
                }
            }

            $query = $this->filtrar($req, $query)->orderBy('id_seguridad_sitio_turno', 'ASC');
            if (sizeof($query->get()) == 0) {
                return $this->handleAlert(__('messages.sin_registros_por_mostrar'), false);
            }

            $limitePagina = 1;
            $contador = clone $query;
            $contador = $contador->select('id_seguridad_sitio_turno')->count();
            $query = $query->forPage($pagina, $limite)->get();
            $limitePagina = ($contador / $limite);
            if ($limitePagina <= 1) {
                $limitePagina = $limitePagina = 1;
            }

            return $this->handleResponse($req, $this->wbSeguridadSitioTurnoToArray($query), __('messages.consultado'), ceil($limitePagina));
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    /**
     * Maneja la actualización de un turno en el contexto de seguridad en el sitio.
     *
     * @param  Request  $req  La solicitud HTTP.
     * @return mixed  La respuesta con un mensaje de éxito o error.
     */
    public function actualizar(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'identificador' => 'required|numeric',
            'turno' => 'required|string',
            'horas' => 'required|numeric',
            'hora_inicio' => 'required|string',
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        // Consultamos los permisos
        $usuarioRol = $this->traitGetMiUsuarioProyectoPorId($req);

        $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('GESTION_TURNO_SEGURIDAD_SITIO', $usuarioRol->fk_rol);
        if (count($permiso) == 0) {
            return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
        }


        $turno = WbSeguridadSitioTurno::where('id_seguridad_sitio_turno', $req->identificador)->first();

        if ($turno == null) {
            return $this->handleAlert(__('messages.turno_no_encontrado'), false);
        }


        $usuario = $this->traitGetIdUsuarioToken($req);

        $turno->estado = 0;
        $turno->usuario_modificacion = $usuario;
        if (!$turno->save()) {
            return $this->handleAlert(__('messages.error_al_intentar_hacer_cambios'), false);
        }


        //convertimos el texto a tiempo
        $hora_inicio = strtotime($req->hora_inicio);

        //calculamos la hora de finalizacion
        $hora_final = strtotime('+' . $req->horas . " hours", $hora_inicio);

        //convertimos la hora en formato de 24 horas
        $hora_inicio = date("H:i", $hora_inicio);
        $hora_final = date("H:i", $hora_final);

        $nuevo = new WbSeguridadSitioTurno;


        $nuevo = $this->traitSetProyectoYCompania($req, $nuevo);

        $nuevo->nombre_turno = $req->turno;
        $nuevo->horas_turno = $req->horas;
        $nuevo->hora_inicio_turno = $hora_inicio;
        $nuevo->hora_final_turno = $hora_final;
        $nuevo->estado = 1;
        $nuevo->usuario_creacion = $usuario;
        $nuevo->usuario_modificacion = $usuario;
        $nuevo->fecha_creacion = DB::raw('SYSDATETIME()');
        $nuevo->fecha_modificacion = DB::raw('SYSDATETIME()');

        if (!$nuevo->save()) {
            return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
        }
        return $this->handleAlert(__('messages.registro_exitoso'), true);
    }

    /**
     * Desactiva un turno en el contexto de seguridad en el sitio.
     *
     * @param  Request  $req  La solicitud HTTP.
     * @return mixed  La respuesta con un mensaje de éxito o error.
     */
    public function eliminar(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'identificador' => 'required|numeric',
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors(), false);
        }

        // Consultamos los permisos
        $usuarioRol = $this->traitGetMiUsuarioProyectoPorId($req);

        $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('GESTION_TURNO_SEGURIDAD_SITIO', $usuarioRol->fk_rol);
        if (count($permiso) == 0) {
            return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
        }

        $turno = WbSeguridadSitioTurno::find($req->identificador);

        if ($turno == null) {
            return $this->handleAlert(__('messages.turno_no_encontrado'), false);
        }

        $usuario = $this->traitGetIdUsuarioToken($req);

        $turno->estado = 0;
        $turno->usuario_modificacion = $usuario;
        if (!$turno->save()) {
            return $this->handleAlert(__('messages.error_al_intentar_hacer_cambios'), false);
        }
        return $this->handleAlert(__('messages.registro_eliminado'), true);
    }
}