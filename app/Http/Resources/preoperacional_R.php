<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class preoperacional_R extends ResourceCollection
{

    public function toArray($request)
    {
        return
        $this->collection->map(function($data) {
                return
                [
                    'AUTOINCREMENTAL'=>$data['preoperacional_auto'],
                    'IDPREOPERACIONAL'=>$data['id_preoperacional'],
                    'EQUIPAMENT'=>$data['fk_equipamentID'],
                    'TIPOVEHICULO'=>$data['tipoVehiculo'],
                    'FECHA'=>$data['fecha'],
                    'CREACION'=>$data['datecreate'],
                    'TURNO'=>$data['turno'],
                    'HOROMETRO'=>$data['horometro'],
                    'PREOPERACIONAL'=>$data['preoperacional'],
                    'OBSERVACION'=>$data['observacion'],
                    'USUARIO'=>$data['fk_id_usuario'],
                    'ESTADO'=>$data['estado'],
                    'ODOMETRO'=>$data['odometro'],
                    'OPERATIVIDAD'=>$data['operativo']

                ];
            });
    }
}
