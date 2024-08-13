<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class Wb_solicitud_liberaciones_act_resources extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return
        $this->collection->map(function($data) {

                return
                [
                    'IDSOLIACTIVIDAD'=>$data['id_solicitud_liberaciones_act'],
                    'IDSOLICITUD'=>$data['fk_id_solicitud_liberaciones'],
                    'IDACTIVIDAD'=>$data['fk_id_liberaciones_actividades'],
                    'CALIFICACION'=>$data['calificacion'],
                    'ESTADO'=>$data['estado'],
                    'IDUSUARIO'=>$data['fk_id_usuario'],
                    'NOMBRE'=>$data['nombre'],
                    'CRITERIO'=>$data['criterios'],
                    'NOTA'=>$data['nota'],
                ];
            });

    }
}
