<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\ts_Equipement;

class EquipementsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
       /* return //parent::toArray($request);
        [
            'EQUIPO'=>$this->collection->map(function($data) {
                    return $data['EquipmentID'];
                }),
            //'DESCRIPCION'=>'123'//$request->Description,
            /*'MARCA'=>$request->Make,
            'MODELO'=>$request->Model,
            'SERIAL'=>$request->SerialNumber ,
            'TIPO'=>$request->Owned, 
            'ESTADO'=>$request->Status,
            'CONTRATO'=>'' ,
            'CANTIDAD'=>'',
            'PESO'=>'' ,
            'KG'=>$request->Payload
        ];*/

        return 
            $this->collection->map(function($data) {
                    if(isset($data['peso'])){
                        $peso=$data['peso'];
                    }else{
                        $peso='';
                    }
                    return 
                    [
                        'EQUIPO'=>$data['EquipmentID'],
                        'DESCRIPCION'=>$data['Description'],
                        'MARCA'=>$data['Make'],
                        'MODELO'=>$data['Model'],
                        'SERIAL'=>$data['SerialNumber'],
                        'TIPO'=>$data['Owned'], 
                        'ESTADO'=>$data['Status'],
                        'CONTRATO'=>$data['SubcontractorDesc'] ,
                        'CANTIDAD'=>'',
                        'PESO'=>$peso ,
                        'M3'=>$data['Payload']

                    ];
                })
            
        ;
       
    }
}
