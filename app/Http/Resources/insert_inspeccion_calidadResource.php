<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class insert_inspeccion_calidadResource extends ResourceCollection
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
                    'IDUSUARIO'=>$data['fk_id_usuarios'],
                    'TRAMO'=>$data['fk_id_tramo'],
                    'HITO'=>$data['fk_id_hito'],
                    'ABSCISA'=>$data['abscisa'],
                    'S1'=>$data['s1'],
                    'S2'=>$data['s2'],
                    'S3'=>$data['s3'],
                    'S4'=>$data['s4'],
                    'S5'=>$data['s5'],
                    'DEPARTAMENTE'=>$data['departamente'],
                    'OBSERVACIONES'=>$data['observaciones'],
                    'PANORAMICA'=>$data['panoramica'],
                    'ESTADO'=>$data['estado']
                ];
            });
    }
}
