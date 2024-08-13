<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\CostCode;
use App\Models\PlanillaControlAsfalto;
use App\Models\PlanillaControlConcreto;
use App\Models\ts_Equipement;
use App\Models\UsuPlanta;
use App\Models\WbSolitudAsfalto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanillaControlAsfaltoController extends BaseController implements Vervos
{
    public function getByUsuario(Request $req)
    {
        $usuario = $this->traitGetIdUsuarioToken($req);
        $consulta = PlanillaControlAsfalto::select(
            '*',
            'usuPlanta.NombrePlanta'
        )->leftjoin('usuPlanta', 'usuPlanta.id_plata', '=', 'PlanillaControlAsfalto.plantaDespacho')
            ->where('fk_id_usuario', $usuario);
        $consulta = $this->filtrar($req, $consulta, 'PlanillaControlAsfalto')->get();
        $ususPlanta = UsuPlanta::all();
        foreach ($consulta as $item) {
            $this->setUsuarioPlantaById($item, $ususPlanta);
        }

        return $this->handleResponse($req, $this->planillaControlAsfaltoToArray($consulta), __('messages.consultado'));
    }

    public function getBySolcitud(Request $req, $solicitud)
    {
        $consulta = PlanillaControlAsfalto::select('*')->where('fk_solicitud', $solicitud);
        $consulta = $this->filtrar($req, $consulta, 'PlanillaControlAsfalto')->get();
        $ususPlanta = UsuPlanta::all();
        foreach ($consulta as $item) {
            $this->setUsuarioPlantaById($item, $ususPlanta);
        }

        return $this->handleResponse($req, $this->planillaControlAsfaltoToArray($consulta), __('messages.consultado'));
    }

    public function listarCdc(Request $request, $anio, $mes)
    {
        $consulta = PlanillaControlConcreto::select(
            'id_planilla',
            'p.fecha as FECHA',
            'P.turno as DN',
            'sc.SubcontractorDesc as EMPRESA',
            DB::raw("case when ISNUMERIC( SUBSTRING(D.EquipmentID,0,2)) = 1 then 'E' else '' end + SUBSTRING(D.EquipmentID,0,4) as EQUIPO"),
            DB::raw('d.EquipmentID AS MAQUINA'),
            'placaVehiculo AS PLACA',
            DB::raw('SUBSTRING (CNF.COSYNCCODE ,0,5) AS CPLANTA'),
            'wbeDestino AS CDESTINO',
            DB::raw("case when (sr.id is not null or sr2.id is not null) then (CASE WHEN (sr.peso3 is not null) THEN 'Registro bascula' ELSE 'Registro bascula sin qr' END) else (case when (itp.peso is not null) then 'Registro bascula movil' else 'Registro no encontrado' end) end AS origen"),
            DB::raw('isnull(ISNULL((convert(float, sr.peso3)/1000), (convert(float, sr2.peso3)/1000)), isnull(itp.peso, 0)) AS TON_real'),
            'p.formula AS FORMULA',
            's.abscisas  AS OBSERVACIONES',
            'CNF.COSYNCCODE as wbeorigen',
        )
            ->from('PlanillaControlAsfalto as p')
            ->leftJoin('TimeScanSI.dbo.Equipments as D', 'D.EquipmentID', DB::raw('P.codigoVehiculo collate SQL_Latin1_General_CP1_CI_AS'))
            ->leftJoin('SolitudAsfalto as S', 'S.id_solicitudAsf', 'p.fk_solicitud')
            ->leftJoin('TimeScanSI.dbo.SubcontractorTrans as st', 'st.ContractID', 'D.ContractID')
            ->leftJoin('TimeScanSI.dbo.Subcontractor as sc', 'sc.SubcontractorID', 'st.SubContractorID')
            ->leftJoin('TipoMezcla as M', 'M.Tipo', 'S.tipoMezcla')
            ->leftJoin('Formula as F', 'F.fk_tipoMezcla', 'M.Id')
            ->leftJoin('usuPlanta as pla1', 'pla1.id_plata', 'P.plantaDespacho')
            ->leftJoin('sync_registros as sr', 'sr.baucher', 'p.codeqr')
            ->leftJoin('sync_registros as sr2', 'sr2.equipo', DB::raw('p.codigoVehiculo and DATEDIFF(minute, p.dateCreate, sr2.fechaSistema) BETWEEN 0 and 30'))
            ->leftJoin('TimeScanSI.dbo.ItensTransportPainel as itp', DB::raw('itp.EquipmentID collate SQL_Latin1_General_CP1_CI_AS'), DB::raw("p.codigoVehiculo and convert(date, p.dateCreate) = convert(date, itp.TranDate) and (DATEDIFF(minute, convert(time, p.dateCreate), convert(time,itp.ClockTime)) BETWEEN 0 and 30) and itp.StartEnd = 'S'"))
            ->leftJoin('CNFCOSTCENTER as CNF', 'CNF.COCEIDENTIFICATION', 'pla1.fk_id_centroCosto')
            ->where(DB::raw('MONTH(p.fecha)'), $mes)
            ->where(DB::raw('YEAR(p.fecha)'), $anio)
            ->orderBy('p.id_planilla', 'desc');
        $consulta = $consulta->get();

        return $this->handleResponse($request, $consulta, 'Consultado');
    }

    public function post(Request $req)
    {
        try {
            $fechaSolicitud = date('j/n/Y');
            $hora = date('g:i a');
            $cdcM = trim(strtoupper($req->cdc));
            $condigoM = strtoupper($req->codVehiculo);
            $equipoById = ts_Equipement::select(
                'EquipmentID',
                'SerialNumber'
            )->where('EquipmentID', $condigoM)
                ->where('Status', 'A')->first();
            $consultoCostCode = CostCode::select('CostCode')->where('CostCode', $cdcM)->first();
            if (!$consultoCostCode) {
                return $this->handleAlert('Cost code no valido.');
            }
            if (!$equipoById) {
                return $this->handleAlert('Equipo no encontrado.');
            }
            $solicitudAsfalto = WbSolitudAsfalto::find($req->solicitud);
            if (!$solicitudAsfalto) {
                return $this->handleAlert('Solicitud de asfalto no encontrada.');
            }
            $sumaPlanillas = PlanillaControlAsfalto::where('fk_solicitud', $req->solicitud)->sum('cantiEnviada');
            $suma = $sumaPlanillas + $req->toneFaltante;
            $limiteEnviado = $solicitudAsfalto->cantidadToneladas;
            $resta = $limiteEnviado - $suma;
            /*return $this->handleAlert([
                'suma' => $suma,
                'limite' => $limiteEnviado,
                'resta' => $resta,
            ]);*/
            if ($resta < 0) {
                return $this->handleAlert('Por favor solicite una cantidad menor, la cantidad actual mas la cantidad anteriormente excede la cantidad solicitada.');
            }
            if ($req->volumen <= 0) {
                return $this->handleAlert('La cantidad solicitada tiene que ser mayor a 0.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            $registroAnterior = PlanillaControlAsfalto::select()
                ->where('fk_solicitud', $req->solicitud)
                ->where('codigoVehiculo', $condigoM)
                ->where('fk_id_project_Company', $proyecto)
                ->whereRaw('DATEDIFF(MINUTE, GETDATE(), CONVERT(DATETIME, dateCreate)) >= -5')
                ->first();
            if ($registroAnterior != null) {
                return $this->handleResponse($req, $registroAnterior->id_planilla, 'Planilla control asfalto registrado.');
            } else {
                $modelo = new PlanillaControlAsfalto();
                $modelo->fk_solicitud = $req->solicitud;
                $modelo->placaVehiculo = $equipoById->SerialNumber;
                $modelo->codigoVehiculo = $condigoM;
                $modelo->hora = $hora;
                $modelo->wbeDestino = $cdcM;
                $modelo->descripDestino = $req->ubicacion;
                $modelo->formula = $req->resistencia;
                $modelo->cantidad = $req->volumen;
                $modelo->firma = '--';
                $modelo->observacion = 'Enviado';
                $modelo->fecha = $fechaSolicitud;
                $modelo->fk_id_usuario = $this->traitGetIdUsuarioToken($req);
                $modelo->cantiEnviada = $req->toneFaltante;
                $modelo->turno = $req->turno;
                $modelo->plantaDespacho = $req->plantaDespacho;
                $modelo->codeqr = $req->codeqr;
                $modelo->base64 = $req->base64;
                $modelo->estado = 1;

                if ($modelo->save()) {
                    $confirmationController = new SmsController();
                    $id_usuarios = $solicitudAsfalto->fk_id_usuario;
                    $mensaje = 'WEBU, se despacho la cantidad de '.$modelo['cantiEnviada'].' Ton en la volqueta '.$modelo['codigoVehiculo'].' ( '.$modelo['placaVehiculo'].' ) de su solicitud de asfalto No '.$modelo['fk_solicitud'].'.';
                    $nota = 'Solicitud de asfalto';
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                    $modelo->id_planilla = PlanillaControlAsfalto::latest('id_planilla')->first()->id_planilla;
                    $modelo->id_planilla = PlanillaControlAsfalto::latest('id_planilla')->first()->id_planilla;
                    $modelo = PlanillaControlAsfalto::find($modelo->id_planilla);
                    $solicitudModificar = WbSolitudAsfalto::find($modelo->fk_solicitud);
                    if ($resta == 0) {
                        $confirmationController = new SmsController();
                        $id_usuarios = $solicitudAsfalto->fk_id_usuario;
                        $mensaje = 'WEBU, La solicitud de asfalto No. '.$modelo['fk_solicitud'].' se ha despachado completamente.';
                        $nota = 'Solicitud de asfalto';
                        $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                        $solicitudModificar->estado = 'ENVIADO';
                    }
                    $solicitudModificar->toneFaltante = $resta;
                    $solicitudModificar->save();

                    return $this->handleResponse($req, $modelo->id_planilla, 'Planilla control asfalto registrado.');
                } else {
                    return $this->handleAlert('Planilla de control asfalto no registrado.');
                }
            }
        } catch (\Exception $exc) {
            Log::info($exc);
        }

        return $this->handleAlert('Planilla de control asfalto no registrado.');
    }

    public function listarCdc2(Request $request)
    {
        $consulta = PlanillaControlConcreto::select(
            'id_planilla',
            DB::raw('DATEADD(day,1, CONVERT(date,fecha)) AS FECHA'),
            DB::raw("CASE WHEN CONVERT(time,P.hora,3) > '07:00:00.0000000' THEN 'D' ELSE 'N' END AS 'D/N'"),
            DB::raw("CASE WHEN D.Owned = 'O' THEN 'ARIGUANI' ELSE 'ALQUILER' END AS 'EMPRESA'"),
            DB::raw("'E'+SUBSTRING(EquipmentID,0,4) AS EQUIPO"),
            DB::raw("REPLACE(EquipmentID collate SQL_Latin1_General_CP1_CI_AS, '-', '') AS MAQUINA"),
            'placaVehiculo AS PLACA',
            DB::raw('SUBSTRING (CNF.COSYNCCODE ,0,5) AS C. PLANTA'),
            'wbeDestino AS C DESTINO',
            'P.cantiEnviada AS TON',
            'F.formula AS FORMULA',
            'S.observaciones AS OBSERVACIONES',
        )->from('PlanillaControlAsfalto as p')
            ->leftJoin('Sync_Vista_Equipos as D', 'D.EquipmentID', DB::raw(' P.codigoVehiculo collate SQL_Latin1_General_CP1_CI_AS'))
            ->leftJoin('SolicitudConcreto as S', 'S.id_solicitudAsf', 'p.fk_solicitud')
            ->leftJoin('TipoMezcla as M', 'M.Tipo', 'S.tipoMezcla')
            ->leftJoin('Formula as F', 'F.fk_tipoMezcla', DB::raw('M.Id'))
            ->leftJoin('usuPlanta as pla1', 'pla1.id_plata', 'P.plantaDespacho')
            ->leftJoin('CNFCOSTCENTER as CNF', 'CNF.COCEIDENTIFICATION', 'pla1.fk_id_centroCosto')
            ->get();

        return $this->handleResponse($request, $consulta, 'Consultado');
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function get(Request $request)
    {
        // TODO: Implement get() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    private function setUsuarioPlantaById($estructura, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($estructura->plantaDespacho == $array[$i]->id_plata) {
                $reescribir = $this->usuPlantaToModel($array[$i]);
                $estructura->objectUsuPlanta = $reescribir;
                break;
            }
        }
    }

    public function getByPlanta(Request $request)
    {
        $usuario = $this->traitGetIdUsuarioToken($request);
        if (!is_numeric($request->page) || !is_numeric($request->limit) || ($request->solicitud && !is_numeric($request->solicitud))) {
            return $this->handleAlert('Datos invalidos');
        }

        $contador = PlanillaControlAsfalto::select(
            'id_planilla',
        )->leftjoin('usuPlanta', 'usuPlanta.id_plata', '=', 'PlanillaControlAsfalto.plantaDespacho');

        $consulta = PlanillaControlAsfalto::select(
            '*',
            'NombrePlanta'
        )->leftjoin('usuPlanta', 'usuPlanta.id_plata', '=', 'PlanillaControlAsfalto.plantaDespacho')
            ->where('fk_id_usuario', $usuario);
        if ($request->solicitud) {
            $consulta->where('fk_solicitud', $request->solicitud);
        }
        if ($request->fecha) {
            $consulta->where('fecha', $request->fecha);
        }
        if ($request->plantaSelecionada) {
            $consulta->where('plantaDespacho', $request->plantaSelecionada);
        }
        $consulta = $this->filtrar($request, $consulta, 'PlanillaControlAsfalto')->orderby('id_planilla', 'DESC');
        $contador = $this->filtrar($request, $contador, 'PlanillaControlAsfalto')->get();
        $contador = clone $consulta;
        $rows = $contador->count();
        $limitePaginas = ($rows / $request->limit) + 1;
        $consulta = $consulta->forPage($request->page, $request->limit)->get();

        return $this->handleResponse($request, $this->planillaControlAsfaltoToArray2($consulta), __('messages.consultado'), $limitePaginas);
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
            $modeloModificar = PlanillaControlAsfalto::find($id_planilla);

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
                $modeloModificar->fk_user_update = $id_usuarios;
                $modeloModificar->fecha_update = $fecha_actualizar;
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
