<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbSeguridadSitio;
use App\Models\WbSeguridadSitioEvidencia;
use DB;
use Illuminate\Http\Request;
use Validator;

class WbSeguridadSitioEvidenciaController extends BaseController implements Vervos
{
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
     * @return void
     */
    public function get(Request $request)
    {
        // TODO: Implement get() method.


    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    /* METODO UTILIZADO PARA OPTENER LA EVIDENCIA DESDE EL HISTORIAL DE CAMBIOS
    
    DEVUELVE LA EVIDENCIA POR MEDIO DEL ID DE EVIDENCIA
    EN CASO DE NO ENCONTRAR EL ID DE EVIDENCIA ENTONCES RECIBE ENTONCES 
    FILTRA POR FECHA Y NUMERO DE LA SOLICITUD 
    
    ENTRADA:
    identificador = id_evidencia / numero de la solicitud

    SALIDA:
    modelo de evidencia en formato json */
    public function getEvidencias(Request $req)
    {
        try {
            $solicitud = null;
            if (is_null($req->identificador)) {
                $solicitud = WbSeguridadSitioEvidencia::where('fecha_registro', $req->fecha)->where('estado', 1)->first();
            } else {
                $solicitud = WbSeguridadSitioEvidencia::where('id_seguridad_sitio_evidencia', $req->identificador)->first();
            }

            if ($solicitud == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            return $this->handleResponse($req, $this->wbSeguridadSitioEvidenciaToModel($solicitud), __('messages.consultado'));
        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Obtiene la evidencia asociada a una solicitud de seguridad de sitio mediante su identificador.
     *
     * @param  Request  $req
     * @return \Illuminate\Http\Response
     */
    public function getEvidenciaPorSolicitud(Request $req)
    {
        try {
            if (!isset($req->identificador)) {
                return $this->handleAlert('messages.solicitud_no_ingresada', false);
            }

            if (!is_numeric($req->identificador)) {
                return $this->handleAlert(__('messages.datos_invalidos'), false);
            }

            $solicitud = WbSeguridadSitio::where('id_registro_proyecto', $req->identificador);
            $solicitud = $this->filtrar2($req, $solicitud)->first();

            if ($solicitud == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            $evidencia = WbSeguridadSitioEvidencia::where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)->where('fecha_registro', DB::raw('CAST(GETDATE() AS DATE)'))->where('estado', 1)->first();

            if ($evidencia == null) {
                return $this->handleAlert(__('messages.no_se_encontraron_evidencias'), false);
            }

            return $this->handleResponse($req, $this->wbSeguridadSitioEvidenciaToModel($evidencia), __('messages.consultado'));

        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Obtiene la última evidencia activa asociada a una solicitud de seguridad de sitio mediante su identificador.
     *
     * @param  Request  $req
     * @return \Illuminate\Http\Response
     */
    public function getUltimaEvidenciaActivaPorSolicitud(Request $req)
    {
        try {

            $validator = Validator::make($req->all(), [
                'identificador' => 'required|numeric',
            ]);

            // Comprobar si la validación falla y devolver los errores
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $solicitud = WbSeguridadSitio::where('id_seguridad_sitio', $req->identificador)->first();

            if ($solicitud == null) {
                return $this->handleAlert(__('messages.solicitud_no_encontrada'), false);
            }

            $evidencia = WbSeguridadSitioEvidencia::where('fk_id_seguridad_sitio', $solicitud->id_seguridad_sitio)
                ->where('estado', 1)
                ->orderBy('fecha_registro', 'desc')->first();

            if ($evidencia == null) {
                return $this->handleAlert(__('messages.no_se_encontraron_evidencias'), false);
            }

            return $this->handleResponse($req, $this->wbSeguridadSitioEvidenciaToModel($evidencia), __('messages.consultado'));

        } catch (\Throwable $e) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($e->getMessage(), false);
        }
    }
}