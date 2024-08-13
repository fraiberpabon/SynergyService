<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class preoperacional_actividades_R extends ResourceCollection
{

    public function toArray($request)
    {
        return
        $this->collection->map(function($data) {
                return
                [
                    'AUTOINCREMENTAL'=>$data['id_preoperacional_calificacion'],
                    'FKIDPREOPERACIONAL'=>$data['fk_id_preoperacional'],
                    'FKACTIVIDAD'=>$data['fk_liberaciones_actividades'],
                    'CALIFICACION'=>$data['calificacion'],
                    'FECHA'=>$data['datecreate']
                ];
            });
    }
}
