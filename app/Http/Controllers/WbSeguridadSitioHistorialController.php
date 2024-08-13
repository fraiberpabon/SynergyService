<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\usuarios_M;
use App\Models\WbSeguridadSitio;
use App\Models\WbSeguridadSitioEquipo;
use App\Models\WbSeguridadSitioEvidencia;
use App\Models\WbSeguridadSitioHistorial;
use App\Models\WbSeguridadSitioMaterial;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WbSeguridadSitioHistorialController extends BaseController implements Vervos
{
    //
    /**
     * Guarda un registro de historial de seguridad de sitio en la base de datos.
     *
     * @param  WbSeguridadSitioHistorial  $historial
     * @return bool
     */
    public function guardar(WbSeguridadSitioHistorial $historial)
    {
        if ($historial != null) {
            $historial->fecha_registro = DB::raw('CAST(GETDATE() AS DATE)');
            $historial->hora_registro = DB::raw('CAST(GETDATE() AS TIME(0))');
            if ($historial->save()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Crea y devuelve un nuevo objeto WbSeguridadSitioHistorial con los datos proporcionados.
     *
     * @param  int|null  $fk_id_seguridad_sitio
     * @param  bool  $is_log
     * @param  string  $evento
     * @param  string  $contenido
     * @param  int|null  $id_evidencia
     * @param  int|null  $id_usuario_evento
     * @return WbSeguridadSitioHistorial
     */
    public function crearRegistro($fk_id_seguridad_sitio = null, $is_log, $evento, $contenido, $id_evidencia = null, $id_usuario_evento = null)
    {
        $historial = new WbSeguridadSitioHistorial();
        $historial->fk_id_seguridad_sitio = $fk_id_seguridad_sitio;
        $historial->is_log = $is_log;
        $historial->evento = $evento;
        $historial->observaciones = $contenido;
        $historial->id_evidencia = $id_evidencia;
        $historial->usuario_evento = $id_usuario_evento;

        return $historial;
    }

    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request)
    {
        // TODO: Implement post() method
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
            $solicitud = WbSeguridadSitio::select('id_seguridad_sitio', 'fk_id_project_Company')->where('id_registro_proyecto', $request->identificador);
            $solicitud = $this->filtrar($request, $solicitud)->first();


            if ($solicitud == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            $query = WbSeguridadSitioHistorial::select(
                'id_seguridad_sitio_historial',
                'evento',
                'observaciones',
                'usuario_evento',
                'id_evidencia',
                'fecha_registro',
                DB::raw("CAST(hora_registro AS TIME(0)) AS hora_registro"),
            )
                ->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                ->orderBy('fecha_registro', 'desc')
                ->orderBy('hora_registro', 'desc')
                ->get();

            if (sizeof($query) == 0) {
                return $this->handleAlert(__('messages.sin_registros_por_mostrar'), false);
            }

            $query->proyecto = $solicitud->fk_id_project_Company;

            $query = $this->formatearDatosExtrasDeLista($query);

            return $this->handleResponse($request, $this->wbSeguridadSitioHistorialToArray($query), __('messages.consultado'));
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Formatea los datos extras de una lista de objetos, específicamente los nombres de usuario.
     *
     * @param  array  $array  La lista de objetos a formatear.
     * @return array|null      La lista de objetos formateada.
     */
    private function formatearDatosExtrasDeLista($array)
    {
        if (sizeof($array) == 0) {
            return;
        }

        foreach ($array as $key) {
            $query = usuarios_M::select('Nombre', 'Apellido')->where('id_usuarios', $key->usuario_evento)->first();
            if ($query == null) {
                continue;
            }

            $nombre = Str::upper($query->Nombre) . ' ' . Str::upper($query->Apellido);
            $key->usuario_evento = $nombre;
        }

        return $array;
    }

    /**
     * Obtiene elementos asociados a una solicitud de seguridad, como maquinarias y materiales.
     *
     * @param  Request  $req  La solicitud HTTP.
     * @return \Illuminate\Http\JsonResponse  La respuesta JSON.
     */
    public function getElementos(Request $req)
    {
        try {
            $historial = WbSeguridadSitioHistorial::where('id_seguridad_sitio_historial', $req->identificador)->first();

            if ($historial == null) {
                return $this->handleAlert(__('messages.historial_no_encontrado'), false);
            }

            $solicitud = WbSeguridadSitio::where('id_seguridad_sitio', $historial->fk_id_seguridad_sitio)->first();

            if ($solicitud == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            $maquinarias = null;
            $materiales = null;

            if (is_numeric($req->es_inicial) && $req->es_inicial == 1) {

                //buscamos los equipos
                $maqui = WbSeguridadSitioEquipo::select('equiment_id', 'descripcion', 'marca', 'modelo', 'placa')
                    ->join('Wb_equipos', 'Wb_equipos.id', 'fk_id_equipo')
                    ->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                    ->where('es_inicial', $req->es_inicial)->get();
                if (sizeof($maqui) > 0) {
                    $maquinarias = $this->equiposToArray($maqui);
                }

                //buscamos los materiales
                $mate = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)->where('es_inicial', $req->es_inicial)->get();
                if (sizeof($mate) > 0) {
                    $materiales = $this->wbSeguridadSitioMaterialToArray($mate);
                }


                $historial->maquinarias = $maquinarias;
                $historial->materiales = $materiales;

                return $this->handleResponse($req, $this->wbSeguridadSitioHistorialToModel($historial), __('messages.consultado'));
            }

            //buscamos los equipos
            $maqui = WbSeguridadSitioEquipo::select('equiment_id', 'descripcion', 'marca', 'modelo', 'placa')
                ->join('Wb_equipos', 'Wb_equipos.id', 'fk_id_equipo')
                ->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                ->where('fecha_registro', $req->fecha)
                ->where('es_inicial', 0)->get();
            if (sizeof($maqui) > 0) {
                $maquinarias = $this->equiposToArray($maqui);
            }

            //buscamos los materiales
            $mate = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                ->where('fecha_registro', $req->fecha)
                ->where('es_inicial', 0)->get();
            if (sizeof($mate) > 0) {
                $materiales = $this->wbSeguridadSitioMaterialToArray($mate);
            }

            if ($maquinarias == null && $materiales == null) {
                //buscamos los equipos
                $maqui = WbSeguridadSitioEquipo::select('fecha_registro')->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                    ->where('fecha_registro', '!=', null)
                    ->orderBy('fecha_registro', 'desc')->first();

                //buscamos los materiales
                $mate = WbSeguridadSitioMaterial::select('fecha_registro')->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                    ->where('fecha_registro', '!=', null)
                    ->orderBy('fecha_registro', 'desc')->first();

                if ($maqui != null && $mate != null) {
                    $fecha_maqui = strtotime($maqui->fecha_registro);
                    $fecha_mate = strtotime($mate->fecha_registro);

                    if ($fecha_maqui > $fecha_mate) {
                        $maquinarias = $this->getMaquinariasList($solicitud);
                        $materiales = null;
                    } else if ($fecha_mate > $fecha_maqui) {
                        $maquinarias = null;
                        $materiales = $this->getMaterialesList($solicitud);
                    } else {
                        $maquinarias = $this->getMaquinariasList($solicitud);
                        $materiales = $this->getMaterialesList($solicitud);
                    }
                } else if ($maqui != null && $mate == null) {
                    $maquinarias = $this->getMaquinariasList($solicitud);
                    $materiales = null;
                } else {
                    $maquinarias = null;
                    $materiales = $this->getMaterialesList($solicitud);
                }
            }

            $historial->maquinarias = $maquinarias;
            $historial->materiales = $materiales;

            return $this->handleResponse($req, $this->wbSeguridadSitioHistorialToModel($historial), __('messages.consultado'));
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Obtiene una lista de maquinarias asociadas a una solicitud de seguridad en base a la fecha de registro más reciente.
     *
     * @param  WbSeguridadSitio  $solicitud  La solicitud de seguridad.
     * @return array|null  La lista de maquinarias en formato de array o null si no se encontraron maquinarias.
     */
    private function getMaquinariasList($solicitud)
    {
        $maqui = WbSeguridadSitioEquipo::select('fecha_registro')->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
            ->where('fecha_registro', '!=', null)
            ->orderBy('fecha_registro', 'desc')->first();

        if ($maqui != null) {
            $maqui = WbSeguridadSitioEquipo::select('equiment_id', 'descripcion', 'marca', 'modelo', 'placa')
                ->join('Wb_equipos', 'Wb_equipos.id', 'fk_id_equipo')
                ->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                ->where('fecha_registro', $maqui->fecha_registro)->get();
            if (sizeof($maqui) > 0) {
                return $this->equiposToArray($maqui);
            }
        }
        return null;
    }

    /**
     * Obtiene una lista de materiales asociados a una solicitud de seguridad en base a la fecha de registro más reciente.
     *
     * @param  WbSeguridadSitio  $solicitud  La solicitud de seguridad.
     * @return array|null  La lista de materiales en formato de array o null si no se encontraron materiales.
     */
    private function getMaterialesList($solicitud)
    {
        $mate = WbSeguridadSitioMaterial::select('fecha_registro')->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
            ->where('fecha_registro', '!=', null)
            ->orderBy('fecha_registro', 'desc')->first();

        if ($mate != null) {
            $mate = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)->where('fecha_registro', $mate->fecha_registro)->get();
            if (sizeof($mate) > 0) {
                return $this->wbSeguridadSitioMaterialToArray($mate);
            }
        }
        return null;
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }


    /* APLICACION WEB */

    /**
     * Obtiene el historial de eventos asociados a una solicitud de seguridad para la visualización en la web.
     *
     * @param  Request  $req  La solicitud HTTP.
     * @return JsonResponse  La respuesta JSON con los datos del historial o un mensaje de alerta en caso de error.
     */
    public function getHistorialWeb(Request $req)
    {
        try {
            if (!is_numeric($req->page) || !is_numeric($req->limit)) {
                return $this->handleAlert('Datos invalidos');
            }

            if (!is_numeric($req->identificador)) {
                return $this->handleAlert('Datos invalidos');
            }

            $pagina = $req->page;
            $limite = $req->limit;

            $fechaInicio_filtro = $req->fechaInicio;
            $fechaFinal_filtro = $req->fechaFinal;


            $query = WbSeguridadSitioHistorial::select(
                'id_seguridad_sitio_historial',
                'fk_id_seguridad_sitio',
                'evento',
                'observaciones',
                'usuario_evento',
                'id_evidencia',
                'fecha_registro',
                DB::raw("CAST(hora_registro AS TIME(0)) AS hora_registro"),
            )->where('fk_id_seguridad_sitio', $req->identificador);

            if (($fechaInicio_filtro !== null && strlen($fechaInicio_filtro) > 0) || ($fechaFinal_filtro !== null && strlen($fechaFinal_filtro) > 0)) {

                if (($fechaInicio_filtro !== null && strlen($fechaInicio_filtro) > 0) && ($fechaFinal_filtro !== null && strlen($fechaFinal_filtro) > 0)) {
                    $query = $query->where('fecha_inicio', '>=', $fechaInicio_filtro)
                        ->where('fecha_finalizacion', '<=', $fechaFinal_filtro);
                }
            }
            //$proyecto = $this->traitGetProyectoCabecera($req);
            //$compa = $this->traitIdEmpresaPorProyecto($req);
            //$query = $query->where('fk_id_project_Company', $proyecto)->where('fk_compañia', $compa)
            $query = $query->orderBy('fecha_registro', 'DESC')->orderBy('hora_registro', 'DESC');
            if (sizeof($query->get()) == 0) {
                return $this->handleAlert(__('messages.sin_registros_por_mostrar'), false);
            }

            $limitePagina = 1;
            $contador = clone $query;
            $contador = $contador->select('id_seguridad_sitio_historial')->count();
            $query = $query->forPage($pagina, $limite)->get();
            $limitePagina = ($contador / $limite);
            if ($limitePagina <= 1) {
                $limitePagina = $limitePagina = 1;
            }

            //$query = $this->getMaterialesYMaquinarias($query);

            $query = $this->agregarColumnaDeInformeDelDia($query);

            $query = $this->getUsuarioEvento($query);

            $query = $this->agregarElementosEnArrayPorIdHistorial($query);

            return $this->handleResponse($req, $this->wbSeguridadSitioHistorialToArray($query), __('messages.consultado'), ceil($limitePagina));
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Obtiene y agrega el nombre completo del usuario asociado a cada evento en el historial.
     *
     * @param  array  $array  El array de objetos de historial de eventos.
     * @return array  El array de objetos de historial de eventos con el nombre completo del usuario agregado.
     */
    private function getUsuarioEvento($array)
    {
        foreach ($array as $key) {
            if ($key->usuario_evento == null) {
                continue;
            }
            $query = usuarios_M::select('Nombre', 'Apellido')->where('id_usuarios', $key->usuario_evento)->first();
            if ($query != null) {
                $nombre = Str::upper($query->Nombre) . ' ' . Str::upper($query->Apellido);
                $key->usuario_evento_name = $nombre;
            }
        }
        return $array;
    }

    /**
     * Agrega entradas de "Informe del día" al historial de eventos.
     *
     * @param  array  $array  El array de objetos de historial de eventos.
     * @return \Illuminate\Support\Collection  Una colección que contiene el historial original con las entradas de "Informe del día" agregadas.
     */
    private function agregarColumnaDeInformeDelDia($array)
    {
        $dato = collect();
        $anterior = null;
        $ultimo = null;
        foreach ($array as $key) {
            if ($ultimo == null) {
                $ultimo = WbSeguridadSitioHistorial::select('id_seguridad_sitio_historial')
                    ->where('fk_id_seguridad_sitio', $key->fk_id_seguridad_sitio)
                    ->orderBy('id_seguridad_sitio_historial', 'asc')->first();
            }
            if ($anterior == null) {
                $anterior = clone ($key);
                $nuevo = clone ($key);


                $nuevo->id_seguridad_sitio_historial = null;
                $nuevo->evento = 'Informe del dia';
                $nuevo->observaciones = null;
                $nuevo->usuario_evento = null;
                $nuevo->hora_registro = null;
                $nuevo->es_inicial = 0;

                // Consultamos evidencias
                foreach ($array as $key2) {
                    if ($key->fecha_registro === $key2->fecha_registro && isset($key2->id_evidencia)) {
                        $nuevo->id_evidencia = $key2->id_evidencia;
                        break;
                    }
                }

                $dato->push($nuevo);

                $dato->push($anterior);
                continue;
            }

            if ($key->fecha_registro !== $anterior->fecha_registro) {
                $nuevo = clone ($key);

                $nuevo->id_seguridad_sitio_historial = null;
                $nuevo->evento = __('messages.informe_del_dia');
                $nuevo->observaciones = null;
                $nuevo->usuario_evento = null;
                $nuevo->hora_registro = null;
                $nuevo->es_inicial = 0;

                // Consultamos evidencias
                foreach ($array as $key2) {
                    if ($key->fecha_registro == $key2->fecha_registro && isset($key2->id_evidencia)) {
                        $nuevo->id_evidencia = $key2->id_evidencia;
                        break;
                    }
                }

                $anterior = clone ($key);

                $dato->push($nuevo);

                $dato->push($key);
                continue;
            }

            if ($key->id_seguridad_sitio_historial == $ultimo->id_seguridad_sitio_historial) {
                $key->es_inicial = 1;
                $dato->push($key);
                continue;
            }

            $dato->push($key);
        }
        return $dato;
    }

    /**
     * Agrega información adicional (equipos y materiales) al array de historial de eventos.
     *
     * @param  array  $array  El array de objetos de historial de eventos.
     * @return array  El array de historial de eventos con información adicional agregada.
     */
    public function agregarElementosEnArrayPorIdHistorial($array)
    {

        if (sizeof($array) > 0) {
            foreach ($array as $key) {
                $solicitud = WbSeguridadSitio::select('id_seguridad_sitio')->where('id_seguridad_sitio', $key->fk_id_seguridad_sitio)->first();

                if ($solicitud == null) {
                    continue;
                }

                $maquinarias = null;
                $materiales = null;
                if (!isset($key->es_inicial)) {
                    continue;
                }

                if (is_numeric($key->es_inicial) && $key->es_inicial == 1) {
                    //buscamos los equipos
                    $maqui = WbSeguridadSitioEquipo::select('equiment_id', 'descripcion', 'marca', 'modelo', 'placa')
                        ->join('Wb_equipos', 'Wb_equipos.id', 'fk_id_equipo')
                        ->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                        ->where('es_inicial', $key->es_inicial)->get();
                    if (sizeof($maqui) > 0) {
                        $maquinarias = $this->equiposToArray($maqui);
                    }

                    //buscamos los materiales
                    $mate = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)->where('es_inicial', $key->es_inicial)->get();
                    if (sizeof($mate) > 0) {
                        $materiales = $this->wbSeguridadSitioMaterialToArray($mate);
                    }


                    $key->maquinarias = $maquinarias;
                    $key->materiales = $materiales;
                    continue;
                }

                //buscamos los equipos
                $maqui = WbSeguridadSitioEquipo::select('equiment_id', 'descripcion', 'marca', 'modelo', 'placa')
                    ->join('Wb_equipos', 'Wb_equipos.id', 'fk_id_equipo')
                    ->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                    ->where('fecha_registro', $key->fecha)
                    ->where('es_inicial', 0)->get();
                if (sizeof($maqui) > 0) {
                    $maquinarias = $this->equiposToArray($maqui);
                }

                //buscamos los materiales
                $mate = WbSeguridadSitioMaterial::where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                    ->where('fecha_registro', $key->fecha)
                    ->where('es_inicial', 0)->get();
                if (sizeof($mate) > 0) {
                    $materiales = $this->wbSeguridadSitioMaterialToArray($mate);
                }

                if ($maquinarias == null && $materiales == null) {
                    //buscamos los equipos
                    $maqui = WbSeguridadSitioEquipo::select('fecha_registro')->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                        ->where('fecha_registro', '!=', null)
                        ->orderBy('fecha_registro', 'desc')->first();

                    //buscamos los materiales
                    $mate = WbSeguridadSitioMaterial::select('fecha_registro')->where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                        ->where('fecha_registro', '!=', null)
                        ->orderBy('fecha_registro', 'desc')->first();

                    if ($maqui != null && $mate != null) {
                        $fecha_maqui = strtotime($maqui->fecha_registro);
                        $fecha_mate = strtotime($mate->fecha_registro);

                        if ($fecha_maqui > $fecha_mate) {
                            $maquinarias = $this->getMaquinariasList($solicitud);
                            $materiales = null;
                        } else if ($fecha_mate > $fecha_maqui) {
                            $maquinarias = null;
                            $materiales = $this->getMaterialesList($solicitud);
                        } else {
                            $maquinarias = $this->getMaquinariasList($solicitud);
                            $materiales = $this->getMaterialesList($solicitud);
                        }
                    } else if ($maqui != null && $mate == null) {
                        $maquinarias = $this->getMaquinariasList($solicitud);
                        $materiales = null;
                    } else {
                        $maquinarias = null;
                        $materiales = $this->getMaterialesList($solicitud);
                    }
                }

                $key->maquinarias = $maquinarias;
                $key->materiales = $materiales;
                continue;
            }
        }
        return $array;
    }



    /* FIN APLICACION WEB */
}