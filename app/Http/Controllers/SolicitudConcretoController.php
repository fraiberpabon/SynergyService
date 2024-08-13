<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Compania;
use App\Models\estado;
use App\Models\solicitudConcreto;
use App\Models\usuarios_M;
use App\Models\WbTramos;
use App\Models\WbHitos;
use App\Models\estructuras;
use App\Models\Formula;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Monolog\Handler\IFTTTHandler;
use Illuminate\Support\Facades\Log;


class SolicitudConcretoController extends BaseController implements Vervos
{

    public function update(Request $req, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert(__('messages.solicitud_de_concreto_no_valida'));
        }
        if (!$req->json()->has('fechaProgramacion')) {
            return $this->handleAlert(__('messages.ingrese_la_fecha_de_programacion'));
        }
        if (!$req->json()->has('nota')) {
            return $this->handleAlert(__('messages.ingrese_la_nota'));
        }
        if (!$req->json()->has('op')) {
            return $this->handleAlert(__('messages.ingrese_una_opcion'));
        }
        if (!($req->op == 'M' || $req->op == 'A')) {
            return $this->handleAlert(__('messages.opcion_no_valida'));
        }
        if (
            $req->validate([
                'fechaProgramacion' => 'required|string', //formatear fecha
                'nota' => 'required|string',
            ])
        ) {
            $solicitudModificar = solicitudConcreto::find($id);
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($solicitudModificar == null) {
                return $this->handleAlert(__('messages.solicitud_concreto_no_encontrada'));
            }
            if ($solicitudModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert(__('messages.solicitud_concreto_no_valida'));
            }
            try {
                switch ($req->op) {
                    case 'M':
                        if (!$req->json()->has('asentamiento')) {
                            return $this->handleAlert(__('messages.ingrese_el_asentamiento'));
                        }
                        if (!$req->json()->has('volumen')) {
                            return $this->handleAlert(__('messages.ingrese_el_volumen'));
                        }
                        if (!$req->json()->has('planta')) {
                            return $this->handleAlert(__('messages.ingrese_la_planta'));
                        }
                        if (
                            $req->validate([
                                'asentamiento' => 'required|integer',
                                'volumen' => 'required|numeric',
                                'planta' => 'required|string',
                            ])
                        ) {
                            $solicitudModificar->fechaProgramacion = $req->fechaProgramacion;
                            $solicitudModificar->asentamiento = $req->asentamiento;
                            $solicitudModificar->volumen = $req->volumen;
                            $solicitudModificar->PlantaDestino = $req->planta;
                            $solicitudModificar->nota = $req->nota;

                            $solicitudModificar->save();
                            return $this->handleResponse($req, $solicitudModificar, __('messages.criterio_modificado'));
                        }
                        break;
                    case 'A':
                        $solicitudModificar->estado = 'ANULADO';
                        $solicitudModificar->notaCierre = $req->nota;
                        $solicitudModificar->fechaAceptacion = $req->fechaProgramacion;
                        $solicitudModificar->save();
                        return $this->handleResponse($req, $solicitudModificar, __('messages.criterio_anulado'));
                        break;
                }
            } catch (Exception $exc) {
                switch ($req->op) {
                    case 'M':
                        return $this->handleAlert(__('messages.no_se_pudo_modificar_la_solicitud'));
                        break;
                    case 'A':
                        return $this->handleAlert(__('messages.no_se_pudo_anular_la_solicitud'));
                        break;
                }
            }
        }
    }

    public function cerrarSolicitud(Request $request, $id)
    {
        $solicitud = solicitudConcreto::find($id);
        if ($solicitud == null) {
            return $this->handleAlert(__("messages.solicitud_no_encontrada"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($solicitud->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.solicitud_no_valida"));
        }
        $solicitud->fechaAceptacion = date("j/n/Y");
        $solicitud->estado = 'ENVIADO';
        $confirmationController = new SmsController();
        // $id_usuarios = $this->traitGetIdUsuarioToken($request);
        $id_usuarios = $solicitud->fk_usuario;
        $mensaje = 'WEBU, Su solicitud  de concreto No. ' . $id . ' fue cerrada correctamente.';
        $nota = 'Solicitud de concreto';
        $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
        $solicitud->save();
        return $this->handleResponse($request, [], __("messages.consultado"));
    }

    public function borrador(Request $req, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Solicitud no valida.');
        }
        if (!$req->json()->has('fechaHoraSolicitud')) {
            return $this->handleAlert('Ingrese la fechaHoraSolicitud.');
        }
        if (!$req->json()->has('estado')) {
            return $this->handleAlert('Ingrese la estado.');
        }
        if (!$req->json()->has('hora')) {
            return $this->handleAlert('Ingrese la hora.');
        }
        if (
            $req->validate([
                'fechaHoraSolicitud' => '',
                'estado' => '',
                'hora' => ''
            ])
        ) {
            $modeloModificar = solicitudConcreto::find($id);
            if ($modeloModificar == null) {
                return $this->handleAlert('Solicitud no encontrada.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Solicitud no valida.');
            }
            $modeloModificar->fechaHoraSolicitud = $req->fechaHoraSolicitud;
            $modeloModificar->estado = $req->estado;
            $modeloModificar->Hora = $req->hora;
            try {
                if ($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Solicitud borrador modificado.');
                }
            } catch (Exception $exc) {
            }
            return $this->handleAlert('Solicitud borrador no modificado.');
        }
    }




    public function postDeprecated(Request $req)
    {
        //validamos que se reciba la informacion requerida
        $validator = Validator::make($req->all(), [
            'IDUSUARIO' => 'required|numeric',
            'TRAMO' => 'required|string',
            'HITO' => 'required|string',
            'ELEMENTOVACIAR' => 'required|string', //validacion adicional
            'NOMENCLATURA' => 'required|string',
            'ABSCISA' => 'required|string', //no validar
            'MEZCLA' => 'required|string', //no validar
            'RESISTENCIA' => 'required|string', //no validar
            'DMX' => 'required|string', //no validar
            'ASENTAMIENTO' => 'required|numeric', //no validar
            'CALZADA' => 'required|string', //no validar
            'CANTIDAD' => 'required|numeric', //no validar
            'PLANTA' => 'required|string',
            'OBSERVACION' => 'present|string|nullable', //no validar
            'EMPRESA' => 'required|string', //no validar
            'UBICACIONGPS' => 'present|string|nullable', //no validar
            'UBICACIONRED' => 'present|string|nullable', //no validar
            'CDC' => 'required|string',
            'ESTADO' => 'required|string', //no validar
            'PROGRAMACION' => 'required|string', //no validar
            'FORMULA' => 'required|string', //validacion adicional
            'IDESTRUCTURA' => 'present|nullable|numeric'
        ]);


        //si no cumple validaciones imprime error
        if ($validator->fails()) {
            //return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (001)",false);
            return $this->handleAlert($validator->errors(), false);
            // return $validator->errors();
        }

        $datos = $req->all();

        //iniciamos la validacion de la informacion recibida
        //validando id del usuario
        $usuario = usuarios_M::where('id_usuarios', $datos['IDUSUARIO'])->where('estado', 'A')->count();
        if ($usuario == 0) {
            return $this->handleAlert("Su cuenta se encuentra inactiva o temporalmente bloqueada, comuniquese con el area de sistemas(002)", false);
        }

        //validamos la existencia del tramo
        $tramo = WbTramos::where('Id_Tramo', $datos['TRAMO'])->where('estado', 'A')->count();
        if ($tramo == 0) {
            return $this->handleAlert("Información inconsistente, sincronice nuevamente la base de datos y vuelva a intentarlo, en caso de no funcionar comuníquese con el área de Sistemas (003)", false);
        }

        //validacion de la existencia del hito
        $hito = WbHitos::where('Id_Hitos', $datos['HITO'])->where('estado', 'A')->count();
        if ($hito == 0) {
            return $this->handleAlert("El hito seleccionado no se encuentra habilitado en la base de datos, sincronice nuevamente la base de datos y vuelva a intentarlo, en caso de no funcionar comuníquese con el área Tecnica (004)", false);
        }

        $estructura = substr($datos['ELEMENTOVACIAR'], 0, strrpos($datos['ELEMENTOVACIAR'], ' -'));
        $idestructura = null;

        //se valida si el elemento existe
        //si no se envia un id desde la aplicacion
        if (!isset($datos['IDESTRUCTURA'])) {
            //se consulta el id si la estructura es una cuneta
            if ($estructura == 'Cunetas') {
                $prueba = estructuras::where('TIPO_DE_ESTRUCTURA', $estructura)->where('HITO_OTRO_SI_10', $datos['HITO'])->where('fk_estado', '<>', 23)->first();
                $idestructura = $prueba->N;
            }
        } else {
            //si se envia un id se valida que sea unoa estructura sin terminar
            $existencia = estructuras::where('N', $datos['IDESTRUCTURA'])->where('fk_estado', '<>', 23)->count();
            if ($existencia == 0) {
                return $this->handleAlert("Información inconsistente, sincronice nuevamente la base de datos y vuelva a intentarlo, en caso de no funcionar comuníquese con el área de Sistemas (005)", false);
            }
            $idestructura = $datos['IDESTRUCTURA'];
        }
        //se procede a verificar que la formula seleccionada este activa
        $idFormula = null;
        $formula = Formula::where('estado', '1')->where('formula', $datos['FORMULA']);
        if ($formula->count() == 0) {
            return $this->handleAlert("Información inconsistente, sincronice nuevamente la base de datos y vuelva a intentarlo, en caso de no funcionar comuníquese con el área de Sistemas (006)", false);
        }

        $formula = $formula->first();
        $idFormula = $formula->id;

        $fecha = date('j/n/Y');
        $hora = date('g:i a');


        //procedemos a verifica la existencia previa de un registro de solicitud de liberacion
        $SolicitudConcreto = new solicitudConcreto();
        $SolicitudConcreto->fk_usuario = $datos['IDUSUARIO'];
        $SolicitudConcreto->tramo = $datos['TRAMO'];
        $SolicitudConcreto->hito = $datos['HITO'];
        $SolicitudConcreto->elementoVaciar = $datos['ELEMENTOVACIAR'];
        $SolicitudConcreto->nomenclatura = $datos['NOMENCLATURA'];
        $SolicitudConcreto->abscisas = $datos['ABSCISA'];
        $SolicitudConcreto->tipoMezcla = $datos['MEZCLA'];
        $SolicitudConcreto->resistencia = $datos['RESISTENCIA'];
        $SolicitudConcreto->dmx = $datos['DMX'];
        $SolicitudConcreto->asentamiento = $datos['ASENTAMIENTO'];
        $SolicitudConcreto->calzada = $datos['CALZADA'];
        $SolicitudConcreto->volumen = $datos['CANTIDAD'];
        $SolicitudConcreto->PlantaDestino = $datos['PLANTA'];
        $SolicitudConcreto->nota = $datos['OBSERVACION'];
        $SolicitudConcreto->nombreCompañia = $datos['EMPRESA'];
        $SolicitudConcreto->ubicacionSolicitudGps = $datos['UBICACIONGPS'];
        $SolicitudConcreto->ubicacionSolicitudNetwork = $datos['UBICACIONRED'];
        $SolicitudConcreto->CostCode = $datos['CDC'];
        $SolicitudConcreto->estado = $datos['ESTADO'];
        $SolicitudConcreto->fechaProgramacion = $datos['PROGRAMACION'];
        $SolicitudConcreto->fk_id_formula = $idFormula;

        $SolicitudConcreto->Fk_id_estructura = $idestructura;

        $SolicitudConcreto->fechaHoraSolicitud = $fecha;
        $SolicitudConcreto->Hora = $hora;

        $SolicitudConcreto->save();
        //consultamos si el registro fue insertado
        $Solicitud2 = solicitudConcreto::where('fk_usuario', $datos['IDUSUARIO'])
            ->where('tramo', $datos['TRAMO'])
            ->where('hito', $datos['HITO'])
            ->where('elementoVaciar', $datos['ELEMENTOVACIAR'])
            ->where('nomenclatura', $datos['NOMENCLATURA'])
            ->where('abscisas', $datos['ABSCISA'])
            ->where('tipoMezcla', $datos['MEZCLA'])
            ->where('resistencia', $datos['RESISTENCIA'])
            ->where('dmx', $datos['DMX'])
            ->where('asentamiento', $datos['ASENTAMIENTO'])
            ->where('calzada', $datos['CALZADA'])
            ->where('volumen', $datos['CANTIDAD'])
            ->where('PlantaDestino', $datos['PLANTA'])
            ->where('nota', $datos['OBSERVACION'])
            ->where('nombreCompañia', $datos['EMPRESA'])
            ->where('ubicacionSolicitudGps', $datos['UBICACIONGPS'])
            ->where('ubicacionSolicitudNetwork', $datos['UBICACIONRED'])
            ->where('CostCode', $datos['CDC'])
            ->where('estado', $datos['ESTADO'])
            ->where('fechaProgramacion', $datos['PROGRAMACION'])
            ->where('fk_id_formula', $idFormula)

            ->where('Fk_id_estructura', $idestructura)

            ->where('fechaHoraSolicitud', $fecha)
            ->where('Hora', $hora)
            ->orderby('id_solicitud', 'desc')
            ->first();

        return $this->handleAlert($Solicitud2->id_solicitud, true);
    }

    public function borador(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fechaHoraSolicitud' => 'required|string',
            'estado' => 'required|string',
            'hora' => 'required|string',
        ]);
        if (!is_numeric($id)) {
            return $this->handleAlert(__('messages.solicitud_concreto_no_valida'));
        }
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }
        $modelo = solicitudConcreto::where('fk_id_project_Company', $this->traitGetProyectoCabecera($request))->find($id);
        if ($modelo == null) {
            return $this->handleAlert(__('messages.solicitud_concreto_no_encontrada'));
        }
        $modelo->fechaHoraSolicitud = $request->fechaHoraSolicitud;
        $modelo->estado = $request->estado;
        $modelo->Hora = $request->hora;
        if ($modelo->save()) {
            return $this->handleResponse($request, [], __('messages.solicitud_de_concreto_guardada_como_borrador'));
        } else {
            return $this->handleAlert(__('messages.solicitud_de_concreto_no_actualizada'));
        }
    }

    public function cerrarLiberaccion(Request $request, $solicitud)
    {
        $validator = Validator::make($request->all(), [
            'fechaAceptacion' => 'required|string',
            'estado' => 'required|string',
            'volumenReal' => 'required|numeric',
            'notaCierre' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }
        if (!is_numeric($solicitud)) {
            return $this->handleAlert(__('messages.solicitud_concreto_no_valida'));
        }
        $modelo = solicitudConcreto::where('fk_id_project_Company', $this->traitGetProyectoCabecera($request))->find($solicitud);
        if ($modelo == null) {
            return $this->handleAlert(__('messages.solicitud_concreto_no_encontrada'));
        }
        if ($modelo->fk_usuario != $this->traitGetIdUsuarioToken($request)) {
            return $this->handleAlert('no_puede_cerrar_solicitudes_de_concreto_de_otros');
        }
        $modelo->fechaAceptacion = $request->fechaAceptacion;
        $modelo->estado = $request->estado;
        $modelo->volumenReal = $request->volumenReal;
        $modelo->notaCierre = $request->notaCierre;
        if ($modelo->save()) {
            return $this->handleResponse($request, [], __('messages.solicitud_de_concreto_actualizada'));
        } else {
            return $this->handleAlert(__('messages.solicitud_de_concreto_no_actualizada'));
        }
    }

    public function getBorradores(Request $request)
    {
        $consulta = solicitudConcreto::select(
            DB::raw("usu.Nombre +' '+ usu.Apellido as [nombre_completo ]"),
            's.id_solicitud',
            's.estado',
            DB::raw("s.PlantaDestino + 'Tramo: ' + CONVERT(varchar, tramo)  +' Hito: '+ [hito]  as [Ubicacion]"),
            'elementoVaciar',
            'nomenclatura',
            'abscisas',
            'tipoMezcla',
            'resistencia',
            'dmx',
            'asentamiento',
            'calzada',
            'volumen',
            'nota',
            's.fechaHoraSolicitud',
            's.nombreCompañia',
            'lib.firmaAmbiental',
            'lib.firmaCalidad',
            'lib.firmaProduccion',
            'lib.firmaSST',
            'lib.firmaTopografia',
            'lib.firmaLaboratorio',
            DB::raw("s.Hora,usu.Nombre +' '+ usu.Apellido as [nombre]"),
            's.nombreCompañia as compania',
            'CostCode',
            'usu.Correo',
            'toneFaltante',
            's.fk_usuario',
            'usu.id_usuarios',
            'fechaProgramacion'
        )->from('SolicitudConcreto as s')
            ->leftJoin('usuarioss as usu', 'usu.id_usuarios', 's.fk_usuario')
            ->leftJoin('Liberaciones as lib', 'lib.fk_solicitud', 's.id_solicitud')
            ->where('s.estado', 'BORRADOR')
            ->where('s.fk_usuario', $this->traitGetIdUsuarioToken($request))
            ->orderBy('id_solicitud', 'desc');
        $consulta = $this->filtrar($request, $consulta, 's')->get();
        return $this->handleResponse($request, $consulta, __('messages.consultado'));
    }

    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $req)
    {
        //validamos que se reciba la informacion requerida
        $validator = Validator::make($req->all(), [
            'TRAMO' => 'required|string',
            'HITO' => 'required|string',
            'ELEMENTOVACIAR' => 'required|string', //validacion adicional
            'NOMENCLATURA' => 'required|string',
            'ABSCISA' => 'required|string', //no validar
            'MEZCLA' => 'required|string', //no validar
            'RESISTENCIA' => 'required|string', //no validar
            'DMX' => 'required|string', //no validar
            'ASENTAMIENTO' => 'required|numeric', //no validar
            'CALZADA' => 'required|string', //no validar
            'CANTIDAD' => 'required|numeric', //no validar
            'PLANTA' => 'required|string',
            'OBSERVACION' => 'present|string|nullable', //no validar
            'UBICACIONGPS' => 'present|string|nullable', //no validar
            'UBICACIONRED' => 'present|string|nullable', //no validar
            'CDC' => 'required|string',
            'ESTADO' => 'required|string', //no validar
            'PROGRAMACION' => 'nullable|string', //no validar
            'FORMULA' => 'nullable|string', //validacion adicional
            'IDESTRUCTURA' => 'present|nullable|numeric'
        ]);


        //si no cumple validaciones imprime error
        if ($validator->fails()) {
            //return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (001)",false);
            return $this->handleAlert($validator->errors(), false);
            // return $validator->errors();
        }

        $datos = $req->all();

        //validamos la existencia del tramo
        $tramo = WbTramos::where('Id_Tramo', $datos['TRAMO'])->where('estado', 'A')->count();
        if ($tramo == 0) {
            return $this->handleAlert(__('messages.información_inconsistente_sincronice_nuevamente_la_base_de_datos_y_vuelva_a_intentarlo_en_caso_de_no_funcionar_comuníquese_con_el_área_de_Sistemas') . " (003)", false);
        }

        //validacion de la existencia del hito
        $hito = WbHitos::where('Id_Hitos', $datos['HITO'])->where('estado', 'A')->count();
        if ($hito == 0) {
            return $this->handleAlert(__('messages.el_hito_seleccionado_no_se_encuentra_habilitado_en_la_base_de_datos_sincronice_nuevamente_la_base_de_datos_y_vuelva_a_intentarlo_en_caso_de_no_funcionar_comuníquese_con_el_área_Tecnica') . " (004)", false);
        }

        $estructura = substr($datos['ELEMENTOVACIAR'], 0, strrpos($datos['ELEMENTOVACIAR'], ' -'));
        $idestructura = null;

        //se valida si el elemento existe
        //si no se envia un id desde la aplicacion
        if (!isset($datos['IDESTRUCTURA']) || strlen($datos['IDESTRUCTURA']) == 0) {
            //se consulta el id si la estructura es una cuneta
            if ($estructura == 'Cunetas') {
                $prueba = estructuras::where('TIPO_DE_ESTRUCTURA', $estructura)->where('HITO_OTRO_SI_10', $datos['HITO'])->where('fk_estado', '<>', 23)->first();
                $idestructura = $prueba->N;
            }
        } else {
            //si se envia un id se valida que sea unoa estructura sin terminar
            $existencia = estructuras::where('N', $datos['IDESTRUCTURA'])->where('fk_estado', '<>', 23)->count();
            if ($existencia == 0) {
                return $this->handleAlert(__('messages.información_inconsistente_sincronice_nuevamente_la_base_de_datos_y_vuelva_a_intentarlo_en_caso_de_no_funcionar_comuníquese_con_el_área_de_Sistemas') . " (005)", false);
            }
            $idestructura = $datos['IDESTRUCTURA'];
        }
        //se procede a verificar que la formula seleccionada este activa
        $idFormula = null;
        if (strlen($datos['FORMULA']) > 0) {
            if (Formula::find($datos['FORMULA']) == null) {
                return $this->handleAlert(__('messages.información_inconsistente_sincronice_nuevamente_la_base_de_datos_y_vuelva_a_intentarlo_en_caso_de_no_funcionar_comuníquese_con_el_área_de_Sistemas') . " (006)", false);
            }
            $idFormula = $datos['FORMULA'];
        }
        $fecha = date('j/n/Y');
        $hora = date('g:i a');
        $fechaFull = date('j/n/Y g:i a');
        $idEmpresa = $this->traitIdEmpresaPorProyecto($req);
        if ($idEmpresa == null) {
            return $this->handleAlert(__('messages.usuario_sin_empresa_asignada'));
        }
        //validar si existe ya uno con los mismos datos
        $empresa = Compania::find($idEmpresa);
        $isDuplicado = solicitudConcreto::select('id_solicitud')
            ->where('fk_usuario', $this->traitGetIdUsuarioToken($req))
            ->where('tramo', $datos['TRAMO'])
            ->where('hito', $datos['HITO'])
            ->where('elementoVaciar', $datos['ELEMENTOVACIAR'])
            ->where('nomenclatura', $datos['NOMENCLATURA'])
            ->where('abscisas', $datos['ABSCISA'])
            ->where('tipoMezcla', $datos['MEZCLA'])
            ->where('resistencia', $datos['RESISTENCIA'])
            ->where('dmx', $datos['DMX'])
            ->where('asentamiento', $datos['ASENTAMIENTO'])
            ->where('calzada', $datos['CALZADA'])
            ->where('volumen', $datos['CANTIDAD'])
            ->where('PlantaDestino', $datos['PLANTA'])
            ->where('nota', $datos['OBSERVACION'])
            ->where('nombreCompañia', $empresa->nombreCompañia)
            ->where('CostCode', $datos['CDC'])
            ->where('estado', $datos['ESTADO'])
            ->where('fechaProgramacion', $datos['PROGRAMACION'])
            ->where('fk_id_formula', $idFormula)
            ->where('estado', "PENDIENTE") //estado pendiente
            ->whereRaw("DATEDIFF(MINUTE, GETDATE(), CONVERT(DATETIME, CONVERT(VARCHAR, fechaHoraSolicitud , 101) + ' ' + CONVERT(VARCHAR, Hora, 8))) >= -10")->first();
        if ($isDuplicado != null) {
            return $this->handleAlert($isDuplicado->id_solicitud, true);
        } else {
            //procedemos a verifica la existencia previa de un registro de solicitud de liberacion
            $SolicitudConcreto = new solicitudConcreto();
            $SolicitudConcreto->fk_usuario = $this->traitGetIdUsuarioToken($req);
            $SolicitudConcreto->tramo = $datos['TRAMO'];
            $SolicitudConcreto->hito = $datos['HITO'];
            $SolicitudConcreto->elementoVaciar = $datos['ELEMENTOVACIAR'];
            $SolicitudConcreto->nomenclatura = $datos['NOMENCLATURA'];
            $SolicitudConcreto->abscisas = $datos['ABSCISA'];
            $SolicitudConcreto->tipoMezcla = $datos['MEZCLA'];
            $SolicitudConcreto->resistencia = $datos['RESISTENCIA'];
            $SolicitudConcreto->dmx = $datos['DMX'];
            $SolicitudConcreto->asentamiento = $datos['ASENTAMIENTO'];
            $SolicitudConcreto->calzada = $datos['CALZADA'];
            $SolicitudConcreto->volumen = $datos['CANTIDAD'];
            $SolicitudConcreto->PlantaDestino = $datos['PLANTA'];
            $SolicitudConcreto->nota = $datos['OBSERVACION'];
            $SolicitudConcreto->nombreCompañia = $empresa->nombreCompañia;
            $SolicitudConcreto->ubicacionSolicitudGps = $datos['UBICACIONGPS'];
            $SolicitudConcreto->ubicacionSolicitudNetwork = $datos['UBICACIONRED'];
            $SolicitudConcreto->CostCode = $datos['CDC'];
            $SolicitudConcreto->estado = $datos['ESTADO'];
            $SolicitudConcreto->fechaProgramacion = $datos['PROGRAMACION'];
            $SolicitudConcreto->fk_id_formula = $idFormula;
            $SolicitudConcreto->Fk_id_estructura = $idestructura;

            $SolicitudConcreto->fechaHoraSolicitud = $fecha;
            $SolicitudConcreto->Hora = $hora;
            $SolicitudConcreto = $this->traitSetProyectoYCompania($req, $SolicitudConcreto);

            try {
                $SolicitudConcreto->save();
                $SolicitudConcreto = solicitudConcreto::select('id_solicitud')
                    ->where('fk_usuario', $this->traitGetIdUsuarioToken($req))
                    ->where('tramo', $datos['TRAMO'])
                    ->where('hito', $datos['HITO'])
                    ->where('elementoVaciar', $datos['ELEMENTOVACIAR'])
                    ->where('nomenclatura', $datos['NOMENCLATURA'])
                    ->where('abscisas', $datos['ABSCISA'])
                    ->where('tipoMezcla', $datos['MEZCLA'])
                    ->where('resistencia', $datos['RESISTENCIA'])
                    ->where('dmx', $datos['DMX'])
                    ->where('asentamiento', $datos['ASENTAMIENTO'])
                    ->where('calzada', $datos['CALZADA'])
                    ->where('volumen', $datos['CANTIDAD'])
                    ->where('PlantaDestino', $datos['PLANTA'])
                    ->where('nota', $datos['OBSERVACION'])
                    ->where('nombreCompañia', $empresa->nombreCompañia)
                    ->where('ubicacionSolicitudGps', $datos['UBICACIONGPS'])
                    ->where('ubicacionSolicitudNetwork', $datos['UBICACIONRED'])
                    ->where('CostCode', $datos['CDC'])
                    ->where('estado', $datos['ESTADO'])
                    ->where('fechaProgramacion', $datos['PROGRAMACION'])
                    ->where('fk_id_formula', $idFormula)->first();
                $confirmationController = new SmsController();
                $id_usuarios = $this->traitGetIdUsuarioToken($req);
                $mensaje = 'WEBU, Su solicitud de concreto No. ' . $SolicitudConcreto['id_solicitud'] . ' ha sido radicada.';
                $nota = 'Solicitud de concreto';
                $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                return $this->handleAlert($SolicitudConcreto->id_solicitud, true);
            } catch (Exception $exc) {
                return $this->handleAlert(__('messages.error_solicitud_concreto'), false);
            }
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

    public function numeroSolicitudesConcretoRealizados(Request $request)
    {
        $consulta = solicitudConcreto::where('fk_usuario', '!=', 1)
            ->where('estado', '!=', 'ANULADO')
            ->where('estado', '!=', 'BORRADOR')
            ->get()->count();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    public function get(Request $request)
    {
        // TODO: Implement get() method.
        $consulta = $this->getConsultaGeneral();
        if ($request->estado) {
            $consulta = $consulta->where('SolicitudConcreto.estado', $request->estado);
        }
        if ($request->id) {
            $consulta = $consulta->where('SolicitudConcreto.id_solicitud', $request->id);
        }
        if ($request->estadoFirma && strlen($request->estadoFirma) > 0) {
            if ($request->estadoFirma == 'firmado') {
                $consulta = $consulta->where(function ($query) {
                    $query->whereRaw('ISNUMERIC(Liberaciones.firmaLaboratorio)=1')
                        ->whereRaw('ISNUMERIC(Liberaciones.firmaAmbiental)=1')
                        ->whereRaw('ISNUMERIC(Liberaciones.firmaCalidad)=1')
                        ->whereRaw('ISNUMERIC(Liberaciones.firmaProduccion)=1')
                        ->whereRaw('ISNUMERIC(Liberaciones.firmaSST)=1')
                        ->whereRaw('ISNUMERIC(Liberaciones.firmaTopografia)=1');
                });
            } else if ($request->estadoFirma == 'pendiente') {
                $consulta = $consulta->where(function ($query) {
                    $query->orWhereRaw('ISNUMERIC(Liberaciones.firmaLaboratorio)=0')
                        ->orWhereRaw('ISNUMERIC(Liberaciones.firmaAmbiental)=0')
                        ->orWhereRaw('ISNUMERIC(Liberaciones.firmaCalidad)=0')
                        ->orWhereRaw('ISNUMERIC(Liberaciones.firmaProduccion)=0')
                        ->orWhereRaw('ISNUMERIC(Liberaciones.firmaSST)=0')
                        ->orWhereRaw('ISNUMERIC(Liberaciones.firmaTopografia)=0');
                });
            }
        }
        if ($request->planta && strlen($request->planta) > 0) {
            $like = '%' . strtolower($request->planta) . '%';
            $consulta = $consulta->whereRaw("LOWER(SolicitudConcreto.PlantaDestino) like (?)", $like);
        }
        if ($request->estructura && strlen($request->estructura) > 0) {
            $like = '%' . strtolower($request->estructura) . '%';
            $consulta = $consulta->whereRaw("LOWER(SolicitudConcreto.elementoVaciar) like (?)", $like);
        }
        if ($request->fechaSolicitud && strlen($request->fechaSolicitud) > 0) {
            $consulta = $consulta->where('SolicitudConcreto.fechaHoraSolicitud', $request->fechaSolicitud);
        }
        if ($request->fechaProgramacion && strlen($request->fechaProgramacion) > 0) {
            $consulta = $consulta->where('fechaProgramacion', $request->fechaProgramacion);
        }
        $consulta = $this->filtrar($request, $consulta, 'SolicitudConcreto');
        $limitePaginas = 1;
        if ($request->query('page') && is_numeric($request->page) && $request->query('limit') && is_numeric($request->limit)) {
            $contador = clone $consulta;
            $contador = $this->filtrar($request, $contador, 'SolicitudConcreto');
            $contador = $contador->select('SolicitudConcreto.id_solicitud')->get();
            $consulta = $consulta->forPage($request->page, $request->limit)->get();
            $limitePaginas = ($contador->count() / $request->limit) + 1;
        } else {
            $consulta = $consulta->get();
        }
        foreach ($consulta as $item) {
            $verificar = 0;
            if (is_numeric($item->firmaLaboratorio)) {
                $verificar += 1;
            }
            if (is_numeric($item->firmaAmbiental)) {
                $verificar += 1;
            }
            if (is_numeric($item->firmaCalidad)) {
                $verificar += 1;
            }
            if (is_numeric($item->firmaProduccion)) {
                $verificar += 1;
            }
            if (is_numeric($item->firmaSST)) {
                $verificar += 1;
            }
            if (is_numeric($item->firmaTopografia)) {
                $verificar += 1;
            }
            $item->compañiaD = '';
            if ($verificar >= 6) {
                $item->firma = 'firmado';
            } else if ($verificar != 6) {
                $item->firma = 'pendiente';
            }
        }
        return $this->handleResponse($request, $consulta, __("messages.consultado"), $limitePaginas);
    }

    public function getPendientes()
    {
        $consulta = $this->getConsultaGeneral();
        $consulta = $consulta->where('SolicitudConcreto.estado', 'PENDIENTE');
        $consulta = $this->filtrar(\request(), $consulta, 'SolicitudConcreto')->get();
        return $this->handleResponse(\request(), $consulta, __("messages.consultado"));
    }

    public function getConsultaGeneral()
    {
        return solicitudConcreto::select(
            'SolicitudConcreto.id_solicitud as identificador',
            Db::raw("usuarioss.Nombre + ' ' + usuarioss.Apellido as nombreCompleto"),
            'SolicitudConcreto.estado',
            'SolicitudConcreto.PlantaDestino as plantaDestino',
            Db::raw("+ 'Tramo: ' + CONVERT(varchar, tramo)  +' Hito: '+ hito  as ubicacion"),
            'elementoVaciar',
            'nomenclatura',
            'abscisas',
            'tipoMezcla',
            'resistencia',
            'dmx',
            'asentamiento',
            'calzada',
            'volumen',
            'nota',
            'SolicitudConcreto.fechaHoraSolicitud',
            'SolicitudConcreto.nombreCompañia',
            'Liberaciones.firmaAmbiental',
            'Liberaciones.firmaCalidad',
            'Liberaciones.firmaProduccion',
            'Liberaciones.firmaSST',
            'Liberaciones.firmaTopografia',
            'Liberaciones.firmaLaboratorio',
            'SolicitudConcreto.Hora as hora',
            Db::raw("usuarioss.Nombre +' '+ usuarioss.Apellido as nombre"),
            'SolicitudConcreto.nombreCompañia as compania',
            'CostCode as costCode',
            'usuarioss.Correo as correo',
            'toneFaltante',
            'SolicitudConcreto.volumenReal',
            'CNFCOSTCENTER.COSYNCCODE as cosYNcCode',
            'usuarioss.id_usuarios as usuario',
            DB::raw("(SELECT sum(cantiEnviada) FROM PlanillaControlConcreto WHERE fk_solicitud = id_solicitud  Group by fk_solicitud ) as volumenPlantas"),
            'fechaProgramacion'
        )
            ->leftJoin('usuarioss', 'usuarioss.id_usuarios', '=', 'SolicitudConcreto.fk_usuario')
            ->leftJoin('Liberaciones', 'Liberaciones.fk_solicitud', '=', 'SolicitudConcreto.id_solicitud')
            ->leftJoin('usuPlanta', 'usuPlanta.NombrePlanta', '=', 'SolicitudConcreto.PlantaDestino')
            ->leftJoin('CNFCOSTCENTER', 'CNFCOSTCENTER.COCEIDENTIFICATION', '=', 'usuPlanta.fk_id_centroCosto')
            ->orderBy('id_solicitud', 'desc');
    }

    function getPendientesOEnviados(Request $req)
    {
        if (!(is_numeric($req->page) && is_numeric($req->limit))) {
            return $this->handleAlert('FDaltan parametros');
        }
        $consulta = solicitudConcreto::from('SolicitudConcreto as s')
            ->select(
                Db::raw("usu.Nombre +' '+ usu.Apellido as nombre_completo"),
                's.id_solicitud as identificador',
                's.estado',
                's.PlantaDestino as plantaDestino',
                'tramo as Tramo',
                'hito  as Hito',
                Db::raw("CASE WHEN (CHARINDEX(' - ', elementoVaciar, 1) = 0) THEN elementoVaciar ELSE SUBSTRING(elementoVaciar, 1, CHARINDEX(' - ', elementoVaciar, 1) - 1) END AS Estructura"),
                Db::raw("CASE  WHEN (CHARINDEX(' - ', elementoVaciar, 1) = 0)  THEN elementoVaciar ELSE  SUBSTRING(elementoVaciar , CHARINDEX(' - ', elementoVaciar, 1) +3 ,LEN(elementoVaciar)) END AS Elemento"),
                'lib.firmaAmbiental',
                'lib.firmaCalidad',
                'lib.firmaProduccion',
                'lib.firmaSST',
                'lib.firmaTopografia',
                'lib.firmaLaboratorio',
                'nomenclatura',
                'abscisas',
                'tipoMezcla',
                'resistencia',
                'dmx',
                'asentamiento',
                'calzada',
                'volumen',
                'nota',
                Db::raw("FORMAT(CONVERT(date,s.fechaHoraSolicitud), 'g', 'es-CO' ) AS 'fechaHoraSolicitud'"),
                Db::raw("(SELECT TOP 1 fecha FROM PlanillaControlConcreto where fk_solicitud =s.id_solicitud) AS fechaDespacho ,s.nombreCompañia"),
                's.Hora',
                Db::raw("usu.Nombre +' '+ usu.Apellido as [nombre]"),
                's.nombreCompañia as compania',
                'CostCode as costCode',
                'usu.Correo',
                'toneFaltante',
                's.volumenReal',
                Db::raw("(SELECT sum(cantiEnviada) FROM PlanillaControlConcreto WHERE fk_solicitud = id_solicitud  Group by fk_solicitud ) as volumenPlantas"),
                Db::raw("(SELECT TOP 1 hora FROM PlanillaControlConcreto where fk_solicitud =s.id_solicitud) as horaP"),
                'usu1.matricula as ambiental',
                'usu2.matricula as calidad',
                'usu3.matricula as produccion',
                'usu4.matricula as sst',
                'usu5.matricula as topografia',
                'usu6.matricula as laboratorio',
                'Fk_id_estructura as idestructura'
            )
            ->leftJoin('usuarioss as usu', 'usu.id_usuarios', '=', 's.fk_usuario')
            ->leftJoin('Liberaciones as lib', 'lib.fk_solicitud', '=', 's.id_solicitud')
            ->leftJoin('usuarioss as usu1', \DB::raw("CAST(usu1.id_usuarios as varchar)"), '=', 'lib.firmaAmbiental')
            ->leftJoin('usuarioss as usu2', \DB::raw("CAST(usu2.id_usuarios as varchar)"), '=', 'lib.firmaCalidad')
            ->leftJoin('usuarioss as usu3', \DB::raw("CAST(usu3.id_usuarios as varchar)"), '=', 'lib.firmaProduccion')
            ->leftJoin('usuarioss as usu4', \DB::raw("CAST(usu4.id_usuarios as varchar)"), '=', 'lib.firmaSST')
            ->leftJoin('usuarioss as usu5', \DB::raw("CAST(usu5.id_usuarios as varchar)"), '=', 'lib.firmaTopografia')
            ->leftJoin('usuarioss as usu6', \DB::raw("CAST(usu6.id_usuarios as varchar)"), '=', 'lib.firmaLaboratorio')
            ->orderBy('id_solicitud', 'desc');
        if ($req->estadoFirma && strlen($req->estadoFirma) <= 0) {
            $consulta = $consulta
                ->whereOr('s.estado', 'PENDIENTE')
                ->whereOr('s.estado', 'ENVIADO');
        } else if ($req->estadoFirma && strlen($req->estadoFirma) > 0) {
            if ($req->estadoFirma == 'firmado') {
                $consulta = $consulta->where(function ($query) {
                    $query->whereRaw('ISNUMERIC(lib.firmaLaboratorio)=1')
                        ->whereRaw('ISNUMERIC(lib.firmaAmbiental)=1')
                        ->whereRaw('ISNUMERIC(lib.firmaCalidad)=1')
                        ->whereRaw('ISNUMERIC(lib.firmaProduccion)=1')
                        ->whereRaw('ISNUMERIC(lib.firmaSST)=1')
                        ->whereRaw('ISNUMERIC(lib.firmaTopografia)=1');
                });
            } else if ($req->estadoFirma == 'pendiente') {
                $consulta = $consulta->where(function ($query) {
                    $query->orWhereRaw('ISNUMERIC(lib.firmaLaboratorio)=0')
                        ->orWhereRaw('ISNUMERIC(lib.firmaAmbiental)=0')
                        ->orWhereRaw('ISNUMERIC(lib.firmaCalidad)=0')
                        ->orWhereRaw('ISNUMERIC(lib.firmaProduccion)=0')
                        ->orWhereRaw('ISNUMERIC(lib.firmaSST)=0')
                        ->orWhereRaw('ISNUMERIC(lib.firmaTopografia)=0');
                });
            }

            if ($req->estadoFirma != 'Enviado') {
                $consulta = $consulta
                    ->whereOr('s.estado', 'PENDIENTE')
                    ->whereOr('s.estado', 'ENVIADO');
            } else {
                $consulta = $consulta
                    ->where('s.estado', 'ENVIADO');
            }
        }
        if ($req->id && strlen($req->id) > 0) {
            $consulta = $consulta->where('id_solicitud', $req->id);
        }
        if ($req->fechaSolicitud && strlen($req->fechaSolicitud) > 0) {
            $consulta = $consulta->where('s.fechaHoraSolicitud', $req->fechaSolicitud);
        }
        $contador = clone $consulta;
        $contador = $contador->select('s.id_solicitud')->get();
        $rows = $contador->count();
        $limitePaginas = ($rows / $req->limit) + 1;
        $consulta = $consulta->forPage($req->page, $req->limit)->get();
        foreach ($consulta as $item) {
            $verificar = 0;
            if (is_numeric($item->firmaLaboratorio)) {
                $verificar += 1;
            }
            if (is_numeric($item->firmaAmbiental)) {
                $verificar += 1;
            }
            if (is_numeric($item->firmaCalidad)) {
                $verificar += 1;
            }
            if (is_numeric($item->firmaProduccion)) {
                $verificar += 1;
            }
            if (is_numeric($item->firmaSST)) {
                $verificar += 1;
            }
            if (is_numeric($item->firmaTopografia)) {
                $verificar += 1;
            }
            $item->compañiaD = '';
            if ($item->estado=='RECHAZADO'){
                $item->firmaa = 'Rechazado';
            }else{
                if ($verificar >= 6) {
                    $item->firmaa = 'firmado';
                } else if ($verificar != 6) {
                    $item->firmaa = 'pendiente';
                }
            }
        }
        return $this->handleResponse($req, $consulta, 'Consultado', $limitePaginas);
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
