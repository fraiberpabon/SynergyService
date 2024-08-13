<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController as BaseController;
use App\Http\interfaces\Vervos;
use App\Http\trait\Resource;
use App\Models\WbReporteInspeccionCalidadCalifi;
use App\Models\WbReporteInspeccionCalidad;
use App\Models\WbLiberacionesActividades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WbReporteInspeccionCalidadCalifiController extends BaseController implements Vervos
{
    use Resource;
    public function guardar( Request $request){
        try {
             $datos=$request->all();
            $actividades= new WbReporteInspeccionCalidadCalifi;
                     $actividades->fk_reporte_inspeccion_calidad=$datos['FKREPORTE'];
                     $actividades->fk_liberaciones_actividades=$datos['FKACTIVIDAD'];
                     $actividades->calificacion=$datos['CALIFICACION'] ;
                     $actividades = $this->traitSetProyectoYCompania($request, $actividades);
                     $actividades->save();
                     return $this->handleAlert( 1,true);

         } catch (\Throwable $th) {
             return $this->handleError('Error',$th->getMessage());
         }
    }

    public function postDeprecated(Request $calificar)
    {

        //se crea la variable que va a guardar la respuesta
        $respuesta=collect();

        //return $calificar[0];
        $prueba=$calificar["datos"];

        //recorrer los datos recibidos
        foreach ($prueba as $key => $req) {

            //convierte los datos en una colleccion
            //$req=json_encode($req);


            //se valida la informacion recibida
            $validator = Validator::make($req, [
                'REPORTEID' => 'required|numeric',
                'ACTIVIDAD' => 'required|numeric',
                'CALIFICACION' => 'required|string',
                'FECHA' => 'required|date',
            ]);

            //extrae datos
            $datos=$req;

            //inicializamos la variable que guarda la
            $rep=collect();
            $rep->put('REPORTEID',$datos['REPORTEID']);
            $rep->put('ACTIVIDAD',$datos['ACTIVIDAD']);

            //si no cumple validaciones imprime error
            if(!$validator->fails()){

                //inicializamos la variable de validacion
                $cumple=true;

                //valida que el reporte de inspeccion este creado
                if (WbReporteInspeccionCalidad::where("id_reporte_inspeccion_calidad",$datos['REPORTEID'])->count()==0) {
                    $cumple=false;
                }
                //valida que el id de la actividad sea valido
                if (WbLiberacionesActividades::where("id_liberaciones_actividades",$datos['ACTIVIDAD'])->where("estado","=","1")->count()==0) {
                    $cumple=false;
                }

                //si cumple todos los requerimientos inserta la informacion
                if ($cumple) {
                    //valida que el dato existe en caso contrario lo inserta
                    $calificacion= WbReporteInspeccionCalidadCalifi::firstOrCreate(
                        ['fk_reporte_inspeccion_calidad'=>$datos['REPORTEID']
                            ,'fk_liberaciones_actividades'=>$datos['ACTIVIDAD']],
                        ['calificacion'=>$datos['CALIFICACION'],
                            'dateCreate'=>date('d-m-Y H:i:s',strtotime($datos['FECHA']))
                        ]);
                }

                $rep->put('ESTADO',$cumple);

            }else{
                $rep->put('ESTADO',false);
            }
            $respuesta->push($rep);
            //return $this->handleAlert($respuesta,true);
            /* if($validator->fails()){
                 //return $this->handleAlert("Algunos datos no pudieron ser sincronizados (001)",false);
                 return $this->handleAlert($validator->errors(),false);


             }

             $datos=$req->all();

             if (WbReporteInspeccionCalidad::where("id_reporte_inspeccion_calidad",$datos['REPORTEID'])->count()==0) {
                 return $this->handleAlert("Algunos datos no pudieron ser sincronizados (002)",false);
             }
             if (WbLiberacionesActividades::where("id_liberaciones_actividades",$datos['ACTIVIDAD'])->where("estado","=","1")->count()==0) {
                 return $this->handleAlert("Algunos datos no pudieron ser sincronizados (003)",false);
             }

             //return format(strtotime($datos['FECHA']);
             //return date('Y-m-d H:i:s',strtotime($datos['FECHA']));


             $calificacion= new WbReporteInspeccionCalidadCalifi;
             $calificacion->fk_reporte_inspeccion_calidad=$datos['REPORTEID'];
             $calificacion->fk_liberaciones_actividades=$datos['ACTIVIDAD'];
             $calificacion->calificacion=$datos['CALIFICACION'] ;
             $calificacion->dateCreate= date('d-m-Y H:i:s',strtotime($datos['FECHA'])) ;
             $calificacion->save();

             return $this->handleAlert("Calificacion insertada con exito",true);*/


        }
        return $this->handleAlert($respuesta,true);
    }

    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request)
    {

        //se crea la variable que va a guardar la respuesta
        $respuesta=collect();

        //return $calificar[0];
        $prueba=$request["datos"];

        //recorrer los datos recibidos
        try {
            foreach ($prueba as $key => $item) {

                //convierte los datos en una colleccion
                //$req=json_encode($req);


                //se valida la informacion recibida
                $validator = Validator::make($item, [
                    'REPORTEID' => 'required|numeric',
                    'ACTIVIDAD' => 'required|numeric',
                    'CALIFICACION' => 'required|string',
                    'FECHA' => 'required|date',
                ]);

                //inicializamos la variable que guarda la
                $rep=collect();
                $rep->put('REPORTEID',$item['REPORTEID']);
                $rep->put('ACTIVIDAD',$item['ACTIVIDAD']);
                $proyecto = $this->traitGetProyectoCabecera($request);
                //si no cumple validaciones imprime error
                if(!$validator->fails()){

                    //inicializamos la variable de validacion
                    $cumple=true;

                    //valida que el reporte de inspeccion este creado
                    if (WbReporteInspeccionCalidad::where("id_reporte_inspeccion_calidad",$item['REPORTEID'])
                            ->where('fk_id_project_Company', $proyecto)->count()==0) {
                        $cumple=false;
                    }
                    //valida que el id de la actividad sea valido
                    if (WbLiberacionesActividades::where("id_liberaciones_actividades",$item['ACTIVIDAD'])
                            ->where('fk_id_project_Company', $proyecto)
                            ->where("estado","=","1")->count()==0) {
                        $cumple=false;
                    }

                    //si cumple todos los requerimientos inserta la informacion
                    if ($cumple) {
                        $aux = new WbReporteInspeccionCalidadCalifi;
                        $aux = $this->traitSetProyectoYCompania($request, $aux);
                        //valida que el dato existe en caso contrario lo inserta
                        $calificacion = WbReporteInspeccionCalidadCalifi::firstOrCreate(
                            [
                                'fk_reporte_inspeccion_calidad'=>$item['REPORTEID'],
                                'fk_liberaciones_actividades'=>$item['ACTIVIDAD'],
                                'fk_id_project_Company'=>$aux->fk_id_project_Company,
                                'fk_compañia'=>$aux->fk_compañia,
                            ],
                            ['calificacion'=>$item['CALIFICACION'], 'dateCreate'=>date('d-m-Y H:i:s',strtotime($item['FECHA'])) ]
                        );
                    }

                    $rep->put('ESTADO',$cumple);

                }else{
                    $rep->put('ESTADO',false);
                }
                $respuesta->push($rep);
            }
        } catch (\Exception $exc) {
            var_dump($exc);
        }
        return $this->handleAlert($respuesta,true);
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
}
