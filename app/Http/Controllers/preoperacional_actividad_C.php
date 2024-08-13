<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use App\Http\interfaces\Vervos;
use App\Models\preoperacional_actividades_M;
use App\Models\preoperacional_M;
use App\Models\WbLiberacionesActividades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class preoperacional_actividad_C extends BaseController implements Vervos
{
    public function guardarDeprecated(Request $request)
    {
        //sleep(1);
        try {
            $datos = $request->all();

            $existencia = preoperacional_actividades_M::select('fk_id_preoperacional')
                ->where('fk_id_preoperacional', '=', $datos['FKIDPREOPERACIONAL'])
                ->where('fk_liberaciones_actividades', '=', $datos['FKACTIVIDAD'])
                ->get();

            if (!$existencia->isEmpty()) {
                return $this->handleAlert('ya existe actividad', 1);
            }


            $operacionales = new preoperacional_actividades_M;
            $operacionales->fk_id_preoperacional = $datos['FKIDPREOPERACIONAL'];
            $operacionales->fk_liberaciones_actividades = $datos['FKACTIVIDAD'];
            $operacionales->calificacion = $datos['CALIFICACION'];
            $operacionales->save();
            $data = preoperacional_actividades_M::latest('id_preoperacional_calificacion')->first();
            return $this->handleAlert('Actividad Registrada', $data->id_preoperacional_calificacion);
        } catch (\Throwable $th) {
            return $this->handleAlert('Error al Registrar actividad '.$th->getMessage(),0);
        }
    }
    public function guardar(Request $request)
    {
        //sleep(1);
        try {
            $datos = $request->all();

            $existencia = preoperacional_actividades_M::select('fk_id_preoperacional')
                ->where('fk_id_preoperacional', '=', $datos['FKIDPREOPERACIONAL'])
                ->where('fk_liberaciones_actividades', '=', $datos['FKACTIVIDAD'])
                ->get();

            if (!$existencia->isEmpty()) {
                return $this->handleAlert('ya existe actividad', 1);
            }

            $operacionales = new preoperacional_actividades_M;
            $operacionales->fk_id_preoperacional = $datos['FKIDPREOPERACIONAL'];
            $operacionales->fk_liberaciones_actividades = $datos['FKACTIVIDAD'];
            $operacionales->calificacion = $datos['CALIFICACION'];
            //$operacionales = $this->traitSetProyectoYCompania($request, $operacionales);
            $operacionales->save();
            $data = preoperacional_actividades_M::latest('id_preoperacional_calificacion')->first();
            return $this->handleResponse($request, $data->id_preoperacional_calificacion, 'Actividad Registrada');
        } catch (\Throwable $th) {
            return $this->handleAlert('Error al Registrar actividad', $th->getMessage());
        }
    }

    public function postDeprecated(Request $req)
    {
        try {
            //variable que va a guardar la respuesta
            $respuesta=collect();

            //variable que va a contener las actividades enviadas
            $calificaciones=$req["datos"];

            //recorrer los datos recibidos
            foreach ($calificaciones as $key => $calificacion) {

                //Se valida la informacion recibida
                $validator=Validator::make($calificacion,[
                    'PREOPERACIONAL'=>'required|string',
                    'ACTIVIDAD'=>'required|integer',
                    'CALIFICACION'=>'required|string',
                ]);

                $res=collect();
                $res->put('PREOPERACIONAL',$calificacion['PREOPERACIONAL']);
                $res->put('ACTIVIDAD',$calificacion['ACTIVIDAD']);

                if ($validator->fails()) {
                    $res->put('ESTADO',false);
                    $respuesta->push($res);
                    continue;
                }

                //valida que el reporte de preoperacional este creado
                if (preoperacional_M::where("id_preoperacional",$calificacion['PREOPERACIONAL'])->count()==0) {
                    $res->put('ESTADO',false);
                    $respuesta->push($res);
                    continue;
                }

                //valida que el id de la actividad sea valido
                if (WbLiberacionesActividades::where("id_liberaciones_actividades",$calificacion['ACTIVIDAD'])->where("estado","=","1")->count()==0) {
                    $res->put('ESTADO',false);
                    $respuesta->push($res);
                    continue;
                }

                //validamos si la actividad ya fue registrada anteriormente
                $existencia = preoperacional_actividades_M::select('fk_id_preoperacional')
                    ->where('fk_id_preoperacional', '=', $calificacion['PREOPERACIONAL'])
                    ->where('fk_liberaciones_actividades', '=', $calificacion['ACTIVIDAD'])
                    ->get();

                //si no existe insertamos el registro
                if($existencia->isEmpty()){
                    $operacionales= new preoperacional_actividades_M;
                    $operacionales->fk_id_preoperacional=$calificacion['PREOPERACIONAL'];
                    $operacionales->fk_liberaciones_actividades=$calificacion['ACTIVIDAD'];
                    $operacionales->calificacion=$calificacion['CALIFICACION'];
                    $operacionales->save();
                    //$data = preoperacional_actividades_M::latest('id_preoperacional_calificacion')->first();
                    //refrescamos la consulta para ver si se inserto
                    $existencia->fresh();
                }

                if(!$existencia->isEmpty()){
                    $res->put('ESTADO',true);
                    $respuesta->push($res);
                    continue;
                }else{
                    $res->put('ESTADO',false);
                    $respuesta->push($res);
                    continue;
                }


            }
            return $this->handleAlert($respuesta,true);
        } catch (\Throwable $th) {

            return $this->handleAlert('Error al Registrar las calificaciones de Preoperacional (004)'.' Error: '. $th);
        }
    }

    /**
     * @param Request $req
     * @return mixed
     */
    public function post(Request $req)
    {
        try {
        //variable que va a guardar la respuesta
        $respuesta=collect();

        //variable que va a contener las actividades enviadas
        $calificaciones=$req->json();

        //recorrer los datos recibidos
        foreach ($calificaciones as $key => $calificacion) {
            //Se valida la informacion recibida
            $validator=Validator::make($calificacion,[
                'PREOPERACIONAL'=>'required|string',
                'ACTIVIDAD'=>'required|integer',
                'CALIFICACION'=>'required|string',
            ]);

            $res=collect();
            $res->put('PREOPERACIONAL',$calificacion['PREOPERACIONAL']);
            $res->put('ACTIVIDAD',$calificacion['ACTIVIDAD']);

            if ($validator->fails()) {
                $res->put('ESTADO',false);
                 $respuesta->push($res);
                continue;
            }

            //valida que el reporte de preoperacional este creado
            $buscarReportePreoperacional = preoperacional_M::where("id_preoperacional",$calificacion['PREOPERACIONAL']);
            $buscarReportePreoperacional = $this->filtrar($req, $buscarReportePreoperacional);
            if ($buscarReportePreoperacional->count()==0) {
                $res->put('ESTADO',false);
                 $respuesta->push($res);
                continue;
            }

            //valida que el id de la actividad sea valido
            $buscarLiberacionActividad = WbLiberacionesActividades::where("id_liberaciones_actividades",$calificacion['ACTIVIDAD'])
                ->where("estado","=","1");
            $buscarLiberacionActividad = $this->filtrar($req, $buscarLiberacionActividad);
            if ($buscarLiberacionActividad->count()==0) {
                $res->put('ESTADO',false);
                 $respuesta->push($res);
                continue;
            }

            //validamos si la actividad ya fue registrada anteriormente
            $existencia = preoperacional_actividades_M::select('fk_id_preoperacional')
            ->where('fk_id_preoperacional', '=', $calificacion['PREOPERACIONAL'])
            ->where('fk_liberaciones_actividades', '=', $calificacion['ACTIVIDAD']);
            $existencia = $this->filtrar($req, $existencia)->get();

            //si no existe insertamos el registro
            if($existencia->isEmpty()){
                $operacionales= new preoperacional_actividades_M;
                $operacionales->fk_id_preoperacional=$calificacion['PREOPERACIONAL'];
                $operacionales->fk_liberaciones_actividades=$calificacion['ACTIVIDAD'];
                $operacionales->calificacion=$calificacion['CALIFICACION'];
                /**
                 * Agrego el proyecto y compañia que el usuario tiene selecionado actualmente
                 */
                $operacionales = $this->traitSetProyectoYCompania($req, $operacionales);
                $operacionales->save();
                  //$data = preoperacional_actividades_M::latest('id_preoperacional_calificacion')->first();
                //refrescamos la consulta para ver si se inserto
                $existencia->fresh();
                $res->put('ESTADO',true);
                $respuesta->push($res);
            } else {
                $res->put('ESTADO',true);
                $respuesta->push($res);
            }
        }
        return $this->handleAlert($respuesta,true);
        } catch (\Throwable $th) {
             return $this->handleAlert('Error al Registrar las calificaciones de Preoperacional (004)'.' Error: '. $th);
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
