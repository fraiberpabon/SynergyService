<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use App\Http\Resources\EquipementsCollection;
use App\Models\Sync_VehiculosPesos;
use App\Models\ts_Equipement;
use App\Models\WbEquipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @deprecated migrar a WbEquipos
 */
class EquipementsController extends BaseController
{
    /*
    *
    BUSCA EL LISTADO DE VEHICULOS SEGUN LOS PARAMETOS ENVIADOS (CODIGO DE EQUIPO, PLACA O CONTRATISTA)
    *
    */
    public function ListarEquipos(Request $request, $n_equipos, $equipo = '')
    {

        try {

            //Consulto el listado de equipos
            $consulta = ts_Equipement::select(
                'Equipments.EquipmentID',
                'Equipments.Description',
                'Equipments.Make',
                'Equipments.Model',
                'Equipments.SerialNumber',
                'Equipments.Owned',
                'Equipments.Status',
                'Equipments.Payload',
                'SubcontractorDesc'
            )
                ->leftjoin('SubcontractorTrans', 'Equipments.ContractID', '=', 'SubcontractorTrans.ContractID')
                ->leftjoin('Subcontractor', 'SubcontractorTrans.SubcontractorID', '=', 'Subcontractor.SubcontractorID')
                ->where('Equipments.EquipmentID', 'like', '%' . $equipo . '%')
                ->orWhere('Equipments.SerialNumber', 'like', '%' . $equipo . '%')
                ->orWhere('Subcontractor.SubcontractorDesc', 'like', '%' . $equipo . '%')
                ->take($n_equipos)
                ->orderBy('Equipments.EquipmentID', 'asc');
            $limitePaginas = 1;
            if ($request->query('page') && is_numeric($request->page) && $request->query('limit') && is_numeric($request->limit)) {
                $contador = clone $consulta;
                $contador = $contador->select('Equipments.EquipmentID')->get();
                $consulta = $consulta->forPage($request->page, $request->limit)->get();
                $limitePaginas = ($contador->count() / $request->limit) + 1;
            } else {
                $consulta = $this->filtrar($request, $consulta, 'Equipments')->get();
            }
            if ($consulta->count() > 0) {
                // recorro uno por uno en busca del peso
                foreach ($consulta as $equipo) {
                    //busco el peso
                    $peso = Sync_VehiculosPesos::select('peso')->find($equipo->EquipmentID);
                    //compruebo si viene vacio
                    if (!empty($peso)) {
                        //si no esta vacio lo inserto al existente
                        $equipo1 = array_merge($equipo->toArray(), $peso->toArray());
                    } else {
                        //solo convierto a array el existente
                        $equipo1 = $equipo->toArray();
                    }
                    //guardo el valor en una nueva variable
                    $Vehiculos[] = $equipo1;
                }
                //se retorna la informacion en caso de que sea correcta
                return $this->handleResponse($request, new EquipementsCollection($Vehiculos), 'Consulta exitosa', $limitePaginas);
            } else {
                //se retorna un mensaje en caso de que no pueda encontrar ningun dato
                return $this->handleAlert('No se encontraron datos que coincidan con los criterios');

            }
        } catch (Exception $e) {
            //devuelve error si por algun caso no puede consultar la base de datos
            return $this->handleError('Error al consultar la base de datos', $e->getMessage());
        }


    }


    /*
     * CAMBIAR ESTADO DE UN VEHICULO
     */
    public function CambiarEstado($equipo)
    {
        $equipos = ts_Equipement::find($equipo);
        $WBequipos = WbEquipo::where('equiment_id', $equipo)->first();
        switch ($equipos->Status) {
            case 'A':
                $equipos->Status = 'I';
                $WBequipos->estado = 'I';
                $WBequipos->save();
                $equipos->save();

                $equipos->refresh();
                $mensaje = 'El equipo ' . $equipo . ' a sido inactivado';
                break;

            case 'I':
                $equipos->Status = 'A';
                $WBequipos->estado = 'A';
                $WBequipos->save();
                $equipos->save();
                $equipos->refresh();
                $mensaje = 'El equipo ' . $equipo . ' a sido activado';
                break;
        }

        return $this->handleAlert($mensaje, true);
    }

    public function CrearEquipo(Request $request)
    {
        try {

            //se valida la informacion recibida
            $validator = Validator::make($request->all(), [
                'CODIGO' => 'required|regex:/^[A-Za-z0-9]{2,3}+[Z]?[.]+[A-Za-z0-9]{2,3}$/|between:6,7|unique:App\Models\ts_Equipement,EquipmentID',
                'MARCA' => 'required|min:1',
                'MODELO' => 'present',
                'PLACA' => 'present',
                'CUBICAJE' => 'present|numeric',
                'TIPO' => 'required|size:1|regex:/[O,R]/',
                'CONTRATISTAID' => 'required|max:20',
                'TIPO_CONTRATO' => 'required',
                'DESCRIPCION' => 'required|max:50',
                'OBSERVACION' => 'present'
            ]);
            //si no cumple validaciones imprime error
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }
            //se guardan lo datos
            $datos = $request->all();
            //inicia la instancia de contratista
            $contratista = new ContratistaController();

            //se verifica que el contratista exista
            $contratistaresult = $contratista->contratista($datos['CONTRATISTAID']);

            if (!$contratistaresult) {
                return $this->handleAlert('El contratista no existe en la base de datos');
            }

            //se verifica que el contrato exista
            //valida si el contrato existe
            $contrato = $contratista->Contrato($contratistaresult->SubcontractorID, $datos['TIPO_CONTRATO']);

            //se define el grupo al cual pertenece el equipo
            if (is_numeric(substr($datos['CODIGO'], 0, 1))) {
                //si inicia por numero se le agrega una E al final
                $a = 'E' . substr($datos['CODIGO'], 0, 3);
            } else {
                //si inicia por letra se toma las primeras letras del codigo
                $a = substr($datos['CODIGO'], 0, strpos($datos['CODIGO'], '.'));
            }

            //se consulta la base de datos
            $equipo = new ts_Equipement;
            $equipo->EquipmentID = $datos['CODIGO'];
            $equipo->Make = $datos['MARCA'];
            $equipo->Model = $datos['MODELO'];
            $equipo->SerialNumber = $datos['PLACA'];
            $equipo->Payload = $datos['CUBICAJE'];
            $equipo->Owned = $datos['TIPO'];
            $equipo->Description = $datos['DESCRIPCION'];
            $equipo->Comments = $datos['OBSERVACION'];
            $equipo->ContractID = $contrato->ContractID;
            $equipo->ModelNumber = $a;
            $equipo->save();


            return $this->handleAlert('El equipo ' . $datos['CODIGO'] . ' ha sido insertado con exito', true);
        } catch (Exception $e) {
            return $this->handleError('Error al consultar la insertar', $e->getMessage());
        }
    }

    //listamos equipos en general pero activos
    public function equiposActivos(Request $request)
    {
        $consulta = ts_Equipement::select(
            'EquipmentID',
            'Description',
            'Make',
            'Model'
            ,
            'Owned'
            ,
            'Status'
            ,
            'SerialNumber'
        )
            ->where('Status', '!=', 'I');
        //$consulta = $this->filtrar($request, $consulta)->get();
        return json_encode(new EquipementsCollection($consulta->get()));
    }

    public function get(Request $request)
    {
        $consulta = ts_Equipement::select(
            'Equipments.EquipmentID',
            'Equipments.Description',
            'Equipments.Make',
            'Equipments.Model',
            'Equipments.SerialNumber',
            'Equipments.Owned',
            'Equipments.Status',
            'Equipments.Payload',
            'SubcontractorDesc',
        )
            ->leftjoin('SubcontractorTrans', 'Equipments.ContractID', '=', 'SubcontractorTrans.ContractID')
            ->leftjoin('Subcontractor', 'SubcontractorTrans.SubcontractorID', '=', 'Subcontractor.SubcontractorID');
        if ($request->has('id') && strlen($request->id) > 0) {
            $consulta = $consulta->where('EquipmentID', $request->id);
        }
        if ($request->has('estado') && strlen($request->estado) > 0) {
            $consulta = $consulta->where('Status', 'A');
        }
        $limitePaginas = 1;
        if ($request->query('page') && is_numeric($request->page) && $request->query('limit') && is_numeric($request->limit)) {
            $contador = clone $consulta;
            $contador = $contador->select('Equipments.EquipmentID')->get();
            $consulta = $consulta->forPage($request->page, $request->limit)->get();
            $limitePaginas = ($contador->count() / $request->limit) + 1;
        } else {
            $consulta = $this->filtrar($request, $consulta, 'Equipments')->get();
        }
        return $this->handleResponse($request, $this->equimentToArray($consulta), __("messages.consultado"), $limitePaginas);
    }

    public function equiposParaViajeBascula(Request $request, $equipo)
    {
        if (!($request->query('page') && is_numeric($request->page) && $request->query('limit') && is_numeric($request->limit))) {
            return $this->handleAlert('Faltan parametros.');
        }
        $baseDatos = Db::connection('sqlsrv2')->getDatabaseName() . '.dbo.';
        $consulta = ts_Equipement::select(
            'Equipments.EquipmentID as EQUIPO',
            'Equipments.Description as DESCRIPCION',
            'Equipments.Make as MARCA',
            'Equipments.Model as MODELO',
            'Equipments.SerialNumber as SERIAL',
            'Equipments.Owned as TIPO',
            'Equipments.Status as ESTADO',
            'Equipments.Payload as M3',
            'sc.SubcontractorDesc as CONTRATO'
        )
            ->selectRaw("(SELECT COUNT(*) FROM {$baseDatos}sync_registros WHERE equipo=Equipments.EquipmentID collate SQL_Latin1_General_CP1_CI_AS) as CANTIDAD")
            ->selectRaw("(SELECT top 1 PESO FROM {$baseDatos}sync_relacion_VehiculosPesos WHERE vehiculo = Equipments.EquipmentID collate SQL_Latin1_General_CP1_CI_AS) as PESO")
            ->leftjoin('TimeScanSI.dbo.SubcontractorTrans as sct', 'Equipments.ContractID', '=', 'sct.ContractID')
            ->leftjoin('TimeScanSI.dbo.Subcontractor as sc', 'sc.SubcontractorID', 'sct.SubContractorID');
        if ($equipo != 0) {
            $consulta = $consulta->where(function ($query) use ($equipo) {
                $query->where('sc.SubcontractorDesc', 'like', DB::raw("'%$equipo%'"))
                    ->orWhere('Equipments.SerialNumber', 'like', DB::raw("'%$equipo%'"))
                    ->orWhere('Equipments.EquipmentID', 'like', DB::raw("'%$equipo%'"));
            });
        }
        if ($request->estado) {
            $consulta = $consulta->where('Equipments.Status', $request->estado);
        }
        $consulta = $consulta->orderBy('Equipments.EquipmentID', 'asc');
        $contador = clone $consulta;
        $contador = $contador->select('Equipments.EquipmentID')->get();
        $consulta = $consulta->forPage($request->page, $request->limit)->get();
        $limitePaginas = ($contador->count() / $request->limit) + 1;
        return $this->handleResponse($request, $consulta, __("messages.consultado"), $limitePaginas);
    }

    public function validarEquimentId(Request $request, $id)
    {
        $consulta = ts_Equipement::where('EquipmentID', $id)->where('Status', 'A')->first();
        if ($consulta != null) {
            return $this->handleResponse($request, ['placa' => $consulta->SerialNumber, 'descripcion' => $consulta->Description], __("messages.consultado"), $consulta != null);
        } else {
            return $this->handleAlert('Equipo no encontrado');
        }
    }



}


