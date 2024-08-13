<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\estado;
use App\Models\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbFormulaCentroProduccion;
use App\Models\WbFormulaLista;
use App\Models\WbHitos;
use App\Models\WbMaterialCentroProduccion;
use App\Models\WbMaterialLista;
use App\Models\WbSolicitudMateriales;
use App\Models\WbTipoCalzada;
use App\Models\WbTipoCapa;
use App\Models\WbTramos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WbSolicitudMaterialesController extends BaseController implements Vervos
{
    public function update(Request $req, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Solicitud de materiales no valido');
        }
        if (!$req->json()->has('fechaCierre')) {
            return $this->handleAlert('Falta campo fechaCierre.');
        }
        if (!$req->json()->has('estados')) {
            return $this->handleAlert('Falta campo idEstados.');
        }
        if (!$req->json()->has('cantidadReal')) {
            return $this->handleAlert('Falta campo cantidadReal.');
        }
        if (!$req->json()->has('notaCierre')) {
            return $this->handleAlert('Falta campo notaCierre.');
        }
        if ($req->validate([
            'fechaCierre' => '',
            'estados' => '',
            'cantidadReal' => '',
            'notaCierre' => '',
        ])) {
            if (estado::find($req->estados) == null) {
                return $this->handleAlert('Estado no encontrado.');
            }
            $modeloModificar = WbSolicitudMateriales::find($id);
            if ($modeloModificar == null) {
                return $this->handleAlert('Solicitud de material no encontrado.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Solicitud de material no valido.');
            }
            $modeloModificar->fecha_cierre = $req->fechaCierre;
            $modeloModificar->fk_id_estados = $req->estados;
            $modeloModificar->cantidad_real = $req->cantidadReal;
            $modeloModificar->nota_cierre = $req->notaCierre;
            try {
                if ($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Solicitud material modificado.');
                }
            } catch (\Exception $exc) {
            }

            return $this->handleAlert('Solicitud material no modificado.');
        }
    }

    public function reAsignar(Request $request, $id)
    {
        if ($request->validate([
            'planta' => '',
        ])) {
            $usuario = $this->traitGetIdUsuarioToken($request);
            $modelo = WbSolicitudMateriales::find($id);

            $solicitante = WbSolicitudMateriales::select('fk_id_usuarios')->find($modelo->id_solicitud_Materiales);
            if (!$modelo) {
                return $this->handleAlert('Solicitud material no encontrada');
            } else {
                $modelo->fk_id_estados = 12;
                $modelo->fk_id_plantaReasig = $request->planta;
                $modelo->fk_id_usuarios_update = $usuario;
                if ($modelo->save()) {
                    // Obtener el nombre de la planta
                    $nombrePlanta = UsuPlanta::where('id_plata', $modelo->fk_id_plantaReasig)->value('NombrePlanta');

                    $confirmationController = new SmsController();
                    $id_usuarios = $this->traitGetIdUsuarioToken($request);
                    $mensaje = 'WEBU, La solicitud de material No. '.$id.' fue reasignada correctamente a '.$nombrePlanta.'.';
                    $nota = 'Solicitud de material';
                    $mensaje2 = 'WEBU, Su solicitud de material No. '.$id.' fue aprobada correctamente, pero se reasignó a otra planta: '.$nombrePlanta;
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje2, $nota, $solicitante->fk_id_usuarios);

                    return $this->handleResponse($request, [], 'Solicitud actualizada');
                }
            }
        }

        return $this->handleAlert('Solicitud material no actualizada');
    }

    public function aprovar(Request $request, $id)
    {
        if ($request->validate([
            'nota' => '',
        ])) {
            $usuario = $this->traitGetIdUsuarioToken($request);
            $modelo = WbSolicitudMateriales::find($id);
            $solicitante = WbSolicitudMateriales::select('fk_id_usuarios')->find($modelo->id_solicitud_Materiales);
            if (!$modelo) {
                return $this->handleAlert('Solicitud material no encontrada');
            } else {
                $modelo->fk_id_estados = 12;
                $modelo->notaSU = $request->nota;
                $modelo->fk_id_usuarios_update = $usuario;
                if ($modelo->save()) {
                    $confirmationController = new SmsController();
                    $id_usuarios = $this->traitGetIdUsuarioToken($request);
                    $mensaje = 'WEBU, La solicitud de material '.$id.' fue aprobada correctamente.';
                    $nota = 'Solicitud de material';
                    $mensaje2 = 'WEBU, Su solicitud de material '.$id.' fue aprobada correctamente.';
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje2, $nota, $solicitante->fk_id_usuarios);

                    return $this->handleResponse($request, [], 'Solicitud actualizada');
                }
            }
        }

        return $this->handleAlert('Solicitud material no actualizada');
    }

    public function rechazar(Request $request, $id)
    {
        if ($request->validate([
            'nota' => '',
        ])) {
            $usuario = $this->traitGetIdUsuarioToken($request);
            $modelo = WbSolicitudMateriales::find($id);
            $solicitante = WbSolicitudMateriales::select('fk_id_usuarios')->find($modelo->id_solicitud_Materiales);
            if (!$modelo) {
                return $this->handleAlert('Solicitud material no encontrada');
            } else {
                $modelo->fk_id_estados = 13;
                $modelo->notaSU = $request->nota;
                $modelo->fk_id_usuarios_update = $usuario;
                if ($modelo->save()) {
                    $confirmationController = new SmsController();
                    $mensaje = 'WEBU, La solicitud de material '.$id.' fue cerrada correctamente.';
                    $nota = 'Solicitud de material';
                    $mensaje2 = 'WEBU, Su solicitud de material '.$id.' fue cerrada.';
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $usuario);
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje2, $nota, $solicitante->fk_id_usuarios);

                    return $this->handleResponse($request, [], 'Solicitud actualizada');
                }
            }
        }

        return $this->handleAlert('Solicitud material no actualizada');
    }

    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function getApp(Request $request)
    {
        $consulta = WbSolicitudMateriales::select(
            'id_solicitud_Materiales',
            'fk_id_usuarios',
            'capa.Descripcion',
            'fk_id_tramo',
            'fk_id_hito',
            'abscisaInicialReferencia',
            'abscisaFinalReferencia',
            DB::raw("'Inicial: K'+ SUBSTRING(abscisaInicialReferencia,1,2) +'+'+ SUBSTRING(abscisaInicialReferencia, 3,5) + ' - Final: K'+ SUBSTRING(abscisaFinalReferencia,1,2) +'+'+ SUBSTRING(abscisaFinalReferencia, 3,5) as inicialfinal"),
            'carril.Carril',
            'calzada.Calzada',
            'fk_id_material',
            'material.Nombre',
            'material.unidadMedida',
            'fechaProgramacion',
            'Cantidad',
            'numeroCapa',
            'notaUsuario',
            'notaSU',
            'notaCenProduccion',
            'fk_id_estados',
            'esta.descripcion_estado',
            DB::raw('convert(varchar, dateCreation, 0) as dateCreation'),
            'fk_id_formula',
            'pla.NombrePlanta',
            DB::raw("ISNULL(Formula.Nombre,material.Nombre)  + ' \n' + ISNULL(pla1.NombrePlanta,pla.NombrePlanta) + ' \n' + usu.Nombre+' ' + usu.Apellido as nombreFormula"),
            DB::raw("usu.Nombre + ' ' + usu.Apellido as nombreFormula"),
            'pla2.NombrePlanta as plantadestino',
        )->leftJoin('Wb_Tipo_Capa as capa', 'capa.id_tipo_capa', 'Wb_Solicitud_Materiales.fk_id_tipo_capa')
            ->leftJoin('Wb_Tipo_Carril as carril', 'carril.id_tipo_carril', 'Wb_Solicitud_Materiales.fk_id_tipo_carril')
            ->leftJoin('Wb_Tipo_Calzada as calzada', 'calzada.id_tipo_calzada', 'Wb_Solicitud_Materiales.fk_id_tipo_calzada')
            ->leftJoin('Wb_Material_Lista as material', 'material.id_material_lista', 'Wb_Solicitud_Materiales.fk_id_material')
            ->leftJoin('estados as esta', 'esta.id_estados', 'Wb_Solicitud_Materiales.fk_id_estados')
            ->leftJoin('usuPlanta as pla', 'pla.id_plata', 'Wb_Solicitud_Materiales.fk_id_planta')
            ->leftJoin('usuPlanta as pla1', 'pla1.id_plata', 'Wb_Solicitud_Materiales.fk_id_plantaReasig')
            ->leftJoin('usuPlanta as pla2', 'pla2.id_plata', 'Wb_Solicitud_Materiales.fk_id_planta_destino')
            ->leftJoin('Wb_Formula_Lista as Formula', 'Formula.id_formula_lista', 'Wb_Solicitud_Materiales.fk_id_formula')
            ->leftJoin('usuarioss as usu', 'usu.id_usuarios', 'Wb_Solicitud_Materiales.fk_id_usuarios')
            ->orderBy('id_solicitud_Materiales', 'desc');
        if (strlen($request->fecha) > 0) {
            $consulta = $consulta->whereBetween('Wb_Solicitud_Materiales.fechaProgramacion', [DB::raw("CONVERT(DATETIME,'".$request->fecha." 00:00:00',120)"), DB::raw("CONVERT(DATETIME,'".$request->fecha." 23:59:59',120)")]);
        } else {
            $consulta = $consulta->whereBetween('Wb_Solicitud_Materiales.fechaProgramacion', [DB::raw("CONVERT(DATETIME,CONVERT(VARCHAR, GETDATE(),105)+ ' 00:00:00',105)"), DB::raw("CONVERT(DATETIME,CONVERT (VARCHAR, GETDATE(),105)+ ' 23:59:59',105)")]);
        }
        $consulta = $this->filtrar($request, $consulta, 'Wb_Solicitud_Materiales')->get();

        return $this->handleResponse($request, $this->solicitudMaterialAppToArray($consulta), __('messages.consultado'));
    }

    public function getAppByFecha(Request $request)
    {
        $consulta = WbSolicitudMateriales::select(
            'id_solicitud_Materiales',
            'fk_id_usuarios',
            'capa.Descripcion',
            'fk_id_tramo',
            'fk_id_hito',
            'abscisaInicialReferencia',
            'abscisaFinalReferencia',
            DB::raw("'Inicial: K'+ SUBSTRING(abscisaInicialReferencia,1,2) +'+'+ SUBSTRING(abscisaInicialReferencia, 3,5) + ' - Final: K'+ SUBSTRING(abscisaFinalReferencia,1,2) +'+'+ SUBSTRING(abscisaFinalReferencia, 3,5) as inicialfinal"),
            'carril.Carril',
            'calzada.Calzada',
            'fk_id_material',
            'material.Nombre',
            'material.unidadMedida',
            'fechaProgramacion',
            'Cantidad',
            'numeroCapa',
            'notaUsuario',
            'notaSU',
            'notaCenProduccion',
            'fk_id_estados',
            'esta.descripcion_estado',
            DB::raw('convert(varchar, dateCreation, 0) as dateCreation'),
            'fk_id_formula',
            'pla.NombrePlanta',
            DB::raw("ISNULL(Formula.Nombre,material.Nombre)  + ' \n' + ISNULL(pla1.NombrePlanta,pla.NombrePlanta) + ' \n' + usu.Nombre+' ' + usu.Apellido as nombreFormula"),
            DB::raw("usu.Nombre + ' ' + usu.Apellido as nombreFormula"),
        )->leftJoin('Wb_Tipo_Capa as capa', 'capa.id_tipo_capa', 'Wb_Solicitud_Materiales.fk_id_tipo_capa')
            ->leftJoin('Wb_Tipo_Carril as carril', 'carril.id_tipo_carril', 'Wb_Solicitud_Materiales.fk_id_tipo_carril')
            ->leftJoin('Wb_Tipo_Calzada as calzada', 'calzada.id_tipo_calzada', 'Wb_Solicitud_Materiales.fk_id_tipo_calzada')
            ->leftJoin('Wb_Material_Lista as material', 'material.id_material_lista', 'Wb_Solicitud_Materiales.fk_id_material')
            ->leftJoin('estados as esta', 'esta.id_estados', 'Wb_Solicitud_Materiales.fk_id_estados')
            ->leftJoin('usuPlanta as pla', 'pla.id_plata', 'Wb_Solicitud_Materiales.fk_id_planta')
            ->leftJoin('usuPlanta as pla1', 'pla1.id_plata', 'Wb_Solicitud_Materiales.fk_id_plantaReasig')
            ->leftJoin('Wb_Formula_Lista as Formula', 'Formula.id_formula_lista', 'Wb_Solicitud_Materiales.fk_id_formula')
            ->leftJoin('usuarioss as usu', 'usu.id_usuarios', 'Wb_Solicitud_Materiales.fk_id_usuarios')
            ->whereBetween('Wb_Solicitud_Materiales.fechaProgramacion', [DB::raw("CONVERT(DATETIME,'".$request->fecha." 00:00:00',120)"), DB::raw("CONVERT(DATETIME,'".$request->fecha." 23:59:59',120)")])
            ->orderBy('id_solicitud_Materiales', 'desc');
        $consulta = $this->filtrar($request, $consulta, 'Wb_Solicitud_Materiales')->get();

        return $this->handleResponse($request, $this->solicitudMaterialAppToArray($consulta), __('messages.consultado'));
    }

    // Nuevo get
    public function getSolicitudMateriales(Request $request)
    {
        // Obtener el año del JSON de la solicitud o usar el año actual si no se proporciona
        $year = $request->input('year', Carbon::now()->year);
        // Si $year no es un valor numérico válido, usa el año actual por defecto
        if (!is_numeric($year) || $year < 1900 || $year > Carbon::now()->year) {
            $year = Carbon::now()->year;
        }
        Log::info("Año seleccionado: $year");
        // Construir la consulta con el año proporcionado
        $consulta = WbSolicitudMateriales::with(
            ['usuario', 'tipoCalzada', 'materialLista', 'tramo', 'hitos', 'formulaLista', 'tipoCapa', 'tipoCarril', 'plantas', 'plantas_destino', 'compania', 'globalProjectCompany', 'estado']
        )
        ->whereYear('dateCreation', $year);
        $consulta = $consulta->orderBy('id_solicitud_Materiales', 'desc');
        $limitePaginas = 0;

        return $this->handleResponse($request, $this->wbSolicitudMaterialToArray($consulta->get()), __('messages.consultado'), $limitePaginas);
    }

    public function get(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        if (!is_numeric($year) || $year < 1900 || $year > Carbon::now()->year) {
            $year = Carbon::now()->year;
        }
        // Construir la consulta con el año proporcionado y el rango de estados
        $consulta = WbSolicitudMateriales::with(
            [
                'usuario',
                'tipoCalzada',
                'materialLista',
                'tramo',
                'hitos',
                'formulaLista',
                'tipoCapa',
                'tipoCarril',
                'plantas',
                'plantas_destino',
                'compania',
                'globalProjectCompany',
                'estado',
                'usuarioaprobador',
            ]
        )
        ->whereYear('dateCreation', $year)
        ->where(function ($query) {
            $query->whereBetween('fk_id_estados', [7, 11])
                  ->orWhereNotNull('fk_id_usuarios_update');
        });
        $consulta = $consulta->orderBy('id_solicitud_Materiales', 'desc');
        $limitePaginas = 0;

        return $this->handleResponse($request, $this->wbSolicitudMaterialToArray($consulta->get()), __('messages.consultado'), $limitePaginas);
    }

    private function setTipoCapalById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($modelo->fk_id_tipo_capa == $array[$i]->id_tipo_capa) {
                $reescribir = $this->wbTipoCapaToModel($array[$i]);
                $modelo->objectTipoCapa = $reescribir;
                break;
            }
        }
    }

    private function setTipoCarrilById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($modelo->fk_id_tipo_carril == $array[$i]->id_tipo_carril) {
                $reescribir = $this->wbTipoCarrilToModel($array[$i]);
                $modelo->objectTipoCarril = $reescribir;
                break;
            }
        }
    }

    private function setTipoCalzadaById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($modelo->fk_id_tipo_calzada == $array[$i]->id_tipo_calzada) {
                $reescribir = $this->wbTipoCalzadaToModel($array[$i]);
                $modelo->objectTipoCalzada = $reescribir;
                break;
            }
        }
    }

    private function setMaterialListaById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($modelo->fk_id_material == $array[$i]->id_material_lista) {
                $reescribir = $this->wbMaterialListaToModel($array[$i]);
                $modelo->objectMaterialLista = $reescribir;
                break;
            }
        }
    }

    private function setEstadoById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($modelo->id_estados == $array[$i]->id_estados) {
                $reescribir = $this->estadoToModel($array[$i]);
                $modelo->objectEstado = $reescribir;
                break;
            }
        }
    }

    private function setUsuPlantaById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($modelo->fk_id_planta == $array[$i]->id_plata) {
                $reescribir = $this->usuPlantaToModel($array[$i]);
                $modelo->objectUsuPlanta = $reescribir;
                break;
            }
        }
    }

    private function setFormulaListaById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($modelo->fk_id_formula == $array[$i]->id_formula_lista) {
                $reescribir = $this->wbFormulaListaToModel($array[$i]);
                $modelo->objectFormulaLista = $reescribir;
                break;
            }
        }
    }

    private function setUsuarioById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($modelo->fk_id_usuarios == $array[$i]->id_usuarios) {
                $reescribir = $this->usuarioToModel($array[$i]);
                $modelo->objectUsuario = $reescribir;
                break;
            }
        }
    }

    // TODO: Implement getPorProyecto() method.
    // funcion para insertar una solicitud de material para centros de produccion
    public function postforCentrosDeprecated(Request $req)
    {
        // validamos que se reciba la informacion requerida
        $validator = Validator::make($req->all(), [
            'IDUSUARIO' => 'required|numeric',
            'MATERIAL' => 'present|numeric|nullable',
            'PLANTADESTINO' => 'required|numeric',
            'FECHA' => 'required',
            'CANTIDAD' => 'required|numeric',
            'OBSERVACION' => 'present|string',
            'ESTADO' => 'required|numeric',
            'FORMULA' => 'present|numeric|nullable',
            'PLANTA' => 'required|numeric',
        ]);
        // si no cumple validaciones imprime error
        if ($validator->fails()) {
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (001)",false);
            return $validator->errors();
        }

        $datos = $req->all();
        // se valida que la solicitud no este duplicada
        $validacion = $this->consultarCoincidenciaCentro($datos);
        // return $validacion->id_solicitud_materiales;
        // si existe una solicitud igual devolemos la espuesta como correcta
        if ($validacion) {
            // $validacion->first();
            return $this->handleAlert($validacion->id_solicitud_Materiales, true);
        }

        // ahora se valida si los datos de insercion son correctos
        // validando el id del usuario
        $user = usuarios_M::where('estado', 'A')->find($datos['IDUSUARIO']);

        if (!$user) {
            return $this->handleAlert('La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (002)', false);
        }
        $inconsistencia = '';

        // validamos que el centro de produccion este activo
        $cdc1 = UsuPlanta::where('estado', '1')->find($datos['PLANTADESTINO']);
        if (!$cdc1) {
            $inconsistencia .= '/n PLANTA DESTINO';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (006)",false);
        }

        // validamos que el centro de produccion este activo
        $cdc = UsuPlanta::where('estado', '1')->find($datos['PLANTA']);
        if (!$cdc) {
            $inconsistencia .= '/n PLANTA Y MATERIAL';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (006)",false);
        } else {
            // validamos si el material existe y esta activo
            if (!is_null($datos['MATERIAL'])) {
                $material = WbMaterialLista::where('estado', 'A')->find($datos['MATERIAL']);
                if (!$material) {
                    $inconsistencia .= '/n MATERIAL';
                    // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (007)",false);
                } else {
                    $materialcdp = WbMaterialCentroProduccion::where('estado', 'A')
                        ->where('fk_id_material_lista', $datos['MATERIAL'])
                        ->where('fk_id_planta', $datos['PLANTA'])
                        ->count();
                    if ($materialcdp == 0) {
                        $inconsistencia .= '/n PLANTA';
                    }
                }
            }
            // validamos si el material existe y esta activo
            if (!is_null($datos['FORMULA'])) {
                $material = WbFormulaLista::where('estado', 'A')->find($datos['FORMULA']);
                if (!$material) {
                    $inconsistencia .= '/n FORMULA';
                    // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (007)",false);
                } else {
                    $materialcdp = WbFormulaCentroProduccion::where('estado', 'A')
                        ->where('fk_id_formula_lista', $datos['FORMULA'])
                        ->where('fk_id_planta', $datos['PLANTA'])
                        ->count();
                    if ($materialcdp == 0) {
                        $inconsistencia .= '/n PLANTA';
                    }
                }
            }
        }

        // validamos si hubo alguna inconsistencia con los datos enviados
        if ($inconsistencia == '') {
            $solicitud = new WbSolicitudMateriales();
            $solicitud->fk_id_usuarios = $datos['IDUSUARIO'];
            $solicitud->fk_id_material = $datos['MATERIAL'];
            $solicitud->fechaProgramacion = $datos['FECHA'];
            $solicitud->Cantidad = $datos['CANTIDAD'];
            $solicitud->notaUsuario = $datos['OBSERVACION'];
            $solicitud->fk_id_estados = $datos['ESTADO'];
            $solicitud->fk_id_formula = $datos['FORMULA'];
            $solicitud->fk_id_planta = $datos['PLANTA'];
            $solicitud->fk_id_planta_destino = $datos['PLANTADESTINO'];
            $solicitud->save();
            $solicitud = $this->consultarCoincidenciaCentro($datos);
            $confirmationController = new SmsController();
            $id_usuarios = $this->traitGetIdUsuarioToken($req);
            $mensaje = 'WEBU, La solicitud de material No. '.$solicitud->id_solicitud_Materiales.' ha sido radicada.';
            $nota = 'Solicitud de material';
            $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);

            return $this->handleAlert($solicitud->id_solicitud_Materiales, true);
        } else {
            return $this->handleAlert('Revisar los siguientes datos: '.$inconsistencia, false);
        }
    }

    // TODO: Implement getPorProyecto() method.
    // funcion para insertar una solicitud de material para centros de produccion
    public function postforCentros(Request $req)
    {
        // validamos que se reciba la informacion requerida
        $validator = Validator::make($req->all(), [
            'MATERIAL' => 'present|numeric|nullable',
            'PLANTADESTINO' => 'required|numeric',
            'FECHA' => 'required',
            'CANTIDAD' => 'required|numeric',
            'OBSERVACION' => 'present|string',
            'ESTADO' => 'required|numeric',
            'FORMULA' => 'present|numeric|nullable',
            'PLANTA' => 'required|numeric',
        ]);
        // si no cumple validaciones imprime error
        if ($validator->fails()) {
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (001)",false);
            return $validator->errors();
        }
        $proyecto = $this->traitGetProyectoCabecera($req);
        $datos = $req->all();
        // se valida que la solicitud no este duplicada
        $validacion = $this->consultarCoincidenciaCentro($datos, $req);
        // return $validacion->id_solicitud_materiales;
        // si existe una solicitud igual devolemos la espuesta como correcta
        if ($validacion) {
            // $validacion->first();
            return $this->handleAlert($validacion->id_solicitud_Materiales, true);
        }

        // ahora se valida si los datos de insercion son correctos
        $inconsistencia = '';

        // validamos que el centro de produccion este activo
        $cdc1 = UsuPlanta::where('estado', '1')
            ->where('fk_id_project_Company', $proyecto)
            ->find($datos['PLANTADESTINO']);
        if (!$cdc1) {
            $inconsistencia .= '/n PLANTA DESTINO';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (006)",false);
        }

        // validamos que el centro de produccion este activo
        $cdc = UsuPlanta::where('estado', '1')
            ->where('fk_id_project_Company', $proyecto)
            ->find($datos['PLANTA']);
        if (!$cdc) {
            $inconsistencia .= '/n PLANTA Y MATERIAL';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (006)",false);
        } else {
            // validamos si el material existe y esta activo
            if (!is_null($datos['MATERIAL'])) {
                $material = WbMaterialLista::where('estado', 'A')
                    ->where('fk_id_project_company', $proyecto)
                    ->find($datos['MATERIAL']);
                if (!$material) {
                    $inconsistencia .= '/n MATERIAL';
                    // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (007)",false);
                } else {
                    $materialcdp = WbMaterialCentroProduccion::where('estado', 'A')
                        ->where('fk_id_project_Company', $proyecto)
                        ->where('fk_id_material_lista', $datos['MATERIAL'])
                        ->where('fk_id_planta', $datos['PLANTA'])
                        ->count();
                    if ($materialcdp == 0) {
                        $inconsistencia .= '/n PLANTA';
                    }
                }
            }
            if (estado::find($req->ESTADO) == null) {
                $inconsistencia .= '/n ESTADO';
            }
            // validamos si el material existe y esta activo
            if (!is_null($datos['FORMULA'])) {
                $material = WbFormulaLista::where('estado', 'A')
                    ->where('fk_id_project_Company', $proyecto)
                    ->find($datos['FORMULA']);
                if (!$material) {
                    $inconsistencia .= '/n FORMULA';
                    // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (007)",false);
                } else {
                    $materialcdp = WbFormulaCentroProduccion::where('estado', 'A')
                        ->where('fk_id_project_Company', $proyecto)
                        ->where('fk_id_formula_lista', $datos['FORMULA'])
                        ->where('fk_id_planta', $datos['PLANTA'])
                        ->count();
                    if ($materialcdp == 0) {
                        $inconsistencia .= '/n PLANTA';
                    }
                }
            }
        }

        // validamos si hubo alguna inconsistencia con los datos enviados
        if ($inconsistencia == '') {
            $solicitud = new WbSolicitudMateriales();
            $solicitud->fk_id_usuarios = $this->traitGetIdUsuarioToken($req);
            $solicitud->fk_id_material = $datos['MATERIAL'];
            $solicitud->fechaProgramacion = $datos['FECHA'];
            $solicitud->Cantidad = $datos['CANTIDAD'];
            $solicitud->notaUsuario = $datos['OBSERVACION'];
            $solicitud->fk_id_estados = $datos['ESTADO'];
            $solicitud->fk_id_formula = $datos['FORMULA'];
            $solicitud->fk_id_planta = $datos['PLANTA'];
            $solicitud->fk_id_planta_destino = $datos['PLANTADESTINO'];
            $solicitud = $this->traitSetProyectoYCompania($req, $solicitud);
            $solicitud->save();
            $solicitud = $this->consultarCoincidenciaCentro($datos, $req);
            $id_solicitud = $solicitud->id_solicitud_Materiales;
            $confirmationController = new SmsController();
            $id_usuarios = $this->traitGetIdUsuarioToken($req);
            $mensaje = 'WEBU, La solicitud de material  No. '.$id_solicitud.' ha sido radicada.';
            $nota = 'Solicitud de material';
            $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);

            return $this->handleAlert($solicitud->id_solicitud_Materiales, true);
        } else {
            return $this->handleAlert('Revisar los siguientes datos: '.$inconsistencia);
        }
    }

    public function consultarCoincidenciaCentro($datos, Request $request = null)
    {
        $consulta = WbSolicitudMateriales::select('id_solicitud_Materiales')->where('fechaProgramacion', $datos['FECHA'])
            ->where('notaUsuario', '=', $datos['OBSERVACION'])
            ->where('Cantidad', $datos['CANTIDAD'])
            ->where('fk_id_planta_destino', $datos['PLANTADESTINO'])
            ->whereRaw('DATEDIFF(Minute,datecreation,GETDATE())<?', 10)
            ->whereRaw('DATEDIFF(Minute,datecreation,GETDATE())>=?', 0);
        if ($request != null) {
            $aux = new WbSolicitudMateriales();
            $aux = $this->traitSetProyectoYCompania($request, $aux);
            $consulta = $consulta->where('fk_id_project_Company', $aux->fk_id_project_Company)
                ->where('fk_compañia', $aux->fk_compañia);
        }

        return $consulta->first();
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Support\MessageBag
     *
     * @deprecated
     */
    public function postforFrentesDeprecated(Request $req)
    {
        // validamos que se reciba la informacion requerida
        $validator = Validator::make($req->all(), [
            'IDUSUARIO' => 'required|numeric',
            'CAPA' => 'required|numeric',
            'TRAMO' => 'required|string',
            'HITO' => 'required|string',
            'ABSCISAINICIAL' => 'required|numeric',
            'ABSCISAFINAL' => 'required|numeric',
            'CARRIL' => 'required|numeric',
            'CALZADA' => 'required|numeric',
            'MATERIAL' => 'present|numeric|nullable',
            'FECHA' => 'required',
            'CANTIDAD' => 'required|numeric',
            'NROCAPA' => 'required|numeric',
            'OBSERVACION' => 'present|string',
            'ESTADO' => 'required|numeric',
            'FORMULA' => 'present|numeric|nullable',
            'PLANTA' => 'required|numeric',
        ]);
        // si no cumple validaciones imprime error
        if ($validator->fails()) {
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (001)",false);
            return $validator->errors();
        }

        $datos = $req->all();
        // se valida que la solicitud no este duplicada
        $validacion = $this->consultaCoincidenciaFrente($datos);
        // return $validacion->id_solicitud_materiales;
        // si existe una solicitud igual devolemos la espuesta como correcta
        if ($validacion) {
            // $validacion->first();
            return $this->handleAlert($validacion->id_solicitud_Materiales, true);
        }

        // ahora se valida si los datos de insercion son correctos
        // validando el id del usuario
        $user = usuarios_M::where('estado', 'A')->find($datos['IDUSUARIO']);

        if (!$user) {
            return $this->handleAlert('La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (002)', false);
        }
        $inconsistencia = '';
        // validamos que la capa exista y este activa
        $capa = WbTipoCapa::where('estado', 'A')->find($datos['CAPA']);
        if (!$capa) {
            $inconsistencia .= '/n CAPA';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (003)",false);
        }

        // validamos tramo activo
        $tramo = WbTramos::where('estado', 'A')->where('Id_Tramo', $datos['TRAMO'])->first();
        if (!$tramo) {
            $inconsistencia .= '/n TRAMO';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (004)",false);
        }

        // Validamos que el hito este activo
        $hito = WbHitos::where('estado', 'A')->where('Id_Hitos', $datos['HITO'])->first();
        if (!$hito) {
            $inconsistencia .= '/n HITO';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (005)",false);
        }

        // Validamos que el CALZADA este activo
        $calzada = WbTipoCalzada::where('estado', 'A')->find($datos['CALZADA']);
        if (!$calzada) {
            $inconsistencia .= '/n CALZADA';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (006)",false);
        }

        // validamos que el centro de produccion este activo
        $cdc = UsuPlanta::where('estado', '1')->find($datos['PLANTA']);
        if (!$cdc) {
            $inconsistencia .= '/n PLANTA Y MATERIAL';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (006)",false);
        } else {
            // validamos si el material existe y esta activo
            if (!is_null($datos['MATERIAL'])) {
                $material = WbMaterialLista::where('estado', 'A')->find($datos['MATERIAL']);
                if (!$material) {
                    $inconsistencia .= '/n MATERIAL';
                    // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (007)",false);
                } else {
                    $materialcdp = WbMaterialCentroProduccion::where('estado', 'A')
                        ->where('fk_id_material_lista', $datos['MATERIAL'])
                        ->where('fk_id_planta', $datos['PLANTA'])
                        ->count();
                    if ($materialcdp == 0) {
                        $inconsistencia .= '/n PLANTA';
                    }
                }
            }
            // validamos si el material existe y esta activo
            if (!is_null($datos['FORMULA'])) {
                $material = WbFormulaLista::where('estado', 'A')->find($datos['FORMULA']);
                if (!$material) {
                    $inconsistencia .= '/n FORMULA';
                    // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (007)",false);
                } else {
                    $materialcdp = WbFormulaCentroProduccion::where('estado', 'A')
                        ->where('fk_id_formula_lista', $datos['FORMULA'])
                        ->where('fk_id_planta', $datos['PLANTA'])
                        ->count();
                    if ($materialcdp == 0) {
                        $inconsistencia .= '/n PLANTA';
                    }
                }
            }
        }

        // validamos si hubo alguna inconsistencia con los datos enviados
        if ($inconsistencia == '') {
            $solicitud = new WbSolicitudMateriales();
            $solicitud->fk_id_usuarios = $datos['IDUSUARIO'];
            $solicitud->fk_id_tipo_capa = $datos['CAPA'];
            $solicitud->fk_id_tramo = $datos['TRAMO'];
            $solicitud->fk_id_hito = $datos['HITO'];
            $solicitud->abscisaInicialReferencia = $datos['ABSCISAINICIAL'];
            $solicitud->abscisaFinalReferencia = $datos['ABSCISAFINAL'];
            $solicitud->fk_id_tipo_carril = $datos['CARRIL'];
            $solicitud->fk_id_tipo_calzada = $datos['CALZADA'];
            $solicitud->fk_id_material = $datos['MATERIAL'];
            $solicitud->fechaProgramacion = $datos['FECHA'];
            $solicitud->Cantidad = $datos['CANTIDAD'];
            $solicitud->numeroCapa = $datos['NROCAPA'];
            $solicitud->notaUsuario = $datos['OBSERVACION'];
            $solicitud->fk_id_estados = $datos['ESTADO'];
            $solicitud->fk_id_formula = $datos['FORMULA'];
            $solicitud->fk_id_planta = $datos['PLANTA'];
            $solicitud->save();
            $solicitud = $this->consultaCoincidenciaFrente($datos);

            return $this->handleAlert($solicitud->id_solicitud_Materiales, true);
            // return $this->handleResponse($req, [], $solicitud->id_solicitud_Materiales);
        } else {
            return $this->handleAlert('Revisar los siguientes datos: '.$inconsistencia, false);
        }
    }

    // funcion para insertar una solicitud de material para frentes de obra
    public function postforFrentes(Request $req)
    {
        // validamos que se reciba la informacion requerida
        $validator = Validator::make($req->all(), [
            'CAPA' => 'required|numeric',
            'TRAMO' => 'required|string',
            'HITO' => 'required|string',
            'ABSCISAINICIAL' => 'required|numeric',
            'ABSCISAFINAL' => 'required|numeric',
            'CARRIL' => 'required|numeric',
            'CALZADA' => 'required|numeric',
            'MATERIAL' => 'present|numeric|nullable',
            'FECHA' => 'required',
            'CANTIDAD' => 'required|numeric',
            'NROCAPA' => 'required|numeric',
            'OBSERVACION' => 'present|string',
            'ESTADO' => 'required|numeric',
            'FORMULA' => 'present|numeric|nullable',
            'PLANTA' => 'required|numeric',
        ]);
        // si no cumple validaciones imprime error
        if ($validator->fails()) {
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (001)",false);
            return $this->handleAlert($validator->errors(), false);
        }

        $datos = $req->all();

        // se valida que la solicitud no este duplicada
        $validacion = $this->consultaCoincidenciaFrente($datos, $req);
        // return $validacion->id_solicitud_materiales;
        // si existe una solicitud igual devolemos la espuesta como correcta
        if ($validacion) {
            // $validacion->first();
            return $this->handleAlert($validacion->id_solicitud_Materiales, true);
        }
        $proyecto = $this->traitGetProyectoCabecera($req);
        $inconsistencia = '';
        // validamos que la capa exista y este activa
        $capa = WbTipoCapa::where('estado', 'A')->where('fk_id_project_Company', $proyecto)->find($datos['CAPA']);
        if (!$capa) {
            $inconsistencia .= '/n CAPA';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (003)",false);
        }

        // validamos tramo activo
        $tramo = WbTramos::where('estado', 'A')->where('fk_id_project_Company', $proyecto)->where('Id_Tramo', $datos['TRAMO'])->first();
        if (!$tramo) {
            $inconsistencia .= '/n TRAMO';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (004)",false);
        }

        // Validamos que el hito este activo
        $hito = WbHitos::where('estado', 'A')->where('fk_id_project_Company', $proyecto)->where('Id_Hitos', $datos['HITO'])->first();
        if (!$hito) {
            $inconsistencia .= '/n HITO';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (005)",false);
        }

        // Validamos que el CALZADA este activo
        $calzada = WbTipoCalzada::where('estado', 'A')->where('fk_id_project_Company', $proyecto)->find($datos['CALZADA']);
        if (!$calzada) {
            $inconsistencia .= '/n CALZADA';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (006)",false);
        }
        // validamos si el estado existe
        if (estado::find($req->ESTADO) == null) {
            $inconsistencia .= '/n ESTADO';
        }

        // validamos que el centro de produccion este activo
        $cdc = UsuPlanta::where('estado', '1')->where('fk_id_project_Company', $proyecto)->find($datos['PLANTA']);
        if (!$cdc) {
            $inconsistencia .= '/n PLANTA Y MATERIAL';
            // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (006)",false);
        } else {
            // validamos si el material existe y esta activo
            if (!is_null($datos['MATERIAL'])) {
                $material = WbMaterialLista::where('estado', 'A')->where('fk_id_project_company', $proyecto)->find($datos['MATERIAL']);
                if (!$material) {
                    $inconsistencia .= '/n MATERIAL';
                    // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (007)",false);
                } else {
                    $materialcdp = WbMaterialCentroProduccion::where('estado', 'A')
                        ->where('fk_id_project_Company', $proyecto)
                        ->where('fk_id_material_lista', $datos['MATERIAL'])
                        ->where('fk_id_planta', $datos['PLANTA'])
                        ->count();
                    if ($materialcdp == 0) {
                        $inconsistencia .= '/n PLANTA';
                    }
                }
            }
            // validamos si el material existe y esta activo
            if (!is_null($datos['FORMULA'])) {
                $material = WbFormulaLista::where('estado', 'A')
                    ->where('fk_id_project_Company', $proyecto)
                    ->find($datos['FORMULA']);
                if (!$material) {
                    $inconsistencia .= '/n FORMULA';
                    // return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (007)",false);
                } else {
                    $materialcdp = WbFormulaCentroProduccion::where('estado', 'A')
                        ->where('fk_id_project_Company', $proyecto)
                        ->where('fk_id_formula_lista', $datos['FORMULA'])
                        ->where('fk_id_planta', $datos['PLANTA'])
                        ->count();
                    if ($materialcdp == 0) {
                        $inconsistencia .= '/n PLANTA';
                    }
                }
            }
        }

        // validamos si hubo alguna inconsistencia con los datos enviados
        if ($inconsistencia == '') {
            $solicitud = new WbSolicitudMateriales();
            $solicitud->fk_id_usuarios = $this->traitGetIdUsuarioToken($req);
            $solicitud->fk_id_tipo_capa = $datos['CAPA'];
            $solicitud->fk_id_tramo = $datos['TRAMO'];
            $solicitud->fk_id_hito = $datos['HITO'];
            $solicitud->abscisaInicialReferencia = $datos['ABSCISAINICIAL'];
            $solicitud->abscisaFinalReferencia = $datos['ABSCISAFINAL'];
            $solicitud->fk_id_tipo_carril = $datos['CARRIL'];
            $solicitud->fk_id_tipo_calzada = $datos['CALZADA'];
            $solicitud->fk_id_material = $datos['MATERIAL'];
            $solicitud->fechaProgramacion = $datos['FECHA'];
            $solicitud->Cantidad = $datos['CANTIDAD'];
            $solicitud->numeroCapa = $datos['NROCAPA'];
            $solicitud->notaUsuario = $datos['OBSERVACION'];
            $solicitud->fk_id_estados = $datos['ESTADO'];
            $solicitud->fk_id_formula = $datos['FORMULA'];
            $solicitud->fk_id_planta = $datos['PLANTA'];
            $solicitud->id_solicitud_Materiales;
            try {
                $solicitud = $this->traitSetProyectoYCompania($req, $solicitud);
                $solicitud->save();
                $solicitud = $this->consultaCoincidenciaFrente($datos, $req);
                $id_solicitud = $solicitud->id_solicitud_Materiales;
                $confirmationController = new SmsController();
                $id_usuarios = $this->traitGetIdUsuarioToken($req);
                $nota = 'Solicitud de material';
                $mensaje = 'WEBU, La solicitud de material No. '.$id_solicitud.' ha sido radicada.';
                if ($datos['ESTADO'] == 12) {
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                } else {
                    $id_permiso = 69;
                    $mensaje1 = $mensaje.' y requiere de su aprobacion.';
                    $confirmationController->Enviar_Sms_Por_Permiso($mensaje1, $nota, $id_permiso);
                    $mensaje2 = $mensaje.' y esta pendiente aprobacion.';
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje2, $nota, $id_usuarios);
                }

                return $this->handleAlert($solicitud->id_solicitud_Materiales, true);
                // return $this->handleResponse($req, [], $solicitud->id_solicitud_Materiales);
            } catch (\Exception $exc) {
                Log::error('SOLICITUD MATERIAL:  Error: '.$exc);

                return $this->handleAlert('Error al registrar la solicitud.', 0);
            }
        } else {
            return $this->handleAlert('Revisar los siguientes datos: '.$inconsistencia, false);
        }
    }

    public function consultaCoincidenciaFrente($datos, Request $request = null)
    {
        $consulta = WbSolicitudMateriales::select('id_solicitud_Materiales')
            ->where('fk_id_tipo_capa', $datos['CAPA'])
            ->where('fk_id_tramo', $datos['TRAMO'])
            ->where('fk_id_hito', $datos['HITO'])
            ->where('fechaProgramacion', $datos['FECHA'])
            ->where('abscisaInicialReferencia', $datos['ABSCISAINICIAL'])
            ->where('abscisaFinalReferencia', $datos['ABSCISAFINAL'])
            ->where('fk_id_tipo_carril', $datos['CARRIL'])
            ->where('fk_id_tipo_calzada', $datos['CALZADA'])
            ->where('fk_id_material', $datos['MATERIAL'])
            ->where('fechaProgramacion', $datos['FECHA'])
            ->where('Cantidad', '=', $datos['CANTIDAD'])
            ->where('numeroCapa', $datos['NROCAPA'])
            ->where('notaUsuario', $datos['OBSERVACION'])
            ->where('fk_id_estados', $datos['ESTADO'])
            ->where('fk_id_formula', $datos['FORMULA'])
            ->where('fk_id_planta', $datos['PLANTA'])
            ->where('abscisaFinalReferencia', $datos['ABSCISAFINAL'])
            ->whereRaw('DATEDIFF(Minute,datecreation,GETDATE())<?', 10)
            ->whereRaw('DATEDIFF(Minute,datecreation,GETDATE())>=?', 0);
        if ($request != null) {
            $aux = new WbSolicitudMateriales();
            $aux = $this->traitSetProyectoYCompania($request, $aux);
            $consulta = $consulta->where('fk_id_project_Company', $aux->fk_id_project_Company)
                ->where('fk_compañia', $aux->fk_compañia);
        }

        return $consulta->first();
    }

    public function cerrarSolicitudMaterial(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert(__('messages.solicitud_de_material_no_valida'));
        }
        $modelo = WbSolicitudMateriales::where('fk_id_project_Company', $this->traitGetProyectoCabecera($request))->find($id);

        if ($modelo == null) {
            return $this->handleAlert(__('messages.solicitud_de_material_no_encontrada'));
        }
        $validator = Validator::make($request->all(), [
            'fechaCierre' => 'required|string',
            'cantidadReal' => 'required|numeric',
            'notaCierre' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->handleAlert('');
        }
        $modelo->fecha_cierre = $request->fechaCierre;
        $modelo->fk_id_estados = 15;
        $modelo->cantidad_real = $request->cantidadReal;
        $modelo->nota_cierre = $request->notaCierre;
        if ($this->traitGetIdUsuarioToken($request) == $modelo->fk_id_usuarios) {
            if ($modelo->save()) {
                $confirmationController = new SmsController();
                $id_usuarios = $this->traitGetIdUsuarioToken($request);
                $mensaje = 'WEBU, La solicitud de material '.$id.' se cerro correctamente.';
                $nota = 'Solicitud de material';
                $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);

                return $this->handleResponse($request, [], __('messages.solicitud_de_material_cerrado'));
            } else {
                return $this->handleAlert('No guardado');
            }
        } else {
            return $this->handleAlert(__('messages.no_puede_cerrar_solicitudes_de_material_de_otros'));
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
