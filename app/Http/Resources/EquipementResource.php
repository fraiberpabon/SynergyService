<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EquipementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        /*[
            'EQUIPO'=>$request->EquipmentID,
            'DESCRIPCION'=>$request->Description,
            'MARCA'=>$request->Make,
            'MODELO'=>$request->Model,
            'SERIAL'=>$request->SerialNumber ,
            'TIPO'=>$request->Owned, 
            'ESTADO'=>$request->Status,
            'CONTRATO'=>'' ,
            'CANTIDAD'=>'',
            'PESO'=>'' ,
            'KG'=>$request->Payload
        ];*/
      
    }


    
}
