<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class inspeccion_calidadResource extends ResourceCollection
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
                    'IDFORMAACTIVIDAD'=>$data['id_liberaciones_formatos_act'],
                    'IDLIBACTIVIDAD'=>$data['fk_id_liberaciones_actividades'],
                    'IDLIBFORMA'=>$data['fk_id_liberaciones_formatos'],
                    'DATECREATE'=>$data['datecreate'],
                    'ESTADO'=>$data['estado'],
                    'IDUSUARIO'=>$data['userCreator'],
                    'NOMBRE'=>$data['nombre'],
                    'CRITERIOS'=>$data['criterios']
                ];
            });
    }
}
