<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController as BaseController;
use App\Http\interfaces\Vervos;
use App\Http\trait\Resource;
use App\Models\WbReporteInspeccionCalidad;
use App\Models\usuarios_M;
use App\Models\WbHitos;
use App\Models\WbTramos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WbReporteInspeccionCalidadController extends BaseController implements Vervos
{
    use Resource;
    //OBSOLETO
    public function guardar( Request $actividad){
        try {
            $datos=$actividad->all();
            $reporte= new WbReporteInspeccionCalidad;
            $reporte->fk_id_usuarios=$datos['IDUSUARIO'];
            $reporte->fk_id_tramo=$datos['TRAMO'];
            $reporte->fk_id_hito=$datos['HITO'] ;
            $reporte->abscisa= $datos['ABSCISA'] ;
            $reporte->s1=$datos['s1'];
            $reporte->s2=$datos['s2'];
            $reporte->s3=$datos['s3'];
            $reporte->s4=$datos['s4'];
            $reporte->s5=$datos['s5'];
            $reporte->departamente=$datos['departamente'];
            $reporte->observaciones=$datos['observaciones'];
            $reporte->panoramica=$datos['PANORAMICA'];
            $reporte->estado=$datos['estado'];
            $reporte->save();
            $data = WbReporteInspeccionCalidad::latest('id_reporte_inspeccion_calidad')->first();
            return $this->handleAlert(  $data->id_reporte_inspeccion_calidad,true);

         } catch (\Throwable $th) {
             return $this->handleError('Error',$th->getMessage());
         }
        return $this->handleAlert($mensaje,true);
    }

    public function postDeprecated(Request $req)
    {
        ///se valida la informacion recibida
        $validator = Validator::make($req->all(), [
            'IDUSUARIO' => 'required|numeric',
            'TRAMO' => 'required|string',
            'HITO' => 'required|string',
            'ABSCISA' => 'required|numeric',
            'S1' => 'required|integer|min:0|max:5',
            'S2' => 'required|integer|min:0|max:5',
            'S3' => 'required|integer|min:0|max:5',
            'S4' => 'required|integer|min:0|max:5',
            'S5' => 'required|integer|min:0|max:5',
            'DEPARTAMENTO' => 'required|string',
            'OBSERVACION' => 'present|string|nullable',
            'FOTO1' => 'required|string',
            'FOTO2' => 'string|nullable',
            'FOTO3' => 'string|nullable',
            'FOTO4' => 'string|nullable',
            'FECHA' => 'required|date',
            'UBICACION' => 'string|nullable',
        ]);
        //si no cumple validaciones imprime error
        if($validator->fails()){
            return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (001)",false);
        }

        $datos=$req->all();
        //valida que el usuario exista
        if (usuarios_M::where("id_usuarios",$datos['IDUSUARIO'])->count()==0) {
            return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (002)",false);
        }
        //valida que
        if (WbHitos::where("Id_Hitos",$datos['HITO'])->where("Estado","like","A")->count()==0) {
            return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (003)",false);
        }
        if (WbTramos::where("Id_Tramo",$datos['TRAMO'])->where("Estado","like","A")->count()==0) {
            return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (004)",false);
        }

        $reporte=WbReporteInspeccionCalidad::firstOrCreate(
            ['fk_id_usuarios'=>$datos['IDUSUARIO'],
                'fk_id_tramo'=>$datos['TRAMO'],
                'fk_id_hito'=>$datos['HITO'],
                'abscisa'=>$datos['ABSCISA'],
                's1'=>($datos['S1']==0) ? null : $datos['S1'],
                's2'=>($datos['S2']==0) ? null : $datos['S2'],
                's3'=>($datos['S3']==0) ? null : $datos['S3'],
                's4'=>($datos['S4']==0) ? null : $datos['S4'],
                's5'=>($datos['S5']==0) ? null : $datos['S5'],
                'departamente'=>$datos['DEPARTAMENTO'],
                'observaciones'=>$datos['OBSERVACION'],
                'ubicacion'=>$datos['UBICACION'],
                'panoramica'=>str_replace("/", "*", $datos['FOTO1']),
                'panoramica1'=>(isset($datos['FOTO2'])) ? str_replace("/", "*", $datos['FOTO2']) : null ,
                'panoramica2'=>(isset($datos['FOTO3'])) ? str_replace("/", "*", $datos['FOTO3']) : null ,
                'panoramica3'=>(isset($datos['FOTO4'])) ? str_replace("/", "*", $datos['FOTO4']) : null ,
            ],
            ['estado'=>1]);

        if ($reporte->id_reporte_inspeccion_calidad>0) {
            return $this->handleAlert($reporte->id_reporte_inspeccion_calidad,true);
        }else{
            return $this->handleAlert('Error al intentar registrar el reporte',false);
        }
    }

    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @deprecated
     */
    public function post(Request $req)
    {
        //se valida la informacion recibida
        $validator = Validator::make($req->all(), [
            'IDUSUARIO' => 'required|numeric',
            'TRAMO' => 'required|string',
            'HITO' => 'required|string',
            'ABSCISA' => 'required|numeric',
            'S1' => 'required|integer|min:0|max:5',
            'S2' => 'required|integer|min:0|max:5',
            'S3' => 'required|integer|min:0|max:5',
            'S4' => 'required|integer|min:0|max:5',
            'S5' => 'required|integer|min:0|max:5',
            'DEPARTAMENTO' => 'required|string',
            'OBSERVACION' => 'present|string|nullable',
            'FOTO1' => 'required|string',
            'FOTO2' => 'string|nullable',
            'FOTO3' => 'string|nullable',
            'FOTO4' => 'string|nullable',
            'FECHA' => 'required|date',
            'UBICACION' => 'nullable|string',
        ]);
        //si no cumple validaciones imprime error
        if($validator->fails()){
            return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (001)",false);
        }

        $datos=$req->all();
        //valida que el usuario exista
        if (usuarios_M::where("id_usuarios",$datos['IDUSUARIO'])->count()==0) {
            return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (002)",false);
        }
        //valida que
        if (WbHitos::where("Id_Hitos",$datos['HITO'])->where("Estado","like","A")->count()==0) {
            return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (003)",false);
        }
        if (WbTramos::where("Id_Tramo",$datos['TRAMO'])->where("Estado","like","A")->count()==0) {
            return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas (004)",false);
        }

        $reporte=WbReporteInspeccionCalidad::firstOrCreate(
                ['fk_id_usuarios'=>$datos['IDUSUARIO'],
                'fk_id_tramo'=>$datos['TRAMO'],
                'fk_id_hito'=>$datos['HITO'],
                'abscisa'=>$datos['ABSCISA'],
                's1'=>($datos['S1']==0) ? null : $datos['S1'],
                's2'=>($datos['S2']==0) ? null : $datos['S2'],
                's3'=>($datos['S3']==0) ? null : $datos['S3'],
                's4'=>($datos['S4']==0) ? null : $datos['S4'],
                's5'=>($datos['S5']==0) ? null : $datos['S5'],
                'departamente'=>$datos['DEPARTAMENTO'],
                'observaciones'=>$datos['OBSERVACION'],
                'ubicacion'=>$datos['UBICACION'],
                'panoramica'=>str_replace("/", "*", $datos['FOTO1']),
                'panoramica1'=>(isset($datos['FOTO2'])) ? str_replace("/", "*", $datos['FOTO2']) : null ,
                'panoramica2'=>(isset($datos['FOTO3'])) ? str_replace("/", "*", $datos['FOTO3']) : null ,
                'panoramica3'=>(isset($datos['FOTO4'])) ? str_replace("/", "*", $datos['FOTO4']) : null ,
                ],
                ['estado'=>1]);
        if ($reporte->id_reporte_inspeccion_calidad>0) {
            return $this->handleAlert($reporte->id_reporte_inspeccion_calidad,true);
        }else{
            return $this->handleAlert('Error al intentar registrar el reporte',false);
        }
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
