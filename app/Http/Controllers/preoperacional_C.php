<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use App\Http\interfaces\Vervos;
use App\Models\preoperacional_M;
use App\Models\ts_Equipement;
use App\Models\Usuarios\usuarios_M;
use App\Models\WbEquipo;
use App\Models\WbLiberacionesFormato;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class preoperacional_C extends BaseController implements Vervos
{

    public function guardarDeprecated(Request $request)
    {

        try {
            $datos = $request->all();

            $existencia = preoperacional_M::select('id_preoperacional')
                ->where('id_preoperacional', '=', $datos['IDPREOPERACIONAL'])
                ->get();
            if (!$existencia->isEmpty()) {
                return $this->handleAlert('ya existe preoperacional', 1);
            }


            $equipo = ts_Equipement::find($datos['EQUIPAMENT']);
            if (is_null($equipo)) {
                return $this->handleAlert('No Existe Este Equipo');
            }
            $operacionales = new preoperacional_M;
            $operacionales->id_preoperacional = $datos['IDPREOPERACIONAL'];
            $operacionales->fk_equipamentID = $datos['EQUIPAMENT'];
            $operacionales->tipoVehiculo = $datos['TIPOVEHICULO'];
            $operacionales->fecha = $datos['FECHA'];
            $operacionales->turno = $datos['TURNO'];
            $operacionales->horometro = $datos['HOROMETRO'];
            $operacionales->odometro = $datos['ODOMETRO'];
            $operacionales->preoperacional = $datos['PREOPERACIONAL'];
            $operacionales->observacion = $datos['OBSERVACION'];
            $operacionales->fk_id_usuario = $datos['USUARIO'];
            $operacionales->estado = $datos['ESTADO'];
            $operacionales->operativo = $datos['OPERATIVIDAD'];
            $operacionales->save();
            $data = preoperacional_M::latest('preoperacional_auto')->first();
            return $this->handleAlert('Preoperacional Registrado', $data->preoperacional_auto);

        } catch (\Throwable $th) {
            $datos = $request->all();
            Log::error('usuario: ' . $datos['USUARIO'] . ' preoperacional: ' . $datos['IDPREOPERACIONAL'] . ' Error: ' . $th);
            return $this->handleAlert('Error al Registrar Preoperacional', 0);
        }
    }

    public function guardar(Request $request)
    {

        try {
            $datos = $request->all();

            $existencia = preoperacional_M::select('id_preoperacional')
                ->where('id_preoperacional', '=', $datos['IDPREOPERACIONAL'])
                ->get();
            if (!$existencia->isEmpty()) {
                return $this->handleAlert('ya existe preoperacional', 1);
            }


            $equipo = ts_Equipement::find($datos['EQUIPAMENT']);
            if (is_null($equipo)) {
                return $this->handleAlert('No Existe Este Equipo');
            }
            $operacionales = new preoperacional_M;
            $operacionales->id_preoperacional = $datos['IDPREOPERACIONAL'];
            $operacionales->fk_equipamentID = $datos['EQUIPAMENT'];
            $operacionales->tipoVehiculo = $datos['TIPOVEHICULO'];
            $operacionales->fecha = $datos['FECHA'];
            $operacionales->turno = $datos['TURNO'];
            $operacionales->horometro = $datos['HOROMETRO'];
            $operacionales->odometro = $datos['ODOMETRO'];
            $operacionales->preoperacional = $datos['PREOPERACIONAL'];
            $operacionales->observacion = $datos['OBSERVACION'];
            $operacionales->fk_id_usuario = $datos['USUARIO'];
            $operacionales->estado = $datos['ESTADO'];
            $operacionales->operativo = $datos['OPERATIVIDAD'];
            //$operacionales = $this->traitSetProyectoYCompania($request, $operacionales);
            $operacionales->save();
            $data = preoperacional_M::latest('preoperacional_auto')->first();
            return $this->handleAlert('Preoperacional Registrado', $data->preoperacional_auto);

        } catch (\Throwable $th) {
            $datos = $request->all();
            Log::error('usuario: ' . $datos['USUARIO'] . ' preoperacional: ' . $datos['IDPREOPERACIONAL'] . ' Error: ' . $th);
            return $this->handleAlert('Error al Registrar Preoperacional', 0);
        }
    }

    public function postDeprecated(Request $req)
    {
        try {
            //tomamos los datos enviados desde la app
            $datos=$req->all();
            //realizamos las validaciones correspondientes para comprobar que los datos llegaron correctamente
            $validator=Validator::make($datos,[
                'IDPREOPERACIONAL'=>'required',
                'EQUIPAMENT'=>'required',
                'TIPOVEHICULO'=>'present',
                'FECHA'=>'required|date',
                'TURNO'=>'required|string',
                'HOROMETRO'=>'nullable|numeric|present',
                'ODOMETRO'=>'nullable|numeric|present',
                'PREOPERACIONAL'=>'required|string',
                'OBSERVACION'=>'nullable|present|string',
                'USUARIO'=>'required|numeric',
                'ESTADO'=>'required|integer',
                'OPERATIVIDAD'=>'required|string'

            ]);
            //validamos si presenta algun error de validacion de dartos
            if ($validator->fails()) {
                // return $this->handleAlert("No fue posible sincronizar el preoperacional (001)",false);
                return $this->handleAlert($validator->errors());
            }

            //comprobamos que los datos relacionales enviados sean correctos
            //verificamos que el equipo existe
            if (ts_Equipement::find($datos['EQUIPAMENT'])->count()==0) {
                return $this->handleAlert("No fue posible sincronizar el preoperacional (002)");
            }
            //verificamos que el usuario existe
            if (usuarios_M::find($datos['USUARIO'])->count()==0) {
                return $this->handleAlert("No fue posible sincronizar el preoperacional (003)");
            }

            //comprobamos si el preoperacional existe si no lo creamos
            $preoperacional= preoperacional_M::firstOrCreate(
                ['id_preoperacional'=>$datos['IDPREOPERACIONAL']],
                ['fk_equipamentID'=>$datos['EQUIPAMENT'],
                    'tipoVehiculo'=>$datos['TIPOVEHICULO'],
                    'fecha'=>$datos['FECHA'],
                    'turno'=>$datos['TURNO'],
                    'horometro'=>$datos['HOROMETRO'],
                    'odometro'=>$datos['ODOMETRO'],
                    'preoperacional'=>$datos['PREOPERACIONAL'],
                    'observacion'=>$datos['OBSERVACION'],
                    'fk_id_usuario'=>$datos['USUARIO'],
                    'estado'=>$datos['ESTADO'],
                    'operativo'=>$datos['OPERATIVIDAD']
                ]);
            $preoperacional = preoperacional_M::where('id_preoperacional', $datos['IDPREOPERACIONAL'])
                ->where('fk_equipamentID', $datos['EQUIPAMENT'])
                ->where('tipoVehiculo', $datos['TIPOVEHICULO'])
                ->where('fecha', $datos['FECHA'])
                ->where('turno', $datos['TURNO'])
                ->where('horometro', $datos['HOROMETRO'])
                ->where('odometro', $datos['ODOMETRO'])
                ->where('preoperacional', $datos['PREOPERACIONAL'])
                ->where('observacion', $datos['OBSERVACION'])
                ->where('fk_id_usuario', $datos['USUARIO'])
                ->where('estado', $datos['ESTADO'])
                ->where('operativo', $datos['OPERATIVIDAD'])
                ->first();
            if ($preoperacional->preoperacional_auto>0) {
                return $this->handleAlert('Preoperacional Registrado '.$preoperacional->preoperacional_auto,true);

            }else{
                return $this->handleAlert('Error');

            }


        } catch (\Throwable $th) {
            $datos=$req->all();
            Log::error('usuario: '.$datos['USUARIO'].' preoperacional: '.$datos['IDPREOPERACIONAL'].' Error: '. $th);
            return $this->handleAlert('Error al Registrar Preoperacional (004)');
        }
        return $this->handleAlert($mensaje,true);
    }

    /**
     * @param Request $req
     * @return mixed
     */
    public function post(Request $req)
    {
        try {
            //tomamos los datos enviados desde la app
            $datos=$req->all();
            //realizamos las validaciones correspondientes para comprobar que los datos llegaron correctamente
            $validator=Validator::make($datos,[
                'IDPREOPERACIONAL'=>'required',
                'EQUIPAMENT'=>'required',
                'TIPOVEHICULO'=>'present',
                'FECHA'=>'required|date',
                'TURNO'=>'required|string',
                'HOROMETRO'=>'nullable|numeric|present',
                'ODOMETRO'=>'nullable|numeric|present',
                'PREOPERACIONAL'=>'required|string',
                'OBSERVACION'=>'nullable|present|string',
                'ESTADO'=>'required|integer',
                'OPERATIVIDAD'=>'required|string',
                'IDFORMATO'=>'required|numeric',

            ]);
            //validamos si presenta algun error de validacion de dartos
            if ($validator->fails()) {
                // return $this->handleAlert("No fue posible sincronizar el preoperacional (001)",false);
                return $this->handleAlert($validator->errors());
            }
            if (!(strcmp($req->TURNO, 'Turno Nocturno') == 0 || strcmp($req->TURNO, 'Turno Diurno') == 0)) {
                return $this->handleCod('Turno no valido', 'PRE01');
            }

            //comprobamos que los datos relacionales enviados sean correctos
            //verificamos que el equipo existe
            if (WbEquipo::where('equiment_id', $datos['EQUIPAMENT'])->first() == null) {
                return $this->handleCod("No fue posible sincronizar el preoperacional", 'PRE02');
            }
            if (WbLiberacionesFormato::where('id_liberaciones_formatos', $req->IDFORMATO)->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))->first() == null) {
                return $this->handleCod("No fue posible sincronizar el preoperacional", 'PRE03');
            }
            $setproyecto = $this->traitSetProyectoYCompania($req, new preoperacional_M);
            //comprobamos si el preoperacional existe si no lo creamos
            preoperacional_M::firstOrCreate(
                                ['id_preoperacional'=>$datos['IDPREOPERACIONAL']],
                                ['fk_equipamentID'=>$datos['EQUIPAMENT'],
                                'tipoVehiculo'=>$datos['TIPOVEHICULO'],
                                'fecha'=>$datos['FECHA'],
                                'turno'=>$datos['TURNO'],
                                'horometro'=>$datos['HOROMETRO'],
                                'odometro'=>$datos['ODOMETRO'],
                                'preoperacional'=>$datos['PREOPERACIONAL'],
                                'observacion'=>$datos['OBSERVACION'],
                                'fk_id_usuario'=>$this->traitGetIdUsuarioToken($req),
                                'estado'=>$datos['ESTADO'],
                                'fk_id_liberaciones_formatos'=>$datos['IDFORMATO'],
                                'operativo'=>$datos['OPERATIVIDAD'],
                                'fk_id_project_Company'=>$setproyecto->fk_id_project_Company,
                                'fk_compañia'=>$setproyecto->fk_compañia,
                                ]);
            $preoperacional = preoperacional_M::where('id_preoperacional', $datos['IDPREOPERACIONAL'])
                ->where('fk_equipamentID', $datos['EQUIPAMENT'])
                ->where('tipoVehiculo', $datos['TIPOVEHICULO'])
                ->where('turno', $datos['TURNO'])
                ->where('horometro', $datos['HOROMETRO'])
                ->where('odometro', $datos['ODOMETRO'])
                ->where('preoperacional', $datos['PREOPERACIONAL'])
                ->where('observacion', $datos['OBSERVACION'])
                ->where('fk_id_usuario', $this->traitGetIdUsuarioToken($req))
                ->where('estado', $datos['ESTADO'])
                ->where('operativo', $datos['OPERATIVIDAD'])
                ->where('fk_id_liberaciones_formatos', $datos['IDFORMATO'])
                ->where('fk_id_project_Company', $setproyecto->fk_id_project_Company)
                ->where('fk_compañia', $setproyecto->fk_compañia)
                ->first();
            if ($preoperacional->preoperacional_auto>0) {
                 return $this->handleResponse($req, [], 'Preoperacional Registrado '.$preoperacional->preoperacional_auto);
            }else{
                 return $this->handleCod('Error', '000');
            }
         } catch (\Throwable $th) {
            $datos=$req->all();
            Log::error('usuario: '.$this->traitGetIdUsuarioToken($req).' preoperacional: '.$datos['IDPREOPERACIONAL'].' Error: '. $th);
             return $this->handleCod('Error al Registrar Preoperacional', '000');
         }
    }

    /**
     * @param Request $req
     * @param $id
     * @return mixed
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return mixed
     */
    public function get(Request $request)
    {
        // TODO: Implement get() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
