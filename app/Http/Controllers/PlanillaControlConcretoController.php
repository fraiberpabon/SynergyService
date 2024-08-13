<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\CostCode;
use App\Models\PlanillaControlConcreto;
use App\Models\solicitudConcreto;
use App\Models\ts_Equipement;
use App\Models\WbConfiguraciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanillaControlConcretoController extends BaseController implements Vervos
{
    public function get(Request $request)
    {
        if (!is_numeric($request->page) || !is_numeric($request->limit) || ($request->solicitud && !is_numeric($request->solicitud))) {
            return $this->handleAlert('Datos invalidos');
        }
        $contador = PlanillaControlConcreto::select(
            'id_planilla',
        )->leftjoin('usuPlanta', 'usuPlanta.id_plata', '=', 'PlanillaControlConcreto.plantaDespacho');
        $consulta = PlanillaControlConcreto::select(
            'id_planilla',
            'fk_solicitud',
            'placaVehiculo',
            'codigoVehiculo',
            'hora',
            'wbeDestino',
            'descripDestino',
            'formula',
            'cantidad',
            'firma',
            'observacion',
            'fecha',
            'cantiEnviada',
            'fk_id_usuario',
            'turno',
            'dateCreate',
            'plantaDespacho',
            'usuPlanta.NombrePlanta',
            'motivo',
            'PlanillaControlConcreto.estado'
            
        )->leftjoin('usuPlanta', 'usuPlanta.id_plata', '=', 'PlanillaControlConcreto.plantaDespacho')
            ->where('PlanillaControlConcreto.estado', 1);
        if ($request->solicitud) {
            $consulta->where('fk_solicitud', $request->solicitud);
        }
        if ($request->fecha) {
            $consulta->where('fecha', $request->fecha);
        }
        if ($request->plantaSelecionada) {
            $consulta->where('plantaDespacho', $request->plantaSelecionada);
        }
        $consulta = $this->filtrar($request, $consulta, 'PlanillaControlConcreto')->orderby('id_planilla', 'DESC');
        $contador = $this->filtrar($request, $contador, 'PlanillaControlConcreto')->get();
        $contador = clone $consulta;
        $rows = $contador->count();
        $limitePaginas = ($rows / $request->limit) + 1;
        $consulta = $consulta->forPage($request->page, $request->limit)->get();

        return $this->handleResponse($request, $this->planillaControlAsfaltoToArray($consulta), __('messages.consultado'), $limitePaginas);
    }

    public function getBySolicitud(Request $request)
    {
        $consulta = PlanillaControlConcreto::select(
            'id_planilla',
            'fk_solicitud',
            'placaVehiculo',
            'codigoVehiculo',
            'hora',
            'wbeDestino',
            'descripDestino',
            'formula',
            'cantidad',
            'firma',
            'observacion',
            'fecha',
            'cantiEnviada',
            'fk_id_usuario',
            'turno',
            'dateCreate',
            'plantaDespacho',
            'motivo',
            'usuPlanta.NombrePlanta',
            'PlanillaControlConcreto.estado'
        )->leftjoin('usuPlanta', 'usuPlanta.id_plata', '=', 'PlanillaControlConcreto.plantaDespacho')
            ->where('fk_solicitud', $request->solicitud)
            ->whereIn('PlanillaControlConcreto.estado', [0, 1])
            ->orderby('id_planilla', 'DESC');
        $consulta = $this->filtrar($request, $consulta, 'PlanillaControlConcreto')->get();

        return $this->handleResponse($request, $this->planillaControlAsfaltoToArray($consulta), __('messages.consultado'));
    }

    public function post(Request $req)
    {
        // TODO: Implement post() method.
        $fechaSolicitud = date('j/n/Y');
        $hora = date('g:i a');
        $cdcM = strtoupper($req->cdc);
        $condigoM = strtoupper($req->codVehiculo);
        $consultoid = ts_Equipement::select('EquipmentID', 'SerialNumber')->where('EquipmentID', $condigoM)->first();
        $consultoCostCode = CostCode::select('CostCode')->where('CostCode', $cdcM)->first();
        if ($consultoid == null) {
            return $this->handleAlert(__('messages.codigo_de_vehiculo_no_encontrado'));
        }
        if ($consultoCostCode == null) {
            return $this->handleAlert(__('messages.codigo_de_vehiculo_no_encontrado'));
        }
        if ($req->volumen <= 0) {
            return $this->handleAlert('La cantidad a neviar no puede ser 0 o menor a 0');
        }
        $solicitudConreto = solicitudConcreto::find($req->id_solicitud);
        if (!$solicitudConreto) {
            return $this->handleAlert('Solicitud de concreto no encontrada.');
        }
        $sumaPlanillas = PlanillaControlConcreto::where('fk_solicitud', $req->id_solicitud)->where('estado', 1)->sum('cantiEnviada');
        $suma = $sumaPlanillas + $req->toneFaltante;
        // capturar valor porcentaje desde una tabla, donde se pueda confirgurar dinamicamente
        $porcentaje = WbConfiguraciones::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))->first();
        if (!$porcentaje) {
            return $this->handleError('Ocurrio un error al intentar guardar este despacho, consulte con el administrador, cod: 230.');
        }
        $limiteEnviado = $solicitudConreto->volumen * $porcentaje->porcentaje_concreto;
        $resta = $limiteEnviado - $suma;
        if ($resta < 0) {
            return $this->handleAlert('Por favor solicite una cantidad menor, la cantidad actual mas la cantidad anteriormente excede la cantidad solicitada.');
        }
        $proyecto = $this->traitGetProyectoCabecera($req);
        $registroAnterior = PlanillaControlConcreto::select()
            ->where('fk_solicitud', $req->id_solicitud)
            ->where('codigoVehiculo', $condigoM)
            ->where('fk_id_project_Company', $proyecto)
            ->where('estado', 1)
            ->whereRaw('DATEDIFF(MINUTE, GETDATE(), CONVERT(DATETIME, dateCreate)) >= -5')
            ->first();

        if ($registroAnterior != null) {
            return $this->handleResponse($req, [$registroAnterior->id_planilla], __('messages.planilla_de_control_concreto_registrado'));
        } else {
            $modeloRegistrar = new PlanillaControlConcreto();
            $modeloRegistrar->fk_solicitud = $req->id_solicitud;
            $modeloRegistrar->placaVehiculo = $consultoid->SerialNumber;
            $modeloRegistrar->codigoVehiculo = $condigoM;
            $modeloRegistrar->hora = $hora;
            $modeloRegistrar->wbeDestino = $cdcM;
            $modeloRegistrar->descripDestino = $req->Ubicacion;
            $modeloRegistrar->formula = $req->resistencia;
            $modeloRegistrar->cantidad = $req->volumen;
            $modeloRegistrar->firma = '--';
            $modeloRegistrar->observacion = $req->nota;
            $modeloRegistrar->fecha = $fechaSolicitud;
            $modeloRegistrar->cantiEnviada = $req->toneFaltante;
            $modeloRegistrar->fk_id_usuario = $this->traitGetIdUsuarioToken($req);
            $modeloRegistrar->turno = $req->turno;
            $modeloRegistrar->plantaDespacho = $req->plantaDespacho;
            $modeloRegistrar->save();
            $confirmationController = new SmsController();
            $id_usuarios = $solicitudConreto->fk_usuario;
            $mensaje = 'WEBU, Se despacho la cantidad de  '.$modeloRegistrar['cantiEnviada'].' m3 en la mixer '.$modeloRegistrar['codigoVehiculo'].' ( '.$modeloRegistrar['placaVehiculo'].' ) de su solicitud de concreto No '.$modeloRegistrar['fk_solicitud'].'.';
            $nota = 'Solicitud de concreto';
            $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
            $modeloRegistrar->refresh();
            $solicitudModificar = solicitudConcreto::find($req->id_solicitud);
            if ($suma >= $limiteEnviado) {
                $solicitudModificar->estado = 'ENVIADO';
                $confirmationController = new SmsController();
                //  $id_usuarios = $this->traitGetIdUsuarioToken($req);
                $mensaje = 'WEBU, La solicitud de concreto No. '.$modeloRegistrar['fk_solicitud'].' se ha despachado completamente .';
                $nota = 'Solicitud de concreto';
                $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                $resta = 0;
            } else {
                $resta = $solicitudConreto->volumen - $suma;
            }
            $solicitudModificar->toneFaltante = $resta;
            $solicitudModificar->save();
            try {
            } catch (\Exception $exc) {
            }

            return $this->handleResponse($req, [$modeloRegistrar->id_planilla], __('messages.planilla_de_control_concreto_registrado'));
        }
    }

    public function listarCdc(Request $request)
    {
        $consulta = PlanillaControlConcreto::select(
            'id_planilla',
            DB::raw('DATEADD(day,1, CONVERT(date,fecha)) AS FECHA'),
            DB::raw("CASE WHEN CONVERT(time,P.hora,3) > '07:00:00.0000000' THEN 'D' ELSE 'N' END AS 'DN'"),
            DB::raw("CASE WHEN D.Owned = 'O' THEN 'ARIGUANI' ELSE 'ALQUILER' END AS 'EMPRESA'"),
            DB::raw("'E'+SUBSTRING(EquipmentID,0,4) AS EQUIPO"),
            DB::raw("REPLACE(EquipmentID collate SQL_Latin1_General_CP1_CI_AS, '-', '') AS MAQUINA"),
            'placaVehiculo AS PLACA',
            DB::raw('SUBSTRING (CNF.COSYNCCODE ,0,5) AS CPLANTA'),
            'wbeDestino AS CDESTINO',
            'P.cantiEnviada AS M3',
            'F.formula AS FORMULA',
            'S.elementoVaciar AS OBSERVACIONES',
            DB::raw("ISNULL(CNF.COSYNCCODE,CNF1.COSYNCCODE) AS 'WBEORIGINE'"),
            'wbeDestino AS WBEDESTINO',
            DB::raw("ISNULL( pla1.NombrePlanta, '') AS 'DESCPLANTA'"),
            DB::raw("'HITO '+hito 'DESCDESTINO'")
        )->from('PlanillaControlConcreto as p')
            ->leftJoin('Sync_Vista_Equipos as D', 'D.EquipmentID', DB::raw(' P.codigoVehiculo collate SQL_Latin1_General_CP1_CI_AS'))
            ->leftJoin('SolicitudConcreto as S', 'S.id_solicitud', 'p.fk_solicitud')
            ->leftJoin('TipoMezcla as M', 'M.Tipo', 'S.tipoMezcla')
            ->leftJoin('Formula as F', 'F.fk_tipoMezcla', DB::raw('M.Id AND F.resistencia = S.resistencia'))
            ->leftJoin('usuPlanta as pla1', 'pla1.id_plata', 'P.plantaDespacho')
            ->leftJoin('CNFCOSTCENTER as CNF', 'CNF.COCEIDENTIFICATION', 'pla1.fk_id_centroCosto')
            ->leftJoin('usuPlanta as pla', 'pla.NombrePlanta', 's.PlantaDestino')
            ->leftJoin('CNFCOSTCENTER as CNF1', 'CNF1.COCEIDENTIFICATION', 'pla.fk_id_centroCosto')
            ->get();

        return $this->handleResponse($request, $consulta, 'COnsulñtado');
    }

    /**
     * @return void
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @return void
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
    }

    /**
     * Función que permite anular viajes.
     */
    public function anularViajes(Request $req, $id_planilla)
    {
        try {
            $fecha_actualizar = date('j/n/Y');
            if (!is_numeric($id_planilla)) {
                return $this->handleAlert(__('messages.validacion_numerico_solicitud'));
            }
            // Obtener el motivo del cuerpo de la solicitud

            $motivo = $req->input('motivo');
            if (empty($motivo)) {
                return $this->handleAlert(__('messages.ingrese_motivo'));
            }

            // Buscar la planilla por su ID
            $modeloModificar = PlanillaControlConcreto::find($id_planilla);

            if ($modeloModificar == null) {
                return $this->handleAlert(__('messages.solicitud_no_existe'));
            }

            // Verificar si el usuario tiene permiso para modificar esta solicitud
            $proyecto = $this->traitGetProyectoCabecera($req);
            $id_usuarios = $this->traitGetIdUsuarioToken($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert(__('messages.no_tiene_permiso'));
            }

            // Actualizar el motivo y el estado de la solicitud

            if ($modeloModificar) {
                $modeloModificar->motivo = $motivo;
                $modeloModificar->fk_id_usuario_actualizar = $id_usuarios;
                $modeloModificar->fecha_actualizar = $fecha_actualizar;
                $modeloModificar->estado = 0;
            }
            $modeloModificar->save();
            $confirmationController = new SmsController();
            $id_usuarios = $modeloModificar->fk_id_usuario;
            $mensaje = 'WEBU, El viaje No '.$modeloModificar['id_planilla'].' fue anulado '.$modeloModificar['codigoVehiculo'].' ('.$modeloModificar['placaVehiculo'].') de su solicitud de concreto No. '.$modeloModificar['fk_solicitud'].' por el siguiente motivo : '.$modeloModificar['motivo'].'.';
            $nota = 'Solicitud de concreto';
            $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);

            return $this->handleAlert(__('messages.viaje_anulado_correcto'), true);
        } catch (\Exception $e) {
            return $this->handleAlert(__('messages.error_anular_viaje', $e->getMessage()));
        }
    }
}
