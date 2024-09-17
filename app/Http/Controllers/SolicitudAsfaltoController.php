<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Http\trait\DateHelpersTrait;
use App\Models\Compania;
use App\Models\MSO;
use App\Models\Usuarios\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbSolitudAsfalto;
use App\Models\WbAsfaltFormula;
use App\Models\WbHitos;
use App\Models\WbTramos;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudAsfaltoController extends BaseController implements Vervos
{
    public function update(Request $req, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Solicitud de asfalto no valido.');
        }
        if (!$req->json()->has('fechaAceptacion')) {
            return $this->handleAlert('Falta campo fechaAceptacion.');
        }
        if (!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if (!$req->json()->has('toneladaReal')) {
            return $this->handleAlert('Falta campo toneladaReal.');
        }
        if (!$req->json()->has('notaCierre')) {
            return $this->handleAlert('Falta campo notaCierre.');
        }
        if (
            $req->validate([
                'fechaAceptacion' => '',
                'estado' => '',
                'toneladaReal' => '',
                'notaCierre' => ''
            ])
        ) {
            $modeloModificar = WbSolitudAsfalto::find($id);
            if ($modeloModificar == null) {
                return $this->handleAlert('Solicitud asfalto no encontrado.');
            }
            $modeloModificar->fechaAceptacion = $req->fechaAceptacion;
            $modeloModificar->estado = $req->estado;
            $modeloModificar->toneladaReal = $req->toneladaReal;
            $modeloModificar->notaCierre = $req->notaCierre;
            try {
                if ($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Solicitud asfalto modificado.');
                }
            } catch (Exception $exc) {
            }
            return $this->handleAlert('Solicitud asfalto no modificado.');
        }
    }

    public function cerrarSolicitud(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert(__('messages.solicitud_de_asfalto_no_valido'));
        }
        $modelo = WbSolitudAsfalto::where('fk_id_project_Company', $this->traitGetProyectoCabecera($request))->find($id);
        if ($modelo == null) {
            return $this->handleAlert(__('messages.solicitud_asfalto_no_encontrado'));
        }
        $validator = Validator::make($request->all(), [
            'volumen' => 'required|string',
            'fecha' => 'required|string',
            'notaCierre' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }
        $modelo->fechaAceptacion = $request->fecha;
        $modelo->estado = 'ENVIADO';
        $modelo->toneladaReal = $request->volumen;
        $modelo->notaCierre = $request->notaCierre;

        if ($modelo->save()) {
            $confirmationController = new SmsController();
            $id_usuarios = $this->traitGetIdUsuarioToken($request);
            $mensaje = 'WEBU, La solicitud de asfalto No' .  $id . ' se cerro correctamente.';
            $nota = 'Solicitud de asfalto';
            $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
            return $this->handleResponse($request, [], __('messages.solicitud_de_asfalto_cerrada'));
        } else {
            return $this->handleAlert(__('messages.solicitud_de_asfalto_no_actualizada'));
        }
    }

    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'formula' => 'present',
            'abcisa' => 'required',
            'hito' => 'required',
            'tramo' => 'nullable|string',
            'calzada' => 'nullable|string',
            'cantidadToneladas' => 'required',
            'tipoMezcla' => 'required',
            'fechaHoraProgramacion' => 'required',
            'estado' => 'required|string',
            'observacion' => 'string',
            'companiaDestino' => 'required',
            'fechaAceptacion' => 'nullable',
            'costCode' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->handleAlert($validator->messages());
        }
        if (!WbAsfaltFormula::where('asfalt_formula', $req->formula)) {
            return $this->handleCod(__('messages.formula_asfalto_no_encontrada'), $this->formulaAsfaltoNoEncontradaError);
        }
        if (!WbHitos::where('Id_Hitos', $req->hito)) {
            return $this->handleCod(__('messages.hito_no_encontrado'), $this->hitoNoEncontradoError);
        }
        if (!WbTramos::where('Id_Tramo', $req->tramo)) {
            return $this->handleCod(__('messages.tramo_no_encontrado'), $this->tramoNoEncontradoError);
        }
        if (!is_numeric($req->cantidadToneladas)) {
            return $this->handleCod(__('messages.la_cantidad_de_toneladas_debe_ser_mayor_a_cero'), $this->cantidadSolicitudAsfaltoSolicitadaMenorOIgualA0Error);
        }
        date_default_timezone_set('America/Bogota');
        $empresa = $this->traitIdEmpresaPorProyecto($req);
        if ($empresa == null) {
            return $this->handleAlert('Su cuenta no se encuentra con una compañia asignada');
        }
        try {
            $nombreEmpresa = Compania::find($empresa);
            $modeloRegistrar = new WbSolitudAsfalto;
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            $recuperado = WbSolitudAsfalto::where('fk_id_usuario', $this->traitGetIdUsuarioToken($req))
                ->where('nombreCompañia', $nombreEmpresa->nombreCompañia)
                ->where('fechaSolicitud', date('Y/m/d'))
                ->where('formula', $req->formula)
                ->where('abscisas', $req->abcisa)
                ->where('hito', $req->hito)
                ->where('tramo', $req->tramo)
                ->where('calzada', $req->calzada)
                ->where('cantidadToneladas', $req->cantidadToneladas)
                ->where('tipoMezcla', $req->tipoMezcla)
                ->where('FechaHoraProgramacion', $req->fechaHoraProgramacion)
                ->where('estado', $req->estado)
                ->where('observaciones', $req->observacion)
                ->where('CompañiaDestino', $req->companiaDestino)
                ->where('fechaAceptacion', $req->fechaAceptacion)
                ->where('CostCode', $req->costCode)
                ->where('fk_id_project_Company', $modeloRegistrar->fk_id_project_Company)
                ->where('fk_compañia', $modeloRegistrar->fk_compañia)
                ->first();
            if ($recuperado != null) {
                return $this->handleResponse($req, $recuperado->id_solicitudAsf, __('messages.solicitud_asfalto_registrado') . ' -> ' . $recuperado->id_solicitudAsf);
            }
            $modeloRegistrar->fk_id_usuario = $this->traitGetIdUsuarioToken($req);
            $modeloRegistrar->nombreCompañia = $nombreEmpresa->nombreCompañia;
            $modeloRegistrar->fechaSolicitud = date('d/m/Y');
            $modeloRegistrar->formula = $req->formula;
            $modeloRegistrar->abscisas = $req->abcisa;
            $modeloRegistrar->hito = $req->hito;
            $modeloRegistrar->tramo = $req->tramo;
            $modeloRegistrar->calzada = $req->calzada;
            $modeloRegistrar->cantidadToneladas = $req->cantidadToneladas;
            $modeloRegistrar->tipoMezcla = $req->tipoMezcla;
            $modeloRegistrar->FechaHoraProgramacion = $req->fechaHoraProgramacion;
            $modeloRegistrar->estado = $req->estado;
            $modeloRegistrar->observaciones = $req->observacion;
            $modeloRegistrar->CompañiaDestino = $req->companiaDestino;
            $modeloRegistrar->fechaAceptacion = $req->fechaAceptacion;
            $modeloRegistrar->CostCode = $req->costCode;

            if ($modeloRegistrar->save()) {
                $recuperado = WbSolitudAsfalto::where('fk_id_usuario', $this->traitGetIdUsuarioToken($req))
                    ->where('nombreCompañia', $nombreEmpresa->nombreCompañia)
                    ->where('fechaSolicitud', date('d/m/Y'))
                    ->where('formula', $req->formula)
                    ->where('abscisas', $req->abcisa)
                    ->where('hito', $req->hito)
                    ->where('tramo', $req->tramo)
                    ->where('calzada', $req->calzada)
                    ->where('cantidadToneladas', $req->cantidadToneladas)
                    ->where('tipoMezcla', $req->tipoMezcla)
                    ->where('FechaHoraProgramacion', $req->fechaHoraProgramacion)
                    ->where('estado', $req->estado)
                    ->where('observaciones', $req->observacion)
                    ->where('CompañiaDestino', $req->companiaDestino)
                    ->where('fechaAceptacion', $req->fechaAceptacion)
                    ->where('CostCode', $req->costCode)
                    ->where('fk_id_project_Company', $modeloRegistrar->fk_id_project_Company)
                    ->where('fk_compañia', $modeloRegistrar->fk_compañia)
                    ->first();

                $id_usuarios = $this->traitGetIdUsuarioToken($req);
                $mensaje = 'WEBU, La solicitud de asfalto No. ' . $recuperado['id_solicitudAsf'] . ' ha sido radicada.';
                $nota = 'Solicitud de asfalto';
                /* $confirmationController = new SmsController();
                $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios); */
                try {
                    $confirmationController = new SmsController();
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                } catch (Exception $e) {
                    \Log::error('sms error -> '. $e->getMessage());
                }
                return $this->handleResponse($req, $recuperado->id_solicitudAsf, __('messages.solicitud_asfalto_registrado'));
            } else {
                return $this->handleAlert(__('messages.solicitud_asfalto_no_registrado'));
            }
        } catch (Exception $exc) {
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
            //return $this->handleAlert($exc->getMessage());
            //var_dump($exc);
        }
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
    }

    public function getById(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert(__("messages.solicitud_de_asfalto_no_valido"));
        }
        $modelo = WbSolitudAsfalto::where('id_solicitudAsf', $id)->get();
        if ($modelo->count() == 0) {
            return $this->handleAlert(__("messages.solicitud_asfalto_no_encontrado"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo[0]->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.solicitud_de_asfalto_no_valido"));
        }
        $usuarios = usuarios_M::all();
        $ususPlanta = UsuPlanta::all();
        $msos = MSO::all();
        foreach ($modelo as $item) {
            $this->setUsuarioById($item, $usuarios);
            $this->setUsuPlantaById($item, $ususPlanta);
            $this->setMsoById($item, $msos);
        }
        return $this->handleResponse($request, $this->SolicitudAsfaltoToModel($modelo[0]), __("messages.consultado"));
    }

    private function setUsuarioById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($modelo->fk_id_usuario == $array[$i]->id_usuarios) {
                $reescribir = $this->usuarioToModel($array[$i]);
                $modelo->objectUsuario = $reescribir;
                break;
            }
        }
    }

    private function setUsuPlantaById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($modelo->CompañiaDestino == $array[$i]->NombrePlanta) {
                $reescribir = $this->usuPlantaToModel($array[$i]);
                $modelo->objectUsuPlanta = $reescribir;
                break;
            }
        }
    }

    private function setMsoById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            $search = array('mm', ' ');
            $replace = array('', '');
            $result = str_replace($search, $replace, $modelo->formula);

            if (Strpos($array[$i]->MSODesc, $result)) {

                $modelo->msoid = $array[$i]->MSOID;
                break;
            }
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
