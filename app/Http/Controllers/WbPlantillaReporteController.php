<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbPlantillaReporte;
use App\Models\WbTipoFormato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WbPlantillaReporteController extends BaseController implements Vervos
{

    /**
     * @param Request $req
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
        try {
            if (WbTipoFormato::find($req->tipoFormato) == null) {
                return $this->handleAlert(__('messages.tipo_de_formato_no_encontrado'));
            }
            if (WbPlantillaReporte::where('fk_tipo_formato',)
                    ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                    ->first() != null) {
                return $this->handleAlert(__('messages.ya_existe_una_plantilla_con_el_tipo_de_formato_a_registrar_en_el_sistema'));
            }
            $validator = Validator::make($req->all(), [
                'nombre' => 'required|string|max:100',
                'url' => 'required|string|max:250',
                'tipoFormato' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors()->all());
            }
            $modeloRegistrar = new WbPlantillaReporte;
            $modeloRegistrar->nombre =$req->nombre;
            $modeloRegistrar->url =$req->url;
            $modeloRegistrar->fk_tipo_formato = $req->tipoFormato;
            $modeloRegistrar->fk_id_project_Company = $this->traitGetProyectoCabecera($req);
            $modeloRegistrar->estado = 1;
            $modeloRegistrar->date_created = $this->traitGetDateTimeNow();
            $modeloRegistrar->fk_usuarioss = $this->traitGetIdUsuarioToken($req);
            if ($modeloRegistrar->save()) {
                return $this->handleResponse($req, [], __('messages.plantilla_registrada'));
            } else {
                return $this->handleAlert(__('messages.ocurrio_un_error_mientras_se_registraba_la_plantilla_intente_de_nuevo_si_el_error_persiste_consulte_con_el_administrador'));
            }
        } catch (\Exception $exc){
            var_dump($exc);
        }
    }

    /**
     * @param Request $req
     * @param $id
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function cambiarUrlYNombre(Request $request, $idPlantilla) {
        if (!is_numeric($idPlantilla)) {
            return $this->handleAlert(__('messages.tipo_formato_no_valido'));
        }
        $planilla = WbPlantillaReporte::find($idPlantilla);
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'url' => 'required|string|max:250',
        ]);
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors()->all());
        }
        if ($planilla == null) {
            return $this->handleAlert(__('messages.plantilla_reporte_no_encontrada'));
        }
        $planilla->nombre = $request->nombre;
        $planilla->url = $request->url;
        $planilla->save();
        return $this->handleResponse($request, [], __('messages.plantilla_reporte_modificada'));
    }

    public function bloquearPlantilla(Request $request, $idPlantilla) {
        if (!is_numeric($idPlantilla)) {
            return $this->handleAlert(__('messages.tipo_formato_no_valido'));
        }
        $planilla = WbPlantillaReporte::where('fk_id_project_Company', $this->traitGetProyectoCabecera($request))
            ->find($idPlantilla);
        if ($planilla == null) {
            return $this->handleAlert(__('messages.plantilla_reporte_no_encontrada'));
        }
        $planilla->estado = 0;
        $planilla->save();
        return $this->handleResponse($request, [], __('messages.plantilla_reporte_bloqueada'));
    }

    public function desBloquearPlantilla(Request $request, $idPlantilla) {
        if (!is_numeric($idPlantilla)) {
            return $this->handleAlert(__('messages.tipo_formato_no_valido'));
        }
        $planilla = WbPlantillaReporte::where('fk_id_project_Company', $this->traitGetProyectoCabecera($request))
            ->find($idPlantilla);
        if ($planilla == null) {
            return $this->handleAlert(__('messages.plantilla_reporte_no_encontrada'));
        }
        $planilla->estado = 1;
        $planilla->save();
        return $this->handleResponse($request, [], __('messages.plantilla_reporte_desbloqueada'));
    }

    /**
     * @param Request $request
     */
    public function get(Request $request)
    {

    }

    public function getByTipoFormato(Request $request, $tipoFormato)
    {
        if (!is_numeric($tipoFormato)) {
            return $this->handleAlert(__('messages.tipo_formato_no_valido'));
        }
        if (WbTipoFormato::find($tipoFormato) == null) {
            return $this->handleAlert(__('messages.tipo_de_formato_no_encontrado'));
        }
        $consulta = WbPlantillaReporte::where('fk_tipo_formato', $tipoFormato)
            ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($request))
            ->where('estado', '1')
            ->first();
        if ($consulta == null) {
            return $this->handleAlert(__('messages.consultado'));
        } else {
            return $this->handleResponse($request, $this->plantillaReporteToModel($consulta), __('messages.consultado'));
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
