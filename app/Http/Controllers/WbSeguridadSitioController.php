<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Mail\notificacionesSeguridadSitio;
use App\Models\usuarios_M;
use App\Models\WbEquipo;
use App\Models\WbHitosAbcisas;
use App\Models\WbSeguridadSitio;
use App\Models\WbSeguridadSitioEquipo;
use App\Models\WbSeguridadSitioEvidencia;
use App\Models\WbSeguridadSitioMaterial;
use App\Models\WbSeguridadSitioTurno;
use App\Models\WbTramos;
use DateTime;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mail;
use Str;
use Validator;

class WbSeguridadSitioController extends BaseController implements Vervos
{
    //prueba
    private $estados = array('Pendiente' => 12, 'Rechazado' => 13, 'Anulado' => 14, 'Aprobado' => 27, 'Finalizado' => 30, 'EnProceso' => 29);

    //historial y log de eventos
    private WbSeguridadSitioHistorialController $historial_y_log;

    /*inicio aplicacion movil*/

    /**
     * 
     * 
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request)
    {
        // TODO: Implement post() method

        try {
            $validator = Validator::make($request->all(), [
                'idTurno' => 'required|numeric',
                'fechaInicio' => 'required|date_format:Y-m-d',
                'fechaFinalizacion' => 'required|date_format:Y-m-d',
                'idTramo' => 'nullable|string',
                'idHito' => 'nullable|string',
                'abscisa' => 'nullable|string',
                'otra_ubicacion' => 'nullable|string',
                'coordenadas' => 'nullable|string',
                'maquinarias' => 'nullable',
                'materiales' => 'nullable',
                'observaciones' => 'nullable',
                'ubicacion' => 'required',
                'en_sitio' => 'required|numeric',
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }



            $observaciones = null;
            if (!is_null($request->observaciones)) {
                $observaciones = $request->observaciones;
            }

            //creamos nueva instacia del modelo
            $seguridadSitio = new WbSeguridadSitio;

            //estabelcemos el proyecto y compañia
            $seguridadSitio = $this->traitSetProyectoYCompania($request, $seguridadSitio);

            //asignamos los valores al modelo
            $usuario = $this->traitGetIdUsuarioToken($request);

            //preguntamos por el ultimo registro del proyecto
            $registro = WbSeguridadSitio::select('id_registro_proyecto');
            //->where('fk_id_project_Company', $seguridadSitio->fk_id_project_Company)->where('fk_compañia', $seguridadSitio->fk_compañia)
            $registro = $this->filtrar($request, $registro)->orderBy('id_registro_proyecto', 'DESC')->limit(1)->first();

            if ($registro != null) {
                $registro = $registro->id_registro_proyecto;
                $seguridadSitio->id_registro_proyecto = $registro + 1;
            } else {
                $seguridadSitio->id_registro_proyecto = 1;
            }
            $seguridadSitio->fecha_inicio = $request->fechaInicio;
            $seguridadSitio->fecha_finalizacion = $request->fechaFinalizacion;

            //consultamos si es un frente de obra
            if ($request->idTramo != null) {
                if (!$this->turnoValido($request->idTurno)) {
                    return $this->handleAlert(__('messages.turno_no_valido'));
                }

                if (!$this->tramoValido($request->idTramo)) {
                    return $this->handleAlert(__('messages.tramo_no_valido'));
                }

                if (!$this->hitoValido($request->idHito)) {
                    return $this->handleAlert(__('messages.hito_no_valido'));
                }

                //guardamos tramo e hito encontrado
                $seguridadSitio->fk_id_tramo = $request->idTramo;
                $seguridadSitio->fk_id_hito = $request->idHito;
                $seguridadSitio->abscisa = $request->abscisa;
            } else if ($request->otra_ubicacion != null) {
                $seguridadSitio->otra_ubicacion = $request->otra_ubicacion;
                $seguridadSitio->coordenadas_de_solicitud = $request->coordenadas;
            }

            //buscamos el id del turno
            $turno = WbSeguridadSitioTurno::select('id_seguridad_sitio_turno')->where('id_seguridad_sitio_turno', $request->idTurno);
            $turno = $this->filtrar($request, $turno)->first();
            if ($turno == null) {
                return $this->handleAlert(__('messages.turno_no_encontrado'));
            }
            $seguridadSitio->fk_id_turno_seguridad_sitio = $turno->id_seguridad_sitio_turno;

            if ($this->fechaIgualAlDiaDeHoy($request->fechaInicio)) {

                $userCompare = $this->traitGetMiUsuarioProyectoPorId($request);

                $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('CALIFICAR_SEGURIDAD_SITIO', $userCompare->fk_rol);
                if (count($permiso) == 0) {
                    return $this->handleAlert(__('messages.debe_registrar_solicitudes_maximo_un_dia_antes'), false);
                }
            }

            if ($request->en_sitio == 1) {
                $seguridadSitio->coordenadas_de_solicitud = $request->ubicacion;
            } else if ($request->en_sitio == 0 && $request->idHito != null) {
                /* separamos los datos de la abscisa para que sea solo numerico */
                $abscisa = explode('+', $request->abscisa);
                $abscisa = substr($abscisa[0], '1') . $abscisa[1];
                $abscisaController = new WbAbscisasController();
                $seguridadSitio->coordenadas_de_solicitud = $abscisaController->getPromedioEntreAbscisas($request->idHito, $seguridadSitio->fk_id_project_Company, $abscisa);
            }



            /* terminamos de capturar los datos que van a persistir en  base de datos */
            $seguridadSitio->observaciones = $observaciones;
            $seguridadSitio->ubicacion = $request->ubicacion;
            $seguridadSitio->en_sitio = $request->en_sitio;
            $seguridadSitio->usuario_creacion = $usuario;
            $seguridadSitio->fecha_creacion = DB::raw('SYSDATETIME()');
            $seguridadSitio->fecha_modificacion = DB::raw('SYSDATETIME()');
            $seguridadSitio->fk_id_estado = $this->estados['Pendiente'];


            /* si la solicitud se guarda sin problemas entonces registramos sus equipos y materiales */
            if ($seguridadSitio->save()) {

                try {
                    $error = 0;
                    $maquinarias = json_decode($request->maquinarias, true);
                    if ($maquinarias != null && is_array($maquinarias)) {
                        foreach ($maquinarias as $key) {
                            $equipos = new WbSeguridadSitioEquipo();
                            $equipos->fk_id_seguridad_sitio = $seguridadSitio->id_seguridad_sitio;
                            $query = WbEquipo::select('id')->where('equiment_id', $key['EquipmentID'])->where('estado', 'A');
                            $query = $this->filtrar($request, $query)->first();
                            if ($query != null) {
                                $equipos->fk_id_equipo = $query->id;
                            }
                            $equipos->es_inicial = 1;
                            $equipos->fecha_registro = DB::raw("GETDATE()");
                            if (!$equipos->save()) {
                                $error++;
                            }
                        }
                    }

                    $materiales = json_decode($request->materiales, true);
                    if ($materiales != null && is_array($materiales)) {
                        foreach ($materiales as $key) {
                            $material = new WbSeguridadSitioMaterial();
                            $material->fk_id_seguridad_sitio = $seguridadSitio->id_seguridad_sitio;
                            $material->material = $key['material'];
                            $material->cantidad = $key['cantidad'];
                            $material->unidad_medida = $key['unidadMedida'];
                            $material->es_inicial = 1;
                            $material->fecha_registro = DB::raw("GETDATE()");
                            if (!$material->save()) {
                                $error++;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    /* en caso de error al crear la solicitud la eliminamos
                    y el resto de registros se eliminan por la funcion de la llave foranea
                    eliminacion en cascada */
                    $error++;
                    $registroFallido = WbSeguridadSitio::find($seguridadSitio->id_seguridad_sitio);
                    $registroFallido->delete();
                }

                if ($error == 0) {
                    $this->enviarCorreoCalificadores($seguridadSitio->id_seguridad_sitio, __('messages.nueva_solicitud'));
                    $this->enviarCorreoSolicitante($seguridadSitio->id_seguridad_sitio, __('messages.solicitud_creada'));

                    $this->registrarHistorial_o_log($seguridadSitio->id_seguridad_sitio, 1, __('messages.solicitud'), __('messages.se_creo_solicitud') . ' ' . $seguridadSitio->id_registro_proyecto, null, $usuario);

                    return $this->handleResponse($request, $seguridadSitio->id_registro_proyecto, __('messages.registro_exitoso'));
                }
                return $this->handleAlert(__('messages.error_al_registrar_maquinarias_y_materiales'), false);
            }
            return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
        } catch (\Throwable $e) {
            //return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            return $this->handleAlert($e->getMessage(), false);
        }

    }

    /**
     * Registra un evento en el historial o en los logs del sistema.
     *
     * @param int|null $fk_id_seguridad_sitio - ID relacionado con la seguridad del sitio.
     * @param bool $is_log - Indica si es un registro de log (true) o historial (false).
     * @param string $evento - Tipo de evento registrado.
     * @param string $contenido - Contenido o descripción del evento.
     * @param int|null $id_evidencia - ID de la evidencia relacionada (opcional).
     * @param int|null $id_usuario_evento - ID del usuario relacionado con el evento (opcional).
     */
    private function registrarHistorial_o_log($fk_id_seguridad_sitio = null, $is_log, $evento, $contenido, $id_evidencia = null, $id_usuario_evento = null)
    {
        $this->historial_y_log = new WbSeguridadSitioHistorialController();
        $log = $this->historial_y_log->crearRegistro(
            $fk_id_seguridad_sitio,
            $is_log,
            $evento,
            $contenido,
            $id_evidencia,
            $id_usuario_evento
        );

        $this->historial_y_log->guardar($log);
    }

    /**
     * Sube evidencias para una solicitud de seguridad en el sitio.
     *
     * @param Request $request - La solicitud HTTP con los datos de las evidencias.
     *
     * @return JsonResponse - Una respuesta JSON indicando el resultado de la operación.
     */
    public function subirEvidencias(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'identificador' => 'required|numeric',
                'evidencia1' => 'required|string',
                'evidencia2' => 'nullable|string',
                'observaciones' => 'nullable|string',
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $usuario = $this->traitGetIdUsuarioToken($request);

            //consultamos la solicitud de la cual quieren subir evidencias
            $query = WbSeguridadSitio::select('id_seguridad_sitio', 'fk_id_project_Company')->where('id_registro_proyecto', $request->identificador);
            $query = $this->filtrar($request, $query)->first();
            if ($query == null) {
                return $this->handleAlert('messages.solicitud_no_encontrada');
            }

            //cargamos las evidencias
            $result = WbSeguridadSitioEvidencia::where('fk_id_seguridad_sitio', $query->id_seguridad_sitio)->orderBy('fecha_registro', 'desc')->first();
            if ($result != null) {
                if ($this->fechaIgualAlDiaDeHoy($result->fecha_registro)) {
                    $result->estado = 0;
                    if ($result->save()) {
                        $evidencia = new WbSeguridadSitioEvidencia();
                        $evidencia->fk_id_seguridad_sitio = $query->id_seguridad_sitio;
                        $evidencia->evidencia1 = $request->evidencia1;
                        $evidencia->evidencia2 = $request->evidencia2;
                        $evidencia->observaciones = $request->observaciones;
                        $evidencia->estado = 1;
                        $evidencia->tipo = 1;
                        $evidencia->fecha_registro = DB::raw("GETDATE()");
                        if ($evidencia->save()) {
                            $this->registrarHistorial_o_log($evidencia->fk_id_seguridad_sitio, 1, __('messages.evidencias'), __('messages.se_actualizaron_evidencias'), $evidencia->id_seguridad_sitio_evidencia, $usuario);
                            return $this->handleAlert(__('messages.registro_exitoso'), true);
                        }
                    }
                    $this->registrarHistorial_o_log($result->fk_id_seguridad_sitio, 1, __('messages.evidencias'), __('messages.error_al_deshabilitar_evidencias_para_ingresar_nuevas'), $result->id_seguridad_sitio_evidencia, $usuario);
                    return $this->handleAlert(__('messages.fallo_registro'), false);
                }
            }
            $evidencia = new WbSeguridadSitioEvidencia();
            $evidencia->fk_id_seguridad_sitio = $query->id_seguridad_sitio;
            $evidencia->evidencia1 = $request->evidencia1;
            $evidencia->evidencia2 = $request->evidencia2;
            $evidencia->observaciones = $request->observaciones;
            $evidencia->estado = 1;
            $evidencia->tipo = 1;
            $evidencia->fecha_registro = DB::raw("GETDATE()");
            if ($evidencia->save()) {
                $this->registrarHistorial_o_log($evidencia->fk_id_seguridad_sitio, 1, __('messages.evidencias'), __('messages.se_agregaron_evidencias'), $evidencia->id_seguridad_sitio_evidencia, $usuario);
                return $this->handleAlert(__('messages.registro_exitoso'), true);
            }
            $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.evidencias'), __('messages.no_se_pudo_registrar_evidencias_en_la_solicitud'), null, $usuario);
            return $this->handleAlert(__('messages.fallo_registro'), false);
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Actualiza los elementos relacionados con la seguridad en el sitio para una solicitud.
     *
     * @param Request $request - La solicitud HTTP con los datos de actualización.
     *
     * @return JsonResponse - Una respuesta JSON indicando el resultado de la operación.
     */
    public function actualizarElementos(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'identificador' => 'required|numeric',
                'maquinarias' => 'nullable',
                'materiales' => 'nullable',
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }
            if ($request->maquinarias == null && $request->materiales == null) {
                return $this->handleAlert(__('messages.no_hay_elementos_por_actualizar'), false);
            }


            $seguridad = WbSeguridadSitio::where('id_registro_proyecto', $request->identificador);
            $seguridad = $this->filtrar($request, $seguridad)->first();
            if ($seguridad == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            $error = 0;

            //preguntamos si hay equipos por registrar
            $maquinarias = json_decode($request->maquinarias, true);
            if (!$this->actualizarEquipos($maquinarias, $seguridad, $request)) {
                $error++;
            }


            //preguntamos si hay materiales por registrar
            $materiales = json_decode($request->materiales, true);
            if (!$this->actualizarMateriales($materiales, $seguridad, $request)) {
                $error++;
            }

            if ($error == 0) {
                return $this->handleAlert(__('messages.registro_exitoso'), true);
            }
            return $this->handleAlert(__('messages.fallo_registro'), false);
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Actualiza los equipos relacionados con la seguridad en el sitio para una solicitud.
     *
     * @param array|null $maquinarias - Lista de maquinarias a actualizar.
     * @param WbSeguridadSitio $seguridad - Objeto que representa la seguridad en el sitio.
     * @param Request $request - La solicitud HTTP con los datos de actualización.
     *
     * @return bool - Indica si la actualización de equipos fue exitosa o no.
     */
    private function actualizarEquipos($maquinarias, $seguridad, Request $request)
    {
        if ($maquinarias != null && is_array($maquinarias)) {

            $usuario = $this->traitGetIdUsuarioToken($request);

            //buscamos fecha de los ultimos equipos registrados que no sean los iniciales
            $result = WbSeguridadSitioEquipo::select('fecha_registro')
                ->where('fk_id_seguridad_sitio', $seguridad->id_seguridad_sitio)
                ->where('fecha_registro', '!=', null)
                ->where('es_inicial', 0)
                ->orderBy('fecha_registro', 'desc')->first();

            $equiposAnt = null;
            if ($result != null) {
                //en caso de encontrar fecha de equipos, comprobamos si las fechas son compatibles
                $equiposAnt = WbSeguridadSitioEquipo::where('fk_id_seguridad_sitio', $seguridad->id_seguridad_sitio)
                    ->where('fecha_registro', $result->fecha_registro)->first();
            }

            //inicializamos indicador de errores
            $error = 0;

            //en caso de no encontrar equipos, registramos como nuevos equipos 
            if ($result == null && $equiposAnt == null) {
                foreach ($maquinarias as $key) {

                    //buscamos el identificador unico del equipo
                    $query = WbEquipo::select('id')->where('equiment_id', $key['EquipmentID'])->where('estado', 'A');
                    $query = $this->filtrar($request, $query)->first();
                    //si no encontramos el id unico del equipo quiere decir que no esta activo o en su defecto no existe
                    if ($query == null) {
                        $error++;
                        $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.maquinarias'), __('messages.error_al_registrar_equipo') . ' ' . $key['EquipmentID'] . ' ' . __('messages.no_existe_o_inactivo'), null, $usuario);
                        continue;
                    }

                    $existe = WbSeguridadSitioEquipo::where('fk_id_seguridad_sitio', $seguridad->id_seguridad_sitio)
                        ->where('fk_id_equipo', $query->id)
                        ->where('es_inicial', 1)
                        ->orderBy('fecha_registro', 'desc')
                        ->first();

                    if ($existe != null) {
                        //guardamos el nuevo registro
                        $equipos = new WbSeguridadSitioEquipo();
                        $equipos->fk_id_seguridad_sitio = $seguridad->id_seguridad_sitio;
                        $equipos->fk_id_equipo = $query->id;
                        $equipos->es_inicial = 0;
                        $equipos->fecha_registro = DB::raw("GETDATE()");
                        if (!$equipos->save()) {
                            $error++;
                        }
                        $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.maquinarias'), __('messages.se_agrego_el_equipo') . ' ' . $key['EquipmentID'] . ' ' . __('messages.en_la_solicitud'), null, $usuario);
                        continue;
                    }

                    //guardamos el nuevo registro
                    $equipos = new WbSeguridadSitioEquipo();
                    $equipos->fk_id_seguridad_sitio = $seguridad->id_seguridad_sitio;
                    $equipos->fk_id_equipo = $query->id;
                    $equipos->es_inicial = 0;
                    $equipos->fecha_registro = DB::raw("GETDATE()");
                    if (!$equipos->save()) {
                        $error++;
                    }
                    //$this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, 'Maquinarias', 'Se agrego el equipo ' . $key['EquipmentID'] . ' en la solicitud', null, $usuario);
                }

                //ahora vamos eliminando los equipos que no estan en la lista de maquinaras(equipos) ha registrar
                $equiposAnt = WbSeguridadSitioEquipo::select('Wb_Seguridad_Sitio_Equipo.id_seguridad_sitio_equipo', 'Wb_Seguridad_Sitio_Equipo.fk_id_equipo', 'Wb_equipos.equiment_id')
                    ->where('fk_id_seguridad_sitio', $seguridad->id_seguridad_sitio)
                    ->where('fecha_registro', DB::raw('CAST(GETDATE() AS DATE)'))
                    ->where('es_inicial', 0)
                    ->join('Wb_equipos', 'Wb_equipos.id', 'Wb_Seguridad_Sitio_Equipo.fk_id_equipo')
                    ->get();
                if (sizeof($equiposAnt) > 0) {
                    foreach ($equiposAnt as $keyEqui) {
                        $encontro = 0;
                        foreach ($maquinarias as $keyMaqui) {
                            if ($keyEqui['equiment_id'] == $keyMaqui['EquipmentID']) {
                                $encontro++;
                            }
                        }
                        if ($encontro == 0) {
                            WbSeguridadSitioEquipo::where('id_seguridad_sitio_equipo', $keyEqui['id_seguridad_sitio_equipo'])->delete();
                            $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.maquinarias'), __('messages.se_elimino_el_equipo') . ' ' . $keyEqui['equiment_id'] . ' ' . __('messages.de_la_solicitud'), null, $usuario);
                        }
                    }
                }

                if ($error > 0) {
                    $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.maquinarias'), __('messages.se_agregaron_algunas_maquinarias'), null, $usuario);
                    return false;
                }
                //$this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, 'Maquinarias', 'Se agregaron maquinarias', null, $usuario);
                return true;
            }

            //en caso de encontrar equipos hacemos las modificaciones
            foreach ($maquinarias as $key) {

                //buscamos el identificador unico del equipo
                $identificador = WbEquipo::select('id')->where('equiment_id', $key['EquipmentID'])->where('estado', 'A');
                $identificador = $this->filtrar($request, $identificador)->first();
                //si no encontramos el id unico del equipo quiere decir que no esta activo o en su defecto no existe
                if ($identificador == null) {
                    $error++;
                    $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.maquinarias'), __('messages.error_al_registrar_equipo') . ' ' . $key['EquipmentID'] . ' ' . __('messages.no_existe_o_inactivo'), null, $usuario);
                    continue;
                }


                $existe = WbSeguridadSitioEquipo::where('fk_id_seguridad_sitio', $seguridad->id_seguridad_sitio)
                    ->where('fk_id_equipo', $identificador->id)
                    ->where('es_inicial', 0)
                    ->orderBy('fecha_registro', 'desc')
                    ->first();
                if ($existe != null) {
                    if ($this->fechaIgualAlDiaDeHoy($existe->fecha_registro)) {
                        continue;
                    }
                }

                //guardamos el nuevo registro
                $equipos = new WbSeguridadSitioEquipo();
                $equipos->fk_id_seguridad_sitio = $seguridad->id_seguridad_sitio;
                $equipos->fk_id_equipo = $identificador->id;
                $equipos->es_inicial = 0;
                $equipos->fecha_registro = DB::raw("GETDATE()");
                if ($equipos->save()) {
                    $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.maquinarias'), __('messages.se_agrego_el_equipo') . ' ' . $key['EquipmentID'] . ' ' . __('messages.en_la_solicitud'), null, $usuario);
                } else {
                    $error++;
                }

            }

            //ahora vamos eliminando los equipos que no estan en la lista de maquinaras(equipos) ha registrar
            $equiposAnt = WbSeguridadSitioEquipo::select('Wb_Seguridad_Sitio_Equipo.id_seguridad_sitio_equipo', 'Wb_Seguridad_Sitio_Equipo.fk_id_equipo', 'Wb_equipos.equiment_id')
                ->where('fk_id_seguridad_sitio', $seguridad->id_seguridad_sitio)
                ->where('fecha_registro', $result->fecha_registro)
                ->where('es_inicial', 0)
                ->join('Wb_equipos', 'Wb_equipos.id', 'Wb_Seguridad_Sitio_Equipo.fk_id_equipo')
                ->get();
            if (sizeof($equiposAnt) > 0) {
                foreach ($equiposAnt as $keyEqui) {
                    $encontro = 0;
                    foreach ($maquinarias as $keyMaqui) {
                        if ($keyEqui['equiment_id'] == $keyMaqui['EquipmentID']) {
                            $encontro++;
                        }
                    }
                    if ($encontro == 0) {
                        WbSeguridadSitioEquipo::where('id_seguridad_sitio_equipo', $keyEqui['id_seguridad_sitio_equipo'])->delete();
                        $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.maquinarias'), __('messages.se_elimino_el_equipo') . ' ' . $keyEqui['equiment_id'] . ' ' . __('messages.de_la_solicitud'), null, $usuario);
                    }
                }
            }

            if ($error > 0) {
                $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.maquinarias'), __('messages.se_agregaron_algunas_maquinarias'), null, $usuario);
                return false;
            }
            return true;
        }
        return true;
    }

    /**
     * Actualiza los materiales relacionados con la seguridad en el sitio para una solicitud.
     *
     * @param array|null $materiales - Lista de materiales a actualizar.
     * @param WbSeguridadSitio $seguridad - Objeto que representa la seguridad en el sitio.
     * @param Request $request - La solicitud HTTP con los datos de actualización.
     *
     * @return bool - Indica si la actualización de materiales fue exitosa o no.
     */
    private function actualizarMateriales($materiales, $seguridad, Request $request)
    {
        if ($materiales != null && is_array($materiales)) {

            $usuario = $this->traitGetIdUsuarioToken($request);

            //buscamos los materiales
            $result = WbSeguridadSitioMaterial::select('fecha_registro')
                ->where('fk_id_seguridad_sitio', $seguridad->id_seguridad_sitio)
                ->where('fecha_registro', '!=', null)
                ->where('es_inicial', 0)
                ->orderBy('fecha_registro', 'desc')->first();

            $materialAnt = null;
            if ($result != null) {
                //en caso de encontrar fecha de materiales, comprobamos si las fechas son compatibles
                $materialAnt = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $seguridad->id_seguridad_sitio)
                    ->where('fecha_registro', $result->fecha_registro)->first();
                /* if ($result != null) {
                    if (sizeof($result) > 0) {
                        WbSeguridadSitioMaterial::whereIn('id_seguridad_sitio_material', $result->toArray())->delete();
                    }
                } */

            }

            //inicializamos indicador de errores
            $error = 0;

            //en caso de no encontrar materiales, registramos como nuevos materiales 
            if ($result == null && $materialAnt == null) {
                foreach ($materiales as $key) {
                    //si el identificador no existe, quiere decir que viene un nuevo registro
                    if (!array_key_exists('identificador', $key)) {
                        $material = new WbSeguridadSitioMaterial();
                        $material->fk_id_seguridad_sitio = $seguridad->id_seguridad_sitio;
                        $material->material = $key['material'];
                        $material->cantidad = $key['cantidad'];
                        $material->unidad_medida = $key['unidadMedida'];
                        $material->fecha_registro = DB::raw("GETDATE()");
                        $material->es_inicial = 0;
                        if ($material->save()) {
                            $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.materiales'), __('messages.se_agrego_el_material') . ' ' . $key['material'] . ' ' . __('messages.en_la_solicitud'), null, $usuario);
                        } else {
                            $error++;
                        }
                    } else {
                        //en caso de no tener identificador nulo buscamos el registro
                        $existe = WbSeguridadSitioMaterial::where('id_seguridad_sitio_material', $key['identificador'])
                            ->first();

                        /* verificamos si no encontro la informacion para notificarlo como error
                        ya que esta ingresando un identificador que no existe */
                        if ($existe != null) {
                            $material = new WbSeguridadSitioMaterial();
                            $material->fk_id_seguridad_sitio = $seguridad->id_seguridad_sitio;
                            $material->material = $key['material'];
                            $material->cantidad = $key['cantidad'];
                            $material->unidad_medida = $key['unidadMedida'];
                            $material->fecha_registro = DB::raw("GETDATE()");
                            $material->es_inicial = 0;
                            if (!$material->save()) {
                                $error++;
                            }
                        } else {
                            $error++;
                        }
                    }
                }

                if ($error > 0) {
                    $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.materiales'), __('messages.se_agregaron_algunos_materiales'), null, $usuario);
                    return false;
                }
                //$this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, 'Materiales', 'Se agregaron materiales', null, $usuario);
                return true;
            }

            //en caso de encontrar materiales hacemos las modificaciones
            foreach ($materiales as $key) {
                //si el identificador no existe, quiere decir que viene un nuevo registro
                if (!isset($key['identificador'])) {
                    $material = new WbSeguridadSitioMaterial();
                    $material->fk_id_seguridad_sitio = $seguridad->id_seguridad_sitio;
                    $material->material = $key['material'];
                    $material->cantidad = $key['cantidad'];
                    $material->unidad_medida = $key['unidadMedida'];
                    $material->fecha_registro = DB::raw("GETDATE()");
                    $material->es_inicial = 0;
                    if (!$material->save()) {
                        $error++;
                        continue;
                    }
                    $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.materiales'), __('messages.se_agrego_el_material') . ' ' . $key['material'] . ' ' . __('messages.en_la_solicitud'), null, $usuario);
                    continue;
                }

                //en caso de no tener identificador nulo buscamos el registro
                $existe = WbSeguridadSitioMaterial::where('id_seguridad_sitio_material', $key['identificador'])
                    ->first();
                /* verificamos si no encontro la informacion para notificarlo como error
                ya que esta ingresando un identificador que no existe */
                if ($existe == null) {
                    $error++;
                    continue;
                }

                /* en dado caso de que si es un identificador valido verificamos validamos su fecha
                si al fecha no es del dia de hoy entonces se registra como nuevo */
                if (!$this->fechaIgualAlDiaDeHoy($existe->fecha_registro)) {
                    $material = new WbSeguridadSitioMaterial();
                    $material->fk_id_seguridad_sitio = $seguridad->id_seguridad_sitio;
                    $material->material = $key['material'];
                    $material->cantidad = $key['cantidad'];
                    $material->unidad_medida = $key['unidadMedida'];
                    $material->fecha_registro = DB::raw("GETDATE()");
                    $material->es_inicial = 0;
                    if (!$material->save()) {
                        $error++;
                        continue;
                    }
                    $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.materiales'), __('messages.se_agrego_el_material') . ' ' . $key['material'] . ' ' . __('messages.en_la_solicitud'), null, $usuario);
                    continue;
                } else {
                    /* si la fecha es del dia de hoy verificamos si es inicial
                    en ese caso entonces registramos como nuevo*/
                    if ($existe->es_inicial == 1) {
                        $material = new WbSeguridadSitioMaterial();
                        $material->fk_id_seguridad_sitio = $seguridad->id_seguridad_sitio;
                        $material->material = $key['material'];
                        $material->cantidad = $key['cantidad'];
                        $material->unidad_medida = $key['unidadMedida'];
                        $material->fecha_registro = DB::raw("GETDATE()");
                        $material->es_inicial = 0;
                        if (!$material->save()) {
                            $error++;
                            continue;
                        }
                        $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.materiales'), __('messages.se_agrego_el_material') . ' ' . $key['material'] . ' ' . __('messages.en_la_solicitud'), null, $usuario);
                        continue;
                    }
                }

                /* si la fecha es igual al dia de hoy entonces verificamos sus cambios */
                $cambios = 0;
                if (strcasecmp($existe->cantidad, $key['cantidad']) != 0) {
                    $existe->cantidad = $key['cantidad'];
                    $cambios++;
                }

                if (strcasecmp($existe->unidad_medida, $key['unidadMedida']) != 0) {
                    $existe->unidad_medida = $key['unidadMedida'];
                    $cambios++;
                }

                if ($cambios > 0) {
                    $existe->fecha_registro = DB::raw("GETDATE()");
                    if (!$existe->save()) {
                        $error++;
                        continue;
                    }
                    $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.materiales'), __('messages.material') . ' ' . $key['material'] . ' ' . __('messages.actualizado_cambio') . ' ' . $key['cantidad'] . ' ' . $key['unidadMedida'], null, $usuario);
                    continue;
                }
            }

            /* ahora vamos a eliminar los materiales que no estan en la lista de materiales a persistir */
            $materialAnt = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $seguridad->id_seguridad_sitio)
                ->where('es_inicial', 0)
                ->where('fecha_registro', $result->fecha_registro)
                ->get();
            if (sizeof($materialAnt) > 0) {
                foreach ($materialAnt as $keyAnt) {
                    $encontro = 0;
                    foreach ($materiales as $keyMate) {
                        if (!isset($keyMate['identificador'])) {
                            if ($keyAnt['material'] == $keyMate['material']) {
                                $encontro++;
                            }
                            continue;
                        }
                        if ($keyAnt['id_seguridad_sitio_material'] == $keyMate['identificador']) {
                            $encontro++;
                        }
                    }

                    if ($encontro == 0) {
                        WbSeguridadSitioMaterial::where('id_seguridad_sitio_material', $keyAnt['id_seguridad_sitio_material'])->delete();
                        $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.materiales'), __('messages.se_elimino_el_material') . ' ' . $keyAnt['material'] . ' ' . __('messages.de_la_solicitud'), null, $usuario);
                    }
                }
            }

            if ($error > 0) {
                $this->registrarHistorial_o_log($seguridad->id_seguridad_sitio, 1, __('messages.materiales'), __('messages.se_agregaron_algunos_materiales'), null, $usuario);
                return false;
            }
            return true;

        }
        return true;
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
     * @return JsonResponse
     */
    public function get(Request $request)
    {
        // TODO: Implement get() method.
        try {
            $query = WbSeguridadSitio::select(
                'id_seguridad_sitio',
                'id_registro_proyecto',
                'fk_id_turno_seguridad_sitio',
                'fecha_inicio',
                'fecha_finalizacion',
                'fk_id_tramo',
                'fk_id_hito',
                'abscisa',
                'otra_ubicacion',
                'observaciones',
                'fk_id_estado',
                'usuario_creacion',
                'fecha_modificacion',
                'coordenadas_de_solicitud'
            )
                ->where('fk_id_estado', '!=', $this->estados['Anulado'])
                ->where('fk_id_estado', '!=', $this->estados['Finalizado'])
                ->where('fk_id_estado', '!=', $this->estados['Rechazado']);
            //$proyecto = $this->traitGetProyectoCabecera($request);
            //$compa = $this->traitIdEmpresaPorProyecto($request);
            //$query = $query->where('fk_id_project_Company', $proyecto)->where('fk_compañia', $compa)
            $query = $this->filtrar2($request, $query)->OrderBy('id_registro_proyecto', 'DESC')->take(100)->get();
            if (sizeof($query) == 0) {
                return $this->handleAlert(__('messages.sin_registros_por_mostrar'), false);
            }
            $query = $this->formatearDatosExtrasDeLista($query);
            return $this->handleResponse($request, $this->wbSeguridadSitioToArray($query), __('messages.consultado'));
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Obtiene los filtros de seguridad en el sitio según los parámetros de la solicitud.
     *
     * @param Request $request - La solicitud HTTP con los filtros.
     *
     * @return JsonResponse - Respuesta JSON con los resultados de la consulta.
     */
    public function getFilters(Request $request)
    {
        try {
            $filtroIdentificador = $request->identificador;
            $filtroFecha = $request->fecha;
            $tipoFiltroFecha = $request->fechaFiltro;
            $filtroTramo = $request->tramo;
            $filtroHito = $request->hito;
            $filtroAnulado = $request->anulado;
            $filtroEnProceso = $request->enProceso;
            $filtroPendiente = $request->pendiente;
            $filtroFinalizado = $request->finalizado;
            $filtroAprobado = $request->aprobado;
            $filtroRechazado = $request->rechazado;

            $query = null;
            if (
                $filtroFecha == null && $filtroTramo == null && $filtroHito == null && $filtroIdentificador == null
                && ($filtroAnulado == null || $filtroAnulado == -1)
                && ($filtroEnProceso == null || $filtroEnProceso == -1)
                && ($filtroPendiente == null || $filtroPendiente == -1)
                && ($filtroFinalizado == null || $filtroFinalizado == -1)
                && ($filtroAprobado == null || $filtroAprobado == -1)
                && ($filtroRechazado == null || $filtroRechazado == -1)
            ) {
                /* $query = WbSeguridadSitio::select('id_seguridad_sitio','id_registro_proyecto','fk_id_turno_seguridad_sitio',
                'fecha_inicio','fecha_finalizacion','fk_id_tramo','fk_id_hito','abscisa','observaciones', 'fk_id_estado', 'tipo_seguridad_sitio', 'usuario_creacion')
                ->where('fk_id_estado', '!=', $this->estados['Anulado'])
                ->where('fk_id_estado', '!=', $this->estados['Finalizado'])
                ->where('fk_id_estado', '!=', $this->estados['Rechazado']); */
                return $this->get($request);
            } else {
                $query = WbSeguridadSitio::select(
                    'id_seguridad_sitio',
                    'id_registro_proyecto',
                    'fk_id_turno_seguridad_sitio',
                    'fecha_inicio',
                    'fecha_finalizacion',
                    'fk_id_tramo',
                    'fk_id_hito',
                    'abscisa',
                    'otra_ubicacion',
                    'observaciones',
                    'fk_id_estado',
                    'usuario_creacion',
                    'fecha_modificacion',
                    'coordenadas_de_solicitud'
                );

                if ($filtroIdentificador != null) {
                    if (is_numeric($filtroIdentificador)) {
                        $query = $query->Where('id_registro_proyecto', 'like', $filtroIdentificador . '%');
                    }
                }

                if ($filtroFecha != null) {
                    if (is_numeric($tipoFiltroFecha)) {
                        if ($tipoFiltroFecha == 1) {
                            $query = $query->Where('fecha_inicio', $filtroFecha);
                        } else {
                            $query = $query->Where('fecha_finalizacion', $filtroFecha);
                        }
                    }
                }
                if ($filtroTramo != null) {
                    $tramo = WbTramos::select('id')->where('id_Tramo', $filtroTramo)->where('ESTADO', 'A');
                    $tramo = $this->filtrar($request, $tramo)->first();
                    if ($tramo != null) {
                        $query = $query->Where('fk_id_tramo', $tramo->id);
                    }
                }
                if ($filtroHito != null) {
                    $hito = WbHitosAbcisas::select('id_hitos_abscisas')->where('fk_id_Hitos', $filtroHito)->where('ESTADO', 'A');
                    $hito = $this->filtrar($request, $hito)->first();
                    if ($hito != null) {
                        $query = $query->Where('fk_id_hito', $hito->id_hitos_abscisas);
                    }
                }
                if (
                    ($filtroAnulado != null && $filtroAnulado != -1) || ($filtroEnProceso != null && $filtroEnProceso != -1) || ($filtroPendiente != null && $filtroPendiente != -1)
                    || ($filtroFinalizado != null && $filtroFinalizado != -1) || ($filtroAprobado != null && $filtroAprobado != -1) || ($filtroRechazado != null && $filtroRechazado != -1)
                )
                    $query = $query->where(function ($consulta) use ($filtroAnulado, $filtroPendiente, $filtroFinalizado, $filtroAprobado, $filtroRechazado, $filtroEnProceso) {
                        $consulta->orWhere('fk_id_estado', $filtroAnulado)
                            ->orWhere('fk_id_estado', $filtroPendiente)
                            ->orWhere('fk_id_estado', $filtroFinalizado)
                            ->orWhere('fk_id_estado', $filtroAprobado)
                            ->orWhere('fk_id_estado', $filtroRechazado)
                            ->orWhere('fk_id_estado', $filtroEnProceso);
                    });
            }
            //$proyecto = $this->traitGetProyectoCabecera($request);
            //$compa = $this->traitIdEmpresaPorProyecto($request);
            //$query = $query->where('fk_id_project_Company', $proyecto)->where('fk_compañia', $compa)
            $query = $this->filtrar2($request, $query)->OrderBy('id_registro_proyecto', 'DESC')->get();

            if (sizeof($query) == 0) {
                return $this->handleAlert(__('messages.sin_registros_por_mostrar'), false);
            }
            $query = $this->formatearDatosExtrasDeLista($query);
            return $this->handleResponse($request, $this->wbSeguridadSitioToArray($query), __('messages.consultado'));
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Verifica si los elementos asociados a la solicitud de seguridad en el sitio han sido actualizados.
     *
     * @param int $id_seguridad_sitio - ID de la solicitud de seguridad en el sitio.
     *
     * @return int - 1 si los elementos están actualizados, -1 si la solicitud está aprobada sin actualizaciones y 0 en otros casos.
     */
    private function elementosActualizados($id_seguridad_sitio)
    {
        $solicitud = WbSeguridadSitio::select('fk_id_estado', 'fecha_inicio', 'fk_id_turno_seguridad_sitio')
            ->where('id_seguridad_sitio', $id_seguridad_sitio)
            ->first();

        $confirm_equipo = WbSeguridadSitioEquipo::select('fk_id_seguridad_sitio')
            ->where('fk_id_seguridad_sitio', $id_seguridad_sitio)
            ->where('fecha_registro', DB::raw('CAST(GETDATE() AS DATE)'))
            ->first();

        $confirm_material = WbSeguridadSitioMaterial::select('fk_id_seguridad_sitio')
            ->where('fk_id_seguridad_sitio', $id_seguridad_sitio)
            ->where('fecha_registro', DB::raw('CAST(GETDATE() AS DATE)'))
            ->first();

        if ($solicitud->fk_id_estado != $this->estados['EnProceso'] && $solicitud->fk_id_estado != $this->estados['Aprobado']) {
            return 0;
        }

        if ($solicitud->fk_id_estado == $this->estados['Aprobado']) {
            $confirm_equipo_aux = WbSeguridadSitioEquipo::select('fk_id_seguridad_sitio')
                ->where('fk_id_seguridad_sitio', $id_seguridad_sitio)
                ->first();
            if ($confirm_equipo_aux != null) {
                return -1;
            }

            $confirm_material_aux = WbSeguridadSitioMaterial::select('fk_id_seguridad_sitio')
                ->where('fk_id_seguridad_sitio', $id_seguridad_sitio)
                ->first();
            if ($confirm_material_aux != null) {
                return -1;
            }

            if ($this->fechaIgualAlDiaDeHoy($solicitud->fecha_inicio)) {
                $hora_inicio = WbSeguridadSitioTurno::select('hora_inicio_turno')
                    ->where('id_seguridad_sitio_turno', $solicitud->fk_id_turno_seguridad_sitio)
                    ->first();

                if ($hora_inicio == null) {
                    return 0;
                }

                $hora = explode(':', $hora_inicio);
                $hora_actual = date('H');
                $comparacion = ($hora[0] - 2) < $hora_actual;
                if ($comparacion) {
                    return 0;
                }
            }

            $fecha_comparacion = date('Y-m-d');
            $fecha_comparacion = strtotime($fecha_comparacion . ' -1 day');
            $comparacion = $fecha_comparacion === $solicitud->fecha_inicio;
            if (!$comparacion) {
                return 0;
            }
        }

        if ($confirm_equipo == null && $confirm_material == null) {
            return 0;
        }
        return 1;
    }

    /**
     * Verifica si hay evidencias actualizadas para una solicitud de seguridad en el sitio.
     *
     * @param int $id_seguridad_sitio - ID de la solicitud de seguridad en el sitio.
     *
     * @return int - 1 si hay evidencias actualizadas, 0 en caso contrario.
     */
    private function evidenciasActualizadas($id_seguridad_sitio)
    {
        $confirm_evidencias = WbSeguridadSitioEvidencia::select('fk_id_seguridad_sitio')
            ->where('fk_id_seguridad_sitio', $id_seguridad_sitio)
            ->where('fecha_registro', DB::raw('CAST(GETDATE() AS DATE)'))
            ->first();
        if ($confirm_evidencias != null) {
            return 1;
        }
        return 0;
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    /**
     * Obtiene los detalles de una solicitud de seguridad en el sitio, incluyendo maquinarias, materiales y evidencias.
     *
     * @param Request $request - Objeto de solicitud HTTP.
     *
     * @return JsonResponse - Respuesta JSON con los detalles de la solicitud.
     */
    public function getDetalle(Request $request)
    {
        try {
            $maquinarias = null;
            $materiales = null;
            $evidencias = null;

            //cargamos las imagenes
            $query = WbSeguridadSitio::select('id_seguridad_sitio', 'fk_id_project_Company')->where('id_registro_proyecto', $request->identificador);
            $query = $this->filtrar($request, $query)->first();
            if ($query == null) {
                return $this->handleAlert('messages.solicitud_no_encontrada');
            }

            //buscamos los equipos
            $maqui = WbSeguridadSitioEquipo::select('fecha_registro')->where('fk_id_seguridad_sitio', $query->id_seguridad_sitio)
                ->where('fecha_registro', '!=', null)
                ->where('es_inicial', 0)
                ->orderBy('fecha_registro', 'desc')->first();

            //buscamos los materiales
            $mate = WbSeguridadSitioMaterial::select('fecha_registro')->where('fk_id_seguridad_sitio', $query->id_seguridad_sitio)
                ->where('fecha_registro', '!=', null)
                ->where('es_inicial', 0)
                ->orderBy('fecha_registro', 'desc')->first();


            //en el caso de que los datos sean nulos y no tengan mas registros que no sean lo iniciales
            if ($maqui == null && $mate == null) {
                //buscamos los equipos
                $maqui = WbSeguridadSitioEquipo::select('equiment_id', 'descripcion', 'marca', 'modelo', 'placa')
                    ->join('Wb_equipos', 'Wb_equipos.id', 'fk_id_equipo')
                    ->where('fk_id_seguridad_sitio', $query->id_seguridad_sitio)
                    ->where('es_inicial', 1)->get();
                if (sizeof($maqui) > 0) {
                    $maquinarias = $this->equiposToArray($maqui);
                }


                //buscamos los materiales
                $mate = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $query->id_seguridad_sitio)
                    ->where('es_inicial', 1)->get();

                if (sizeof($mate) > 0) {
                    $materiales = $this->wbSeguridadSitioMaterialToArray($mate);
                }

                $query->maquinarias = $maquinarias;
                $query->materiales = $materiales;
                $query->evidencias = $evidencias;

                $query->element_confirm = $this->elementosActualizados($query->id_seguridad_sitio);

                $query->evidence_confirm = $this->evidenciasActualizadas($query->id_seguridad_sitio);

                return $this->handleResponse($request, $this->wbSeguridadSitioToModel($query), __('messages.consultado'));
            }

            if ($maqui != null && $mate != null) {
                $fecha_maqui = strtotime($maqui->fecha_registro);
                $fecha_mate = strtotime($mate->fecha_registro);

                if ($fecha_maqui > $fecha_mate) {
                    $maquinarias = $this->getMaquinariasList($query);
                    $materiales = null;
                } else if ($fecha_mate > $fecha_maqui) {
                    $maquinarias = null;
                    $materiales = $this->getMaterialesList($query);
                } else {
                    $maquinarias = $this->getMaquinariasList($query);
                    $materiales = $this->getMaterialesList($query);
                }
            } else if ($maqui != null && $mate == null) {
                $maquinarias = $this->getMaquinariasList($query);
                $materiales = null;
            } else {
                $maquinarias = null;
                $materiales = $this->getMaterialesList($query);
            }


            //cargamos las evidencias
            $evi = WbSeguridadSitioEvidencia::where('fk_id_seguridad_sitio', $query->id_seguridad_sitio)->orderBy('fecha_registro', 'desc')->limit(1)->get();
            if (sizeof($evi) > 0) {
                $evidencias = $this->wbSeguridadSitioEvidenciaToArray($evi);
            }

            $query->maquinarias = $maquinarias;
            $query->materiales = $materiales;
            $query->evidencias = $evidencias;

            $query->element_confirm = $this->elementosActualizados($query->id_seguridad_sitio);

            $query->evidence_confirm = $this->evidenciasActualizadas($query->id_seguridad_sitio);

            return $this->handleResponse($request, $this->wbSeguridadSitioToModel($query), __('messages.consultado'));
        } catch (\Throwable $e) {
            //return $this->handleAlert($e->getMessage(), false);
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }

    /**
     * Obtiene la lista de materiales de una solicitud de seguridad en el sitio.
     *
     * @param object $solicitud - Objeto de solicitud de seguridad en el sitio.
     *
     * @return array|null - Lista de materiales o null si no hay materiales.
     */
    private function getMaterialesList($solicitud)
    {
        $mate = WbSeguridadSitioMaterial::select('fecha_registro')->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
            ->where('fecha_registro', '!=', null)
            ->where('es_inicial', 0)
            ->orderBy('fecha_registro', 'desc')->first();

        if ($mate != null) {
            $mate = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                ->where('fecha_registro', $mate->fecha_registro)
                ->where('es_inicial', 0)->get();
            if (sizeof($mate) > 0) {
                return $this->wbSeguridadSitioMaterialToArray($mate);
            }
        }
        return null;
    }

    /**
     * Obtiene la lista de maquinarias de una solicitud de seguridad en el sitio.
     *
     * @param object $solicitud - Objeto de solicitud de seguridad en el sitio.
     *
     * @return array|null - Lista de maquinarias o null si no hay maquinarias.
     */
    private function getMaquinariasList($solicitud)
    {
        $maqui = WbSeguridadSitioEquipo::select('fecha_registro')->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
            ->where('fecha_registro', '!=', null)
            ->where('es_inicial', 0)
            ->orderBy('fecha_registro', 'desc')->first();

        if ($maqui != null) {
            $maqui = WbSeguridadSitioEquipo::select('equiment_id', 'descripcion', 'marca', 'modelo', 'placa')
                ->join('Wb_equipos', 'Wb_equipos.id', 'fk_id_equipo')
                ->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                ->where('fecha_registro', $maqui->fecha_registro)
                ->where('es_inicial', 0)->get();
            if (sizeof($maqui) > 0) {
                return $this->equiposToArray($maqui);
            }
        }
        return null;
    }

    /**
     * Formatea datos extras para cada elemento en una lista.
     *
     * @param array $array - La lista de elementos a formatear.
     *
     * @return array - La lista formateada con datos adicionales.
     */
    private function formatearDatosExtrasDeLista($array)
    {
        foreach ($array as $key) {
            $query = usuarios_M::select('Nombre', 'Apellido')->where('id_usuarios', $key->usuario_creacion)->first();
            if ($query != null) {
                $nombre = Str::upper($query->Nombre) . ' ' . Str::upper($query->Apellido);
                $key->usuario_crea_name = $nombre;
            }
            $query = WbSeguridadSitio::select('fk_id_seguridad_sitio_traslado')->where('id_seguridad_sitio', $key->id_seguridad_sitio)->first();
            if ($query != null) {
                $subQuery = WbSeguridadSitio::select('id_registro_proyecto')->where('id_seguridad_sitio', $query->fk_id_seguridad_sitio_traslado)->first();
                if ($subQuery != null) {
                    $key->id_traslado = $subQuery->id_registro_proyecto;
                }
            }

            $key->element_confirm = $this->elementosActualizados($key->id_seguridad_sitio);

            $key->evidence_confirm = $this->evidenciasActualizadas($key->id_seguridad_sitio);
        }
        return $array;
    }

    /**
     * Verifica si un ID de turno dado es válido.
     *
     * @param int $turno - El ID del turno a verificar.
     *
     * @return bool - Devuelve true si el turno es válido, de lo contrario, false.
     */
    private function turnoValido($turno)
    {
        $turn = WbSeguridadSitioTurno::select('id_seguridad_sitio_turno')->where('id_seguridad_sitio_turno', $turno)->first();
        return $turn != null;
    }

    /**
     * Verifica si un ID de tramo dado es válido.
     *
     * @param int $tramo - El ID del tramo a verificar.
     *
     * @return bool - Devuelve true si el tramo es válido, de lo contrario, false.
     */
    private function tramoValido($tramo)
    {
        $tram = WbTramos::select('Id_Tramo')->where('Id_Tramo', $tramo)->first();
        return $tram != null;
    }

    /**
     * Verifica si un ID de hito dado es válido.
     *
     * @param int $hito - El ID del hito a verificar.
     *
     * @return bool - Devuelve true si el hito es válido, de lo contrario, false.
     */
    private function hitoValido($hito)
    {
        $hit = WbHitosAbcisas::select('fk_id_Hitos')->where('fk_id_Hitos', $hito)->first();
        return $hit != null;
    }

    /**
     * Anula una solicitud de seguridad.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function anularSolicitud(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'identificador' => 'required|numeric',
            'observaciones' => 'required',
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }
        $query = WbSeguridadSitio::where('id_registro_proyecto', $request->identificador);
        $query = $this->filtrar2($request, $query)->first();

        if ($query == null) {
            return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
        }

        $usuario = $this->traitGetIdUsuarioToken($request);
        if ($usuario == $query->usuario_creacion) {
            if ($query->fk_id_estado == $this->estados['Pendiente'] || $query->fk_id_estado == $this->estados['Aprobado']) {
                $query->fk_id_estado = $this->estados['Anulado'];
                $query->observaciones_anulado_finalizado = $request->observaciones;
                $query->usuario_modificacion = $usuario;
                $query->fecha_modificacion = DB::raw('SYSDATETIME()');
                if ($query->save()) {
                    $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.solicitud') . ' ' . $query->id_registro_proyecto . ' ' . __('messages.cambio_a_estado_anulado'), null, $usuario);
                    $this->enviarCorreoSolicitante($query->id_seguridad_sitio, __('messages.solicitud_anulada'));
                    return $this->handleAlert(__('messages.solicitud_anulada'), true);
                }
                $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.error_en_solicitud') . ' ' . $query->id_registro_proyecto . ' ' . __('messages.en_cambio_a_estado_anulado'), null, $usuario);
                return $this->handleAlert(__('messages.error_al_anular_solicitud'), false);
            }
            $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.error_en_solicitud') . ' ' . $query->id_registro_proyecto . ' ' . __('messages.no_puede_cambiar_a_estado_anulado'), null, $usuario);
            return $this->handleAlert(__('messages.solicitud_no_puede_ser_anulada'), false);
        }
        $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.error_en_solicitud') . ' ' . $query->id_registro_proyecto . ' ' . __('messages.no_cumple_requisitos_para_cambio_de_estado_a_anulado'), null, $usuario->fk_usuario);
        return $this->handleAlert(__('messages.usted_no_puede_anular_esta_solicitud'), false);
    }

    /**
     * Finaliza una solicitud de seguridad.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function finalizarSolicitud(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'identificador' => 'required|numeric',
                'fecha_final' => 'required|date_format:Y-m-d',
                'observaciones' => 'required',
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $query = WbSeguridadSitio::where('id_registro_proyecto', $request->identificador);
            $query = $this->filtrar($request, $query)->first();

            if ($query == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            $usuario = $this->traitGetMiUsuarioProyectoPorId($request);

            if ($query->fk_id_estado == $this->estados['EnProceso']) {


                $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('FINALIZAR_SEGURIDAD_SITIO', $usuario->fk_rol);

                if ($usuario->fk_usuario == $query->usuario_creacion || count($permiso) > 0) {
                    if ($this->fechaDeEntradaDentroDelRango($request->fecha_final, $query->id_seguridad_sitio)) {
                        if ($this->fechaIgualAlDiaDeHoy($request->fecha_final)) {
                            $query->fk_id_estado = $this->estados['Finalizado'];
                        }
                        $query->observaciones_anulado_finalizado = $request->observaciones;
                        $query->usuario_modificacion = $usuario->fk_usuario;
                        $query->fecha_finalizacion = $request->fecha_final;
                        $query->fecha_modificacion = \DB::raw('SYSDATETIME()');
                        if ($query->save()) {
                            if ($this->fechaIgualAlDiaDeHoy($request->fecha_final)) {
                                $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.solicitud') . ' ' . $query->id_registro_proyecto . ' ' . __('messages.cambio_a_estado_finalizado'), null, $usuario->fk_usuario);
                                $this->enviarCorreoSolicitante($query->id_seguridad_sitio, __('messages.solicitud_finalizada'));
                                $this->enviarCorreoCalificadores($query->id_seguridad_sitio, __('messages.solicitud_finalizada'));
                                return $this->handleAlert(__('messages.solicitud_finalizada'), true);
                            } else {
                                $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.solicitud') . ' ' . $query->id_registro_proyecto . ' ' . __('messages.programado_para_cambio_a_estado_finalizado'), null, $usuario->fk_usuario);
                                $this->enviarCorreoSolicitante($query->id_seguridad_sitio, __('messages.solicitud_programada_para_ser_finalizada'));
                                $this->enviarCorreoCalificadores($query->id_seguridad_sitio, __('messages.solicitud_programada_para_ser_finalizada'));
                                return $this->handleAlert(__('messages.solicitud_programada_para_ser_finalizada'), true);
                            }
                        }
                        $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.error_en_solicitud') . ' ' . $query->id_registro_proyecto . ' ' . __('messages.en_cambio_a_estado_finalizado'), null, $usuario->fk_usuario);
                        return $this->handleAlert(__('messages.error_al_finalizar_solicitud'), false);
                    }
                    $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.error_en_solicitud') . ' ' . $query->id_registro_proyecto . ' ' . __('messages.en_cambio_a_estado_finalizado') . '. ' . __('messages.la_fecha_de_finalizar_debe_ser_mayor_a_la_de_inicio'), null, $usuario->fk_usuario);
                    return $this->handleAlert(__('messages.la_fecha_de_finalizar_debe_ser_mayor_a_la_de_inicio'), false);
                }
                $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.error_en_solicitud') . ' ' . $query->id_registro_proyecto . ' ' . __('messages.no_se_puede_cambiar_a_estado_finalizado'), null, $usuario->fk_usuario);
                return $this->handleAlert(__('messages.usted_no_puede_finalizar_esta_solicitud'), false);
            }
            $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.error_en_solicitud') . ' ' . $query->id_registro_proyecto . ' ' . __('messages.no_cumple_requisitos_para_cambio_de_estado_a_finalizado'), null, $usuario->fk_usuario);
            return $this->handleAlert(__('messages.solicitud_no_cumple_los_requisitos_para_finalizar'), false);
        } catch (\Throwable $e) {
            //return $this->handleAlert($e->getMessage(), false);
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }

    /**
     * Realiza una solicitud de traslado para una solicitud de seguridad existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function trasladoSolicitud(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'identificador' => 'required|numeric',
                'fechaInicio' => 'required|date_format:Y-m-d',
                'fechaFinalizacion' => 'required|date_format:Y-m-d',
                'idTramo' => 'required|string',
                'idHito' => 'required|string',
                'abscisa' => 'required|string',
                'observaciones' => 'required|string',
                'ubicacion' => 'required|string',
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            if (!$this->tramoValido($request->idTramo)) {
                return $this->handleAlert(__('messages.tramo_no_valido'));
            }

            if (!$this->hitoValido($request->idHito)) {
                return $this->handleAlert(__('messages.hito_no_valido'));
            }

            $observaciones = null;
            if (!is_null($request->observaciones)) {
                $observaciones = $request->observaciones;
            }

            $query = WbSeguridadSitio::where('id_registro_proyecto', $request->identificador);
            $query = $this->filtrar($request, $query)->first();

            if ($query == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            $usuario = $this->traitGetMiUsuarioProyectoPorId($request);

            $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('TRASLADO_SEGURIDAD_SITIO', $usuario->fk_rol);

            if ($usuario->fk_usuario != $query->usuario_creacion && count($permiso) == 0) {
                return $this->handleAlert(__('messages.usted_no_puede_solicitar_traslado_de_esta_solicitud'), false);
            }

            if ($query->fk_id_tramo == $request->idTramo && $query->fk_id_hito == $request->idHito && $query->abscisa == $request->abscisa) {
                return $this->handleAlert(__('messages.no_hay_cambios_significativos_como_para_realizar_la_solicitud'), false);
            }

            if ($this->tieneTraslado($query->id_seguridad_sitio)) {
                return $this->handleAlert(__('messages.esta_solicitud_ya_tiene_una_solicitud_de_traslado'), false);
            }

            //creamos nueva instacia del modelo
            $seguridadSitio = new WbSeguridadSitio;

            //estabelcemos el proyecto y compañia
            $seguridadSitio = $this->traitSetProyectoYCompania($request, $seguridadSitio);

            //asignamos los valores al modelo
            $usuario = $this->traitGetIdUsuarioToken($request);

            //preguntamos por el ultimo registro del proyecto
            $registro = WbSeguridadSitio::select('id_registro_proyecto');
            //->where('fk_id_project_Company', $seguridadSitio->fk_id_project_Company)->where('fk_compañia', $seguridadSitio->fk_compañia)
            $registro = $this->filtrar($request, $registro)->orderBy('id_registro_proyecto', 'DESC')->limit(1)->first();

            if ($registro != null) {
                $registro = $registro->id_registro_proyecto;
                $seguridadSitio->id_registro_proyecto = $registro + 1;
            } else {
                $seguridadSitio->id_registro_proyecto = 1;
            }

            $seguridadSitio->fecha_inicio = $request->fechaInicio;
            $seguridadSitio->fecha_finalizacion = $request->fechaFinalizacion;

            $seguridadSitio->fk_id_turno_seguridad_sitio = $query->fk_id_turno_seguridad_sitio;
            $seguridadSitio->fk_id_tramo = $request->idTramo;
            $seguridadSitio->fk_id_hito = $request->idHito;
            $seguridadSitio->abscisa = $request->abscisa;
            $seguridadSitio->observaciones = $observaciones;
            $seguridadSitio->ubicacion = $request->ubicacion;
            $seguridadSitio->usuario_creacion = $usuario;
            $seguridadSitio->fecha_creacion = DB::raw('SYSDATETIME()');
            $seguridadSitio->fk_id_estado = $this->estados['Pendiente'];

            $seguridadSitio->fk_id_seguridad_sitio_traslado = $query->id_seguridad_sitio;
            if ($seguridadSitio->save()) {

                $error = 0;
                //buscamos los equipos
                $result = WbSeguridadSitioEquipo::where('fk_id_seguridad_sitio', $query->id_seguridad_sitio)->get();
                if (sizeof($result) > 0) {
                    foreach ($result as $key) {
                        $equipo = new WbSeguridadSitioEquipo();
                        $equipo->fk_id_seguridad_sitio = $seguridadSitio->id_seguridad_sitio;
                        $equipo->fk_id_equipo = $key->fk_id_equipo;
                        if (!$equipo->save())
                            $error++;
                    }
                }

                //buscamos los materiales
                $result = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $query->id_seguridad_sitio)->get();
                if (sizeof($result) > 0) {
                    foreach ($result as $key) {
                        $material = new WbSeguridadSitioMaterial();
                        $material->fk_id_seguridad_sitio = $seguridadSitio->id_seguridad_sitio;
                        $material->material = $key->material;
                        $material->cantidad = $key->cantidad;
                        $material->unidad_medida = $key->unidad_medida;
                        if (!$material->save())
                            $error++;
                    }
                }

                if ($error == 0) {
                    $this->enviarCorreoSolicitante($seguridadSitio->id_seguridad_sitio, 'Nueva solicitud de traslado');
                    $this->enviarCorreoCalificadores($seguridadSitio->id_seguridad_sitio, 'Nueva solicitud de traslado');
                    return $this->handleResponse($request, $seguridadSitio->id_registro_proyecto, __('messages.registro_exitoso'));
                }
                return $this->handleAlert(__('messages.error_al_registrar_maquinarias_y_materiales'), false);
            }
            return $this->handleAlert(__('messages.no_se_pudo_realizar_el_traslado'), false);
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert(__($e->getMessage()), false);
        }
    }

    /**
     * Verifica si una solicitud de seguridad tiene una solicitud de traslado pendiente o aprobada.
     *
     * @param  int  $solicitudId  El ID de la solicitud de seguridad.
     * @return bool  Retorna true si la solicitud de seguridad tiene una solicitud de traslado pendiente o aprobada, de lo contrario, retorna false.
     */
    private function tieneTraslado($solicitudId)
    {
        $query = WbSeguridadSitio::select('id_seguridad_sitio')->where('fk_id_seguridad_sitio_traslado', $solicitudId)->whereIn('fk_id_estado', [$this->estados['Pendiente'], $this->estados['Aprobado']])->first();
        if ($query != null) {
            return true;
        }
        return false;
    }

    /**
     * Verifica si la fecha dada es igual al día de hoy.
     *
     * @param  string  $fecha  La fecha en formato "Y-m-d" que se va a comparar con el día de hoy.
     * @return bool  Retorna true si la fecha es igual al día de hoy, de lo contrario, retorna false.
     */
    private function fechaIgualAlDiaDeHoy($fecha)
    {
        $hoy = date("Y-m-d");
        return $hoy === $fecha;
    }
    /* fin aplicacion movil*/


    /* Inicio Aplicacion Web */

    /**
     * Obtiene datos de seguridad del sitio para su visualización en la interfaz web.
     *
     * @param  Request  $req  La solicitud HTTP recibida.
     * @return mixed  Retorna la respuesta de manejo de alerta o la respuesta JSON con los datos.
     */
    public function getWeb(Request $req)
    {
        try {
            if (!is_numeric($req->page) || !is_numeric($req->limit)) {
                return $this->handleAlert('Datos invalidos');
            }

            $pagina = $req->page;
            $limite = $req->limit;

            $fechaInicio_filtro = $req->fechaInicio;
            $fechaFinal_filtro = $req->fechaFinal;
            $estado_filtro = $req->estado;


            $query = WbSeguridadSitio::select(
                'id_seguridad_sitio',
                'id_registro_proyecto',
                'fk_id_turno_seguridad_sitio',
                'fecha_inicio',
                'fecha_finalizacion',
                'fk_id_tramo',
                'fk_id_hito',
                'otra_ubicacion',
                'abscisa',
                'observaciones',
                'fk_id_estado',
                'usuario_creacion',
                'fk_id_seguridad_sitio_traslado',
                'usuario_calificacion',
                'observaciones_calificacion',
                'usuario_modificacion',
                'observaciones_anulado_finalizado',
                'es_arma_fuego',
                'es_motorizado',
                DB::raw("CAST(fecha_creacion AS date) AS fecha_creacion"),
            );

            if (($fechaInicio_filtro !== null && strlen($fechaInicio_filtro) > 0) || ($fechaFinal_filtro !== null && strlen($fechaFinal_filtro) > 0) || ($estado_filtro !== null && strlen($estado_filtro) > 0)) {
                if ($estado_filtro !== null && strlen($estado_filtro) > 0) {
                    switch ($estado_filtro) {
                        case 'RECHAZADO':
                            $query = $query->where('fk_id_estado', $this->estados['Rechazado']);
                            break;
                        case 'APROBADO':
                            $query = $query->where('fk_id_estado', $this->estados['Aprobado']);
                            break;
                        case 'ANULADO':
                            $query = $query->where('fk_id_estado', $this->estados['Anulado']);
                            break;
                        case 'PENDIENTE':
                            $query = $query->where('fk_id_estado', $this->estados['Pendiente']);
                            break;
                        case 'ENPROCESO':
                            $query = $query->where('fk_id_estado', $this->estados['EnProceso']);
                            break;
                        case 'FINALIZADO':
                            $query = $query->where('fk_id_estado', $this->estados['Finalizado']);
                            break;
                    }
                }

                if (($fechaInicio_filtro !== null && strlen($fechaInicio_filtro) > 0) && ($fechaFinal_filtro !== null && strlen($fechaFinal_filtro) > 0)) {
                    $query = $query->where('fecha_inicio', '>=', $fechaInicio_filtro)
                        ->where('fecha_finalizacion', '<=', $fechaFinal_filtro);
                }
            }
            //$proyecto = $this->traitGetProyectoCabecera($req);
            //$compa = $this->traitIdEmpresaPorProyecto($req);
            //$query = $query->where('fk_id_project_Company', $proyecto)->where('fk_compañia', $compa)
            $query = $this->filtrar2($req, $query)->orderBy('id_registro_proyecto', 'DESC');
            if (sizeof($query->get()) == 0) {
                return $this->handleAlert(__('messages.sin_registros_por_mostrar'), false);
            }

            $limitePagina = 1;
            $contador = clone $query;
            $contador = $contador->select('id_seguridad_sitio')->count();
            $query = $query->forPage($pagina, $limite)->get();
            $limitePagina = ($contador / $limite);
            if ($limitePagina <= 1) {
                $limitePagina = $limitePagina = 1;
            }

            foreach ($query as $key) {
                if ($key->fk_id_estado == $this->estados['Anulado']) {
                    $key->usuario_calificacion = $key->usuario_modificacion;
                    $key->observaciones_calificacion = $key->observaciones_anulado_finalizado;
                }
            }

            $query = $this->formatearDatosExtrasDeLista($query);
            $query = $this->getNombreTurno($query);
            $query = $this->getMaterialesYMaquinarias($query);
            $query = $this->getEstadosName($query);
            $query = $this->getTraslado($query);
            $query = $this->getCalificador($query);
            $query = $this->getTiempoRestanteParaSerAnulado($query);
            $query = $this->getConfirmarSiExistePdfDisponible($query);
            $query = $this->getMensajeDeIndicadorDeEvidenciasDiarias($query);
            $query = $this->getEvidenciaConfirmacion($query);
            $query = $this->columnasExtrasArmaFuegoMotorizado($query);

            return $this->handleResponse($req, $this->wbSeguridadSitioWebToArray($query), __('messages.consultado'), ceil($limitePagina));
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Agrega información sobre la existencia de evidencia de confirmación a cada elemento del array.
     *
     * @param  array  $array  El array de elementos a los que se les agregará la información de evidencia de confirmación.
     * @return array  El array con la información de evidencia de confirmación agregada.
     */
    private function getEvidenciaConfirmacion($array)
    {
        if (sizeof($array) > 0) {
            foreach ($array as $key) {
                $evidencia = WbSeguridadSitioEvidencia::select('id_seguridad_sitio_evidencia')
                    ->where('estado', 1)
                    ->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)->first();
                if ($evidencia == null) {
                    $key->evidencia_confirmacion = 0;
                    continue;
                }
                $key->evidencia_confirmacion = 1;
            }
        }
        return $array;
    }

    /**
     * Agrega información sobre la disponibilidad de PDF para confirmación a cada elemento del array.
     *
     * @param  array  $array  El array de elementos a los que se les agregará la información de disponibilidad de PDF.
     * @return array  El array con la información de disponibilidad de PDF agregada.
     */
    public function getConfirmarSiExistePdfDisponible($array)
    {
        if (sizeof($array) > 0) {
            foreach ($array as $key) {
                $elementos = WbSeguridadSitioEquipo::select('id_seguridad_sitio_equipo')
                    ->where('es_inicial', 0)
                    ->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                    ->where('fecha_registro', DB::raw('CAST(GETDATE() AS DATE)'))
                    ->first();
                if ($elementos != null) {
                    $key->confirm_pdf = 1;
                    continue;
                }

                $elementos = WbSeguridadSitioMaterial::select('id_seguridad_sitio_material')
                    ->where('es_inicial', 0)
                    ->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                    ->where('fecha_registro', DB::raw('CAST(GETDATE() AS DATE)'))
                    ->first();
                if ($elementos != null) {
                    $key->confirm_pdf = 1;
                    continue;
                }
                $key->confirm_pdf = 0;
            }
        }
        return $array;
    }

    /**
     * Agrega información sobre el tiempo restante para que una solicitud sea anulada a cada elemento del array.
     *
     * @param  array  $array  El array de elementos a los que se les agregará la información del tiempo restante.
     * @return array  El array con la información del tiempo restante agregada.
     */
    private function getTiempoRestanteParaSerAnulado($array)
    {
        if (sizeof($array) > 0) {
            $fecha_actual_str = date('Y-m-d');
            $fecha_actual = strtotime($fecha_actual_str);
            foreach ($array as $key) {
                $result = WbSeguridadSitio::select('fecha_inicio')
                    ->where('id_seguridad_sitio', $key->id_seguridad_sitio)
                    ->where('fk_id_estado', $this->estados['Pendiente'])->first();
                if ($result != null) {
                    $fecha_inicial = strtotime($key->fecha_inicio);
                    //$interval = $fecha_actual->diff($fecha_inicial);
                    $diff = $fecha_inicial - $fecha_actual;
                    $interval = floor($diff / (60 * 60 * 24));
                    //$interval = $interval +1;
                    //$key->prox_vencer = $interval;
                    if ($interval < 3 && $interval >= 0) {
                        $key->prox_vencer = $interval + 1;
                    } else if ($interval < 0) {
                        $key->prox_vencer = -1;
                    } else {
                        $key->prox_vencer = '';
                    }
                }

            }
        }
        return $array;
    }

    /**
     * Agrega mensajes indicativos sobre la carga de evidencias diarias a cada elemento del array.
     *
     * @param  array  $array  El array de elementos a los que se les agregarán mensajes indicativos.
     * @return array  El array con los mensajes indicativos agregados.
     */
    private function getMensajeDeIndicadorDeEvidenciasDiarias($array)
    {
        if (sizeof($array) > 0) {
            foreach ($array as $key) {
                if ($key->fk_id_estado != $this->estados['Aprobado'] && $key->fk_id_estado != $this->estados['EnProceso']) {
                    continue;
                }


                $equipo = WbSeguridadSitioEquipo::select('id_seguridad_sitio_equipo')
                    ->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                    ->where('es_inicial', 0)
                    ->where('fecha_registro', DB::raw("CAST(GETDATE() AS DATE)"))->first();

                $material = WbSeguridadSitioMaterial::select('id_seguridad_sitio_material')
                    ->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                    ->where('es_inicial', 0)
                    ->where('fecha_registro', DB::raw("CAST(GETDATE() AS DATE)"))->first();

                if ($equipo == null && $material == null) {
                    $key->alert_evidencias_diarias = __('messages.no_ha_cargado_elementos_del_dia_de_hoy');
                    continue;
                }
                if ($equipo == null) {
                    $key->alert_evidencias_diarias = __('messages.no_ha_cargado_maquinarias_o_equipos_del_dia_de_hoy');
                    continue;
                }
                if ($material == null) {
                    $key->alert_evidencias_diarias = __('messages.no_ha_cargado_maquinarias_o_equipos_del_dia_de_hoy');
                    continue;
                }

                $evidencia = WbSeguridadSitioEvidencia::select('id_seguridad_sitio_evidencia')
                    ->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                    ->where('estado', 1)
                    ->where('fecha_registro', DB::raw("CAST(GETDATE() AS DATE)"))->first();
                if ($evidencia == null) {
                    $key->alert_evidencias_diarias = __('messages.no_ha_cargado_evidencias_del_dia_de_hoy');
                    continue;
                }

                $key->alert_evidencias_diarias = null;
            }
        }
        return $array;
    }

    /**
     * Agrega el nombre del calificador a cada elemento del array.
     *
     * @param  array  $array  El array de elementos a los que se les agregará el nombre del calificador.
     * @return array  El array con el nombre del calificador agregado.
     */
    private function getCalificador($array)
    {
        foreach ($array as $key) {
            if ($key->usuario_calificacion == null) {
                continue;
            }
            $query = usuarios_M::select('Nombre', 'Apellido')->where('id_usuarios', $key->usuario_calificacion)->first();
            if ($query != null) {
                $nombre = Str::upper($query->Nombre) . ' ' . Str::upper($query->Apellido);
                $key->usuario_cal_name = $nombre;
            }
        }
        return $array;
    }

    /**
     * Agrega la información de traslado a cada elemento del array.
     *
     * @param  array  $array  El array de elementos a los que se les agregará la información de traslado.
     * @return array  El array con la información de traslado agregada.
     */
    private function getTraslado($array)
    {
        if (sizeof($array) > 0) {
            foreach ($array as $key) {
                $result = WbSeguridadSitio::select('id_registro_proyecto')->where('id_seguridad_sitio', $key->fk_id_seguridad_sitio_traslado)->first();
                if ($result != null) {
                    $array->id_traslado = $result->id_registro_proyecto;
                }
            }
        }
        return $array;
    }

    /* private function getMaquinariasYMateriales($array)
    {
        if (sizeof($array) > 0) {
            $maquinarias = null;
            $materiales = null;
            foreach ($array as $key) {
                $result = WbSeguridadSitioEquipo::select('equiment_id', 'descripcion', 'marca', 'modelo', 'placa')
                    ->join('Wb_equipos', 'Wb_equipos.id', 'fk_id_equipo')
                    ->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)->get();
                if (sizeof($result) > 0) {
                    $maquinarias = $this->equiposToArray($result);
                }

                $result = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)->get();
                if (sizeof($result) > 0) {
                    $materiales = $this->wbSeguridadSitioMaterialToArray($result);
                }
                $key->maquinarias = $maquinarias;
                $key->materiales = $materiales;
            }
        }
        return $array;
    } */

    /**
     * Agrega información sobre maquinarias y materiales a cada elemento del array.
     *
     * @param  array  $array  El array de elementos a los que se les agregará la información de maquinarias y materiales.
     * @return array  El array con la información de maquinarias y materiales agregada.
     */
    private function getMaterialesYMaquinarias($array)
    {
        if (sizeof($array) > 0) {

            foreach ($array as $key) {
                $maquinarias = null;
                $materiales = null;

                //buscamos los equipos
                $maqui = WbSeguridadSitioEquipo::select('fecha_registro')->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                    ->where('fecha_registro', '!=', null)
                    ->where('es_inicial', 0)
                    ->orderBy('fecha_registro', 'desc')->first();

                //buscamos los materiales
                $mate = WbSeguridadSitioMaterial::select('fecha_registro')->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                    ->where('fecha_registro', '!=', null)
                    ->where('es_inicial', 0)
                    ->orderBy('fecha_registro', 'desc')->first();

                //en el caso de que los datos sean nulos y no tengan mas registros que no sean lo iniciales
                if ($maqui == null && $mate == null) {
                    //buscamos los equipos
                    $maqui = WbSeguridadSitioEquipo::select('equiment_id', 'descripcion', 'marca', 'modelo', 'placa')
                        ->join('Wb_equipos', 'Wb_equipos.id', 'fk_id_equipo')
                        ->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                        ->where('es_inicial', 1)->get();
                    if (sizeof($maqui) > 0) {
                        $maquinarias = $this->equiposToArray($maqui);
                    }


                    //buscamos los materiales
                    $mate = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                        ->where('es_inicial', 1)->get();

                    if (sizeof($mate) > 0) {
                        $materiales = $this->wbSeguridadSitioMaterialToArray($mate);
                    }

                    $key->maquinarias = $maquinarias;
                    $key->materiales = $materiales;

                    continue;
                }

                if ($maqui != null && $mate != null) {
                    $fecha_maqui = strtotime($maqui->fecha_registro);
                    $fecha_mate = strtotime($mate->fecha_registro);

                    if ($fecha_maqui > $fecha_mate) {
                        $maquinarias = $this->getMaquinariasList($key);
                        $materiales = null;
                    } else if ($fecha_mate > $fecha_maqui) {
                        $maquinarias = null;
                        $materiales = $this->getMaterialesList($key);
                    } else {
                        $maquinarias = $this->getMaquinariasList($key);
                        $materiales = $this->getMaterialesList($key);
                    }
                } else if ($maqui != null && $mate == null) {
                    $maquinarias = $this->getMaquinariasList($key);
                    $materiales = null;
                } else {
                    $maquinarias = null;
                    $materiales = $this->getMaterialesList($key);
                }

                $key->maquinarias = $maquinarias;
                $key->materiales = $materiales;
            }

        }
        return $array;
    }

    /**
     * Agrega el nombre del turno a cada elemento del array.
     *
     * @param  array  $array  El array de elementos a los que se les agregará el nombre del turno.
     * @return array  El array con el nombre del turno agregado.
     */
    private function getNombreTurno($array)
    {
        if (sizeof($array) > 0) {
            foreach ($array as $key) {
                $turno = WbSeguridadSitioTurno::select('nombre_turno')->where('id_seguridad_sitio_turno', $key->fk_id_turno_seguridad_sitio)->first();
                if ($turno != null) {
                    $key->nombre_turno = $turno->nombre_turno;
                }
            }
        }
        return $array;
    }

    /**
     * Agrega el nombre del estado a cada elemento del array.
     *
     * @param  array  $array  El array de elementos a los que se les agregará el nombre del estado.
     * @return array  El array con el nombre del estado agregado.
     */
    private function getEstadosName($array)
    {
        if (sizeof($array) > 0) {
            foreach ($array as $key) {
                switch ($key->fk_id_estado) {
                    case $this->estados['Pendiente']:
                        $key->estado_name = Str::upper('Pendiente');
                        break;
                    case $this->estados['Rechazado']:
                        $key->estado_name = Str::upper('Rechazado');
                        break;
                    case $this->estados['Anulado']:
                        $key->estado_name = Str::upper('Anulado');
                        break;
                    case $this->estados['Aprobado']:
                        $key->estado_name = Str::upper('Aprobado');
                        break;
                    case $this->estados['Finalizado']:
                        $key->estado_name = Str::upper('Finalizado');
                        break;
                    case $this->estados['EnProceso']:
                        $key->estado_name = Str::upper('EnProceso');
                        break;
                }
            }
        }
        return $array;
    }

    /**
     * Califica una solicitud de seguridad como aprobada.
     *
     * @param  Request  $req  La solicitud HTTP que contiene los datos necesarios para calificar la solicitud.
     * @return JsonResponse  La respuesta JSON indicando si la operación fue exitosa o no.
     */
    public function calificarAprobado(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'identificador' => 'required|numeric',
                'observaciones' => 'required',
                'es_arma_fuego' => 'required|numeric',
                'es_motorizado' => 'required|numeric',
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $query = WbSeguridadSitio::where('id_seguridad_sitio', $req->identificador);
            $query = $this->filtrar($req, $query)->first();

            if ($query == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            if ($query->fk_id_estado == $this->estados['Pendiente']) {
                $usuario = $this->traitGetMiUsuarioProyectoPorId($req);

                $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('CALIFICAR_SEGURIDAD_SITIO', $usuario->fk_rol);

                if (count($permiso) > 0) {
                    if ($this->fechaIgualAlDiaDeHoy($query->fecha_inicio) || $this->fechaDeHoyDentroDelRango($query->fecha_inicio, $query->fecha_finalizacion)) {
                        $query->fk_id_estado = $this->estados['EnProceso'];
                    } else {
                        $query->fk_id_estado = $this->estados['Aprobado'];
                    }
                    $query->usuario_calificacion = $usuario->fk_usuario;
                    $query->observaciones_calificacion = $req->observaciones;
                    $query->es_arma_fuego = $req->es_arma_fuego;
                    $query->es_motorizado = $req->es_motorizado;
                    $query->usuario_modificacion = $usuario->fk_usuario;
                    $query->fecha_modificacion = DB::raw('SYSDATETIME()');
                    if ($query->save()) {
                        $this->actualizarFechaFinalizacionSolicitudQueSeraTrasladado($query->fk_id_seguridad_sitio_traslado, $usuario, $query->fecha_inicio);
                        $this->enviarCorreoSolicitante($query->id_seguridad_sitio, __('messages.solicitud_aprobada'));
                        $this->enviarCorreoCalificadores($query->id_seguridad_sitio, __('messages.solicitud_aprobada'));
                        if ($this->fechaIgualAlDiaDeHoy($query->fecha_inicio) || $this->fechaDeHoyDentroDelRango($query->fecha_inicio, $query->fecha_finalizacion)) {
                            $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, 'Calificacion', 'Solicitud ' . $query->id_registro_proyecto . ' aprobada esta en proceso, motivo de calificacion: ' . $req->observaciones, null, $usuario->fk_usuario);
                        } else {
                            $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, 'Calificacion', 'Solicitud ' . $query->id_registro_proyecto . ' aprobada, motivo de calificacion: ' . $req->observaciones, null, $usuario->fk_usuario);
                        }
                        return $this->handleAlert(__('messages.solicitud_aprobada'), true);
                    }
                    return $this->handleAlert(__('messages.error_al_aprobar_solicitud'), false);
                }
                return $this->handleAlert(__('messages.usted_no_puede_aprobar_esta_solicitud'), false);
            }
            return $this->handleAlert(__('messages.solicitud_no_cumple_los_requisitos_para_aprobar'), false);
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }

    /**
     * Verifica si la fecha de hoy está dentro del rango definido por la fecha de inicio y la fecha de finalización.
     *
     * @param  string  $fecha_inicio  La fecha de inicio del rango en formato 'Y-m-d'.
     * @param  string  $fecha_final  La fecha de finalización del rango en formato 'Y-m-d'.
     * @return bool  Devuelve true si la fecha de hoy está dentro del rango, de lo contrario, devuelve false.
     */
    private function fechaDeHoyDentroDelRango($fecha_inicio, $fecha_final)
    {
        $fecha_actual_str = date('Y-m-d');
        $fecha_actual = new DateTime($fecha_actual_str);
        $fecha_inicial = new DateTime($fecha_inicio);
        $fecha_finalizacion = new DateTime($fecha_final);
        return $fecha_actual >= $fecha_inicial && $fecha_actual <= $fecha_finalizacion;
    }

    /**
     * Verifica si la fecha de entrada está dentro del rango definido por las fechas de inicio y finalización de una solicitud.
     *
     * @param  string  $entrada  La fecha de entrada que se va a verificar en formato 'Y-m-d'.
     * @param  int  $id_solicitud  El identificador de la solicitud.
     * @return bool  Devuelve true si la fecha de entrada está dentro del rango de la solicitud, de lo contrario, devuelve false.
     */
    private function fechaDeEntradaDentroDelRango($entrada, $id_solicitud)
    {

        $fechas = WbSeguridadSitio::select('fecha_inicio', 'fecha_finalizacion')->where('id_seguridad_sitio', $id_solicitud)->first();

        $fecha_actual_str = $entrada;
        $fecha_actual = new DateTime($fecha_actual_str);
        $fecha_inicial = new DateTime($fechas->fecha_inicio);
        $fecha_finalizacion = new DateTime($fechas->fecha_finalizacion);


        return $fecha_actual >= $fecha_inicial && $fecha_actual <= $fecha_finalizacion;
    }

    /**
     * Actualiza la fecha de finalización de una solicitud que será trasladada.
     *
     * @param  int  $id  El identificador de la solicitud.
     * @param  object  $usuario  Objeto que contiene información del usuario que realiza la modificación.
     * @param  string  $fecha_final  La nueva fecha de finalización en formato 'Y-m-d'.
     * @return void
     */
    private function actualizarFechaFinalizacionSolicitudQueSeraTrasladado($id, $usuario, $fecha_final)
    {
        $query = WbSeguridadSitio::where('id_seguridad_sitio', $id)->first();
        if ($query != null) {
            $query->fecha_finalizacion = $fecha_final;
            $query->usuario_modificacion = $usuario->fk_usuario;
            $query->fecha_modificacion = DB::raw('SYSDATETIME()');
            $query->save();
        }
    }

    /**
     * Califica una solicitud como "Rechazada".
     *
     * @param  Request  $req  La instancia de la solicitud HTTP.
     * @return JsonResponse  La respuesta JSON con el resultado de la operación.
     */
    public function calificarRechazado(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'identificador' => 'required|numeric',
                'observaciones' => 'required',
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $query = WbSeguridadSitio::where('id_seguridad_sitio', $req->identificador);
            $query = $this->filtrar($req, $query)->first();

            if ($query == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            if ($query->fk_id_estado == $this->estados['Pendiente']) {
                $usuario = $this->traitGetMiUsuarioProyectoPorId($req);

                $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('CALIFICAR_SEGURIDAD_SITIO', $usuario->fk_rol);

                if (count($permiso) > 0) {
                    $query->fk_id_estado = $this->estados['Rechazado'];
                    $query->usuario_calificacion = $usuario->fk_usuario;
                    $query->observaciones_calificacion = $req->observaciones;
                    $query->usuario_modificacion = $usuario->fk_usuario;
                    $query->fecha_modificacion = DB::raw('SYSDATETIME()');
                    if ($query->save()) {
                        $this->enviarCorreoSolicitante($query->id_seguridad_sitio, __('messages.solicitud_rechazada'));
                        $this->enviarCorreoCalificadores($query->id_seguridad_sitio, __('messages.solicitud_rechazada'));
                        $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, 'Calificacion', 'Solicitud ' . $query->id_registro_proyecto . ' rechazada, motivo de calificacion: ' . $req->observaciones, null, $usuario->fk_usuario);
                        return $this->handleAlert(__('messages.solicitud_rechazada'), true);
                    }
                    return $this->handleAlert(__('messages.error_al_rechazar_solicitud'), false);
                }
                return $this->handleAlert(__('messages.usted_no_puede_rechazar_esta_solicitud'), false);
            }
            return $this->handleAlert(__('messages.solicitud_no_cumple_los_requisitos_para_rechazar'), false);
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }

    /**
     * Gestiona la anulación de una solicitud.
     *
     * @param  Request  $req  La instancia de la solicitud HTTP.
     * @return JsonResponse  La respuesta JSON con el resultado de la operación.
     */
    public function gestionAnularSolicitud(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'identificador' => 'required|numeric',
                'observaciones' => 'required',
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $query = WbSeguridadSitio::where('id_seguridad_sitio', $req->identificador);
            $query = $this->filtrar($req, $query)->first();

            if ($query == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            if ($query->fk_id_estado == $this->estados['Pendiente'] || $query->fk_id_estado == $this->estados['Aprobado']) {
                $usuario = $this->traitGetMiUsuarioProyectoPorId($req);

                $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('CALIFICAR_SEGURIDAD_SITIO', $usuario->fk_rol);

                if ($usuario->fk_usuario == $query->usuario_creacion || count($permiso) > 0) {
                    $query->fk_id_estado = $this->estados['Anulado'];
                    $query->observaciones_anulado_finalizado = $req->observaciones;
                    $query->usuario_modificacion = $usuario->fk_usuario;
                    $query->fecha_modificacion = DB::raw('SYSDATETIME()');
                    if ($query->save()) {
                        $this->enviarCorreoSolicitante($query->id_seguridad_sitio, __('messages.solicitud_anulada'));
                        $this->enviarCorreoCalificadores($query->id_seguridad_sitio, __('messages.solicitud_anulada'));
                        $this->registrarHistorial_o_log($query->id_seguridad_sitio, 1, 'Cambio de estado', 'Solicitud ' . $query->id_registro_proyecto . ' cambio a estado anulado', null, $usuario->fk_usuario);
                        return $this->handleAlert(__('messages.solicitud_anulada'), true);

                    }
                    return $this->handleAlert(__('messages.error_al_anular_solicitud'), false);
                }
                return $this->handleAlert(__('messages.usted_no_puede_anular_esta_solicitud'), false);
            }
            return $this->handleAlert(__('messages.solicitud_no_puede_ser_anulada'), false);
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }


    /*Agrega comentarios en la solicitud
     *recibe una Request el cual contiene {identificador = clave primaria de la tabla, comentario = minuta o comentario}
     *devuelve  true o false dependiendo del estado del save()
     *si guarda = true
     *si falla = false
     */
    public function comentario(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'identificador' => 'required|numeric',
                'comentario' => 'required',
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $query = WbSeguridadSitio::where('id_seguridad_sitio', $req->identificador);
            $query = $this->filtrar($req, $query)->first();

            if ($query == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            //consultamos si la solicitud se encuentra en estado "en proceso" o "aprobado"
            if ($query->fk_id_estado == $this->estados['EnProceso'] || $query->fk_id_estado == $this->estados['Aprobado']) {
                $usuario = $this->traitGetMiUsuarioProyectoPorId($req);

                //$permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('CALIFICAR_SEGURIDAD_SITIO', $usuario->fk_rol);

                /* if ($usuario->fk_usuario == $query->usuario_creacion || count($permiso) > 0) {
                   
                }
                return $this->handleAlert(__('messages.usted_no_puede_anular_esta_solicitud'), false); */
                $this->registrarHistorial_o_log($query->id_seguridad_sitio, 0, 'Minuta', $req->comentario, null, $usuario->fk_usuario);
                return $this->handleAlert(__('messages.comentario_agregado_con_exito'), true);
            }
            return $this->handleAlert(__('messages.no_se_puede_añadir_comentarios_en_la_solicitud'), false);
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }



    /**
     * Genera un informe de seguridad de sitios basado en los filtros proporcionados.
     *
     * @param  Request  $req  La instancia de la solicitud HTTP.
     * @return JsonResponse  La respuesta JSON con el resultado del informe.
     */
    public function report(Request $req)
    {
        try {
            $fechaInicio_filtro = $req->fechaInicio;
            $fechaFinal_filtro = $req->fechaFinal;
            $estado_filtro = $req->estado;


            $query = WbSeguridadSitio::select(
                'id_seguridad_sitio',
                'id_registro_proyecto',
                'fk_id_turno_seguridad_sitio',
                'fecha_inicio',
                'fecha_finalizacion',
                'fk_id_tramo',
                'fk_id_hito',
                'otra_ubicacion',
                'abscisa',
                'observaciones',
                'fk_id_estado',
                'usuario_creacion',
                'fk_id_seguridad_sitio_traslado',
                'usuario_calificacion',
                'observaciones_calificacion',
                'usuario_modificacion',
                'observaciones_anulado_finalizado',
                'coordenadas_de_solicitud',
                'es_arma_fuego',
                'es_motorizado',
                DB::raw("CAST(fecha_creacion AS date) AS fecha_creacion")
            );

            if (($fechaInicio_filtro !== null && strlen($fechaInicio_filtro) > 0) || ($fechaFinal_filtro !== null && strlen($fechaFinal_filtro) > 0) || ($estado_filtro !== null && strlen($estado_filtro) > 0)) {
                if ($estado_filtro !== null && strlen($estado_filtro) > 0) {
                    switch ($estado_filtro) {
                        case 'RECHAZADO':
                            $query = $query->where('fk_id_estado', $this->estados['Rechazado']);
                            break;
                        case 'APROBADO':
                            $query = $query->where('fk_id_estado', $this->estados['Aprobado']);
                            break;
                        case 'ANULADO':
                            $query = $query->where('fk_id_estado', $this->estados['Anulado']);
                            break;
                        case 'PENDIENTE':
                            $query = $query->where('fk_id_estado', $this->estados['Pendiente']);
                            break;
                        case 'ENPROCESO':
                            $query = $query->where('fk_id_estado', $this->estados['EnProceso']);
                            break;
                        case 'FINALIZADO':
                            $query = $query->where('fk_id_estado', $this->estados['Finalizado']);
                            break;
                    }
                }

                if (($fechaInicio_filtro !== null && strlen($fechaInicio_filtro) > 0) && ($fechaFinal_filtro !== null && strlen($fechaFinal_filtro) > 0)) {
                    $query = $query->where('fecha_inicio', '>=', $fechaInicio_filtro)
                        ->where('fecha_finalizacion', '<=', $fechaFinal_filtro);
                }
            }
            //$proyecto = $this->traitGetProyectoCabecera($req);
            //$compa = $this->traitIdEmpresaPorProyecto($req);
            //$query = $query->where('fk_id_project_Company', $proyecto)->where('fk_compañia', $compa)
            $query = $this->filtrar2($req, $query)->orderBy('id_registro_proyecto', 'DESC')->get();
            if (sizeof($query) == 0) {
                return $this->handleAlert(__('messages.sin_registros_por_mostrar'), false);
            }

            $query = $this->formatearDatosExtrasDeLista($query);
            $query = $this->getNombreTurno($query);
            $query = $this->getMaquinariasYMaterialesExcel($query);
            $query = $this->getEstadosName($query);
            $query = $this->getTraslado($query);
            $query = $this->formatCalificador($query);
            $query = $this->getCalificador($query);
            $query = $this->getTiempoRestanteParaSerAnulado($query);
            $query = $this->columnasExtrasArmaFuegoMotorizado($query);
            foreach ($query as $key) {
                if ($key->prox_vencer == -1) {
                    $key->prox_vencer = __('messages.anulado_por_vencimiento_de_fecha_de_inicio');
                }
            }
            foreach ($query as &$key) {
                if ($key->observaciones_calificacion == null) {
                    $key->observaciones_calificacion = $key->observaciones_anulado_finalizado;
                }
            }
            return $this->handleResponse($req, $this->wbSeguridadSitioExcelToArray($query), __('messages.consultado'));
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Formatea la propiedad 'usuario_calificacion' en el conjunto de datos.
     *
     * @param  array  $array  El conjunto de datos a formatear.
     * @return array  El conjunto de datos con la propiedad 'usuario_calificacion' formateada.
     */
    private function formatCalificador($array)
    {
        foreach ($array as &$key) {
            if ($key->usuario_calificacion == null) {
                $key->usuario_calificacion = $key->usuario_modificacion;
            }
        }
        return $array;
    }

    /**
     * Agrega columnas adicionales ('arma_fuego' y 'motorizado') al conjunto de datos
     * basadas en las propiedades 'es_arma_fuego' y 'es_motorizado'.
     *
     * @param  array  $array  El conjunto de datos al que se le agregarán las columnas.
     * @return array  El conjunto de datos con las columnas adicionales.
     */
    private function columnasExtrasArmaFuegoMotorizado($array)
    {
        if (sizeof($array) > 0) {
            foreach ($array as $key) {

                if ($key->es_arma_fuego == null && $key->es_motorizado == null) {
                    $key->arma_fuego = 'N/A';
                    $key->motorizado = 'N/A';
                    continue;
                }

                if ($key->es_arma_fuego == 1) {
                    $key->arma_fuego = 'SI';
                } else if ($key->es_arma_fuego == 0) {
                    $key->arma_fuego = 'NO';
                } else {
                    $key->arma_fuego = 'N/A';
                }

                if ($key->es_motorizado == 1) {
                    $key->motorizado = 'SI';
                } else if ($key->es_motorizado == 0) {
                    $key->motorizado = 'NO';
                } else {
                    $key->motorizado = 'N/A';
                }

            }
        }
        return $array;
    }

    /**
     * Obtiene información sobre maquinarias y materiales y agrega las propiedades
     * 'maquinarias' y 'materiales' a cada elemento del conjunto de datos.
     *
     * @param  array  $array  El conjunto de datos al que se le agregarán las propiedades.
     * @return array  El conjunto de datos con las propiedades 'maquinarias' y 'materiales'.
     */
    private function getMaquinariasYMaterialesExcel($array)
    {
        if (sizeof($array) > 0) {
            $maquinarias = '';
            $materiales = '';
            foreach ($array as $key) {
                $result = WbSeguridadSitioEquipo::select('equiment_id', 'descripcion', 'marca', 'modelo', 'placa')
                    ->join('Wb_equipos', 'Wb_equipos.id', 'fk_id_equipo')
                    ->where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                    ->where('es_inicial', 1)->get();
                if (sizeof($result) > 0) {
                    $msgMaquinarias = '';
                    foreach ($result as $key1) {
                        $msgMaquinarias .= $key1->equiment_id . '/' . $key1->descripcion . "\n";
                    }
                    $maquinarias = $msgMaquinarias;
                }

                $result = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $key->id_seguridad_sitio)
                    ->where('es_inicial', 1)->get();
                if (sizeof($result) > 0) {
                    $msgMateriales = '';
                    foreach ($result as $key1) {
                        $msgMateriales .= $key1->material . '/' . $key1->cantidad . ' ' . $key1->unidad_medida . "\n";
                    }
                    $materiales = $msgMateriales;
                }
                $key->maquinarias = $maquinarias;
                $key->materiales = $materiales;
            }
        }
        return $array;
    }
    /* Fin Aplicacion Web */

    /* Correos */

    /**
     * Envía correos electrónicos a los calificadores asociados a una solicitud de seguridad de sitio.
     *
     * @param  int|string  $solicitud  Identificador de la solicitud.
     * @param  string  $mensaje  Mensaje que se incluirá en el correo electrónico.
     * @return void
     */
    public function enviarCorreoCalificadores($solicitud, $mensaje)
    {
        $soli = $this->getSolicitudParaCorreo($solicitud);
        if (sizeof($soli) > 0) {
            $soli = $this->formatearDatosExtrasDeLista($soli);
            $soli = $this->getNombreTurno($soli);
            $soli = $this->getMaquinariasYMaterialesExcel($soli);
            $soli = $this->getEstadosName($soli);
            $soli = $this->getTraslado($soli);
            $soli = $this->getCalificador($soli);
            $soli = $this->getTiempoRestanteParaSerAnulado($soli);
            $list_Correos = null;
            foreach ($soli as $key) {
                $list_Correos = $this->getCorreosCalificadores($key->fk_id_project_Company);
            }
            if (sizeof($list_Correos) > 0) {
                foreach ($soli as $so) {
                    foreach ($list_Correos as $key) {
                        if ($key->Correo != null) {
                            Mail::to($key->Correo)->send(new notificacionesSeguridadSitio($so, $mensaje));
                        }
                    }
                }

            }
        }

    }

    /**
     * Envía correos electrónicos al solicitante de una solicitud de seguridad de sitio.
     *
     * @param  int|string  $solicitud  Identificador de la solicitud.
     * @param  string  $mensaje  Mensaje que se incluirá en el correo electrónico.
     * @return void
     */
    public function enviarCorreoSolicitante($solicitud, $mensaje)
    {
        $soli = $this->getSolicitudParaCorreo($solicitud);
        if (sizeof($soli) > 0) {
            $soli = $this->formatearDatosExtrasDeLista($soli);
            $soli = $this->getNombreTurno($soli);
            $soli = $this->getMaquinariasYMaterialesExcel($soli);
            $soli = $this->getEstadosName($soli);
            $soli = $this->getTraslado($soli);
            $soli = $this->getCalificador($soli);
            $soli = $this->getTiempoRestanteParaSerAnulado($soli);
            $correoSolicitante = null;
            foreach ($soli as $key) {
                $correoSolicitante = usuarios_M::select('Correo')->where('id_usuarios', $key->usuario_creacion)->whereNotNull('usuarioss.Correo')->first();
            }
            if ($correoSolicitante != null) {
                foreach ($soli as $key) {
                    if ($correoSolicitante->Correo != null) {
                        Mail::to($correoSolicitante->Correo)->send(new notificacionesSeguridadSitio($key, $mensaje));
                    }
                }

            }
        }

    }

    /**
     * Obtiene los correos electrónicos de los usuarios con el permiso de calificar seguridad de sitio en un proyecto.
     *
     * @param  int|string  $proyecto  Identificador del proyecto.
     * @return \Illuminate\Support\Collection  Colección de correos electrónicos de los usuarios calificadores.
     */
    private function getCorreosCalificadores($proyecto)
    {
        $usuarios = usuarios_M::select('usuarioss.Correo')
            ->leftJoin('Wb_Usuario_Proyecto', 'fk_usuario', 'id_usuarios')
            ->leftJoin('Wb_Seguri_Roles_Permisos', 'Wb_Seguri_Roles_Permisos.fk_id_rol', 'Wb_Usuario_Proyecto.fk_rol')
            ->leftJoin('Wb_Seguri_Permisos', 'Wb_Seguri_Roles_Permisos.fk_id_permiso', 'Wb_Seguri_Permisos.id_permiso')
            ->where('Wb_Seguri_Permisos.nombrePermiso', 'CALIFICAR_SEGURIDAD_SITIO')
            ->where('Wb_Usuario_Proyecto.fk_id_project_Company', $proyecto)
            ->where('usuarioss.estado', 'A')
            ->whereNotNull('usuarioss.Correo')
            ->get();

        return $usuarios;

    }

    /**
     * Obtiene información específica de una solicitud de seguridad de sitio.
     *
     * @param  int|string  $id_seguridad_sitio  Identificador de la solicitud de seguridad de sitio.
     * @return \Illuminate\Support\Collection  Colección de información de la solicitud de seguridad de sitio.
     */
    private function getSolicitudParaCorreo($id_seguridad_sitio)
    {
        $solicitud = WbSeguridadSitio::select(
            'id_seguridad_sitio',
            'id_registro_proyecto',
            'fk_id_turno_seguridad_sitio',
            'fecha_inicio',
            'fecha_finalizacion',
            'fk_id_tramo',
            'fk_id_hito',
            'abscisa',
            'otra_ubicacion',
            'observaciones',
            'fk_id_estado',
            'usuario_creacion',
            'fk_id_seguridad_sitio_traslado',
            'usuario_calificacion',
            'observaciones_calificacion',
            'fk_id_project_Company'
        )
            ->where('id_seguridad_sitio', $id_seguridad_sitio)->get();

        return $solicitud;
    }
    /* Fin Correos */


    /*Procesos en segundo plano */

    /**
     * Inicia las solicitudes de seguridad de sitio que cumplen con ciertos criterios.
     *
     * @return void
     */
    public function iniciarSolicitudes()
    {
        try {
            $solicitudes = WbSeguridadSitio::select('id_seguridad_sitio', 'id_registro_proyecto', 'fk_id_estado', 'fecha_modificacion')->where('fk_id_estado', 27)
                ->where(function ($consulta) {
                    $consulta->orWhere('fecha_inicio', DB::raw("CAST(GETDATE() AS DATE)"));
                    $consulta->orWhere(function ($subconsulta) {
                        $subconsulta->where('fecha_inicio', '<=', DB::raw("CAST(GETDATE() AS DATE)"))
                            ->where('fecha_finalizacion', '>=', DB::raw("CAST(GETDATE() AS DATE)"));
                    });
                })->get();
            if (sizeof($solicitudes) > 0) {
                foreach ($solicitudes as $key) {
                    $key->fk_id_estado = $this->estados['EnProceso'];
                    $key->fecha_modificacion = DB::raw('SYSDATETIME()');
                    $key->save();
                    $this->registrarHistorial_o_log($key->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.solicitud') . ' ' . $key->id_registro_proyecto . ' ' . __('messages.cambio_a_estado_en_proceso'), null, null);
                    //$this->enviarCorreoSolicitante($key->id_seguridad_sitio, 'Solicitud en proceso');
                    //$this->enviarCorreoCalificadores($key->id_seguridad_sitio, 'Solicitud en proceso');
                }
            }
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }

    /**
     * Finaliza o anula las solicitudes de seguridad de sitio que cumplen con ciertos criterios.
     *
     * @return void
     */
    public function finalizar_anular_solicitudes()
    {
        try {
            $solicitudes = WbSeguridadSitio::select('id_seguridad_sitio', 'id_registro_proyecto', 'fk_id_estado', 'fecha_modificacion')->where('fk_id_estado', 29)->where('fecha_finalizacion', DB::raw("CAST (GETDATE() AS DATE)"))->get();
            if (sizeof($solicitudes) > 0) {
                foreach ($solicitudes as $key) {
                    $key->fk_id_estado = $this->estados['Finalizado'];
                    $key->fecha_modificacion = DB::raw('SYSDATETIME()');
                    $key->save();
                    $this->enviarCorreoSolicitante($key->id_seguridad_sitio, __('messages.solicitud_finalizada'));
                    $this->enviarCorreoCalificadores($key->id_seguridad_sitio, __('messages.solicitud_finalizada'));
                    $this->registrarHistorial_o_log($key->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.solicitud') . ' ' . $key->id_registro_proyecto . ' ' . __('messages.cambio_a_estado_finalizado'), null, null);
                }
            }
            $solicitudes = WbSeguridadSitio::select('id_seguridad_sitio', 'id_registro_proyecto', 'fk_id_estado', 'observaciones_anulado_finalizado', 'fecha_modificacion')->where('fk_id_estado', 12)->where('fecha_inicio', '<=', DB::raw("CAST (GETDATE() AS DATE)"))->get();
            if (sizeof($solicitudes) > 0) {
                foreach ($solicitudes as $key) {
                    $key->fk_id_estado = $this->estados['Anulado'];
                    $key->observaciones_anulado_finalizado = __('messages.anulado_por_vencimiento_de_fecha_de_inicio');
                    $key->fecha_modificacion = DB::raw('SYSDATETIME()');
                    $key->save();
                    $this->enviarCorreoSolicitante($key->id_seguridad_sitio, __('messages.anulado_por_vencimiento_de_fecha_de_inicio'));
                    $this->enviarCorreoCalificadores($key->id_seguridad_sitio, __('messages.anulado_por_vencimiento_de_fecha_de_inicio'));
                    $this->registrarHistorial_o_log($key->id_seguridad_sitio, 1, __('messages.cambio_de_estado'), __('messages.solicitud') . ' ' . $key->id_registro_proyecto . ' ' . __('messages.cambio_a_estado_anulado_por_vencimiento_de_fecha_de_inicio'), null, null);
                }
            }
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
    /* Fin en segundo plano */
}