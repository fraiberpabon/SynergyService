<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class Wb_Solicitud_Liberaciones_Firmas_R extends ResourceCollection
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
                   'IDSOLICITUDESLIBERACIONFIRMAS'=>$data['id_solicitudes_liberaciones_firmas'],
                   'IDSOLICITUDLIBERACION'=>$data['fk_id_solicitudes_liberaciones'],
                   'IDAREA'=>$data['fk_id_area'],
                   'IDUSUARIO'=>$data['fk_id_usuario'],
                   'NOTA'=>$data['nota'],
                   'PANORAMICA'=>$data['panoramica'],
                   'ESTADO'=>$data['estado'],
                   'FECHA'=>$data['dateCreate']
                ];
            });
    }
}
