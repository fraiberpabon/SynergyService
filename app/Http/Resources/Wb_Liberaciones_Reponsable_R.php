<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class Wb_Liberaciones_Reponsable_R extends ResourceCollection
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
                    'IDLIBRESPONSABLE'=>$data['id_liberacion_responsable'],
                    'IDACTIVIDAD'=>$data['fk_id_solicitud_liberaciones'],
                    'NOMBRE'=>$data['Area'],
                    'IDAREA'=>$data['fk_id_area'],
                    'ESTADO'=>$data['estado']
                ];
            });
    }
}
