<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class calificar_inspeccion_calidadResource extends ResourceCollection
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
                    'IDREPORTE'=>$data['id_reporte_inspeccion_calidad_califi'],
                    'FKREPORTE'=>$data['fk_reporte_inspeccion_calidad'],
                    'FKACTIVIDAD'=>$data['fk_liberaciones_actividades'],
                    'CALIFICACION'=>$data['calificacion']
                ];
            });
    }
}
